<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use App\Models\MarketplaceComponent;
use App\Models\TenantPluginBill;
use App\Models\Transaction;
use App\Support\Audit;
use App\Support\TenantSettings;

class PaystackCallbackController extends Controller
{
    public function handleCallback(Request $request)
    {
        $reference = $request->query('reference');

        if (!$reference) {
            return redirect()->route('dashboard')->with('error', 'Payment reference is missing.');
        }

        // Verify the transaction with Paystack
        $secretKey = config('services.paystack.secret_key', env('PAYSTACK_SECRET_KEY'));
        $response = Http::withToken($secretKey)
            ->withOptions(['verify' => false])
            ->timeout(15)
            ->get("https://api.paystack.co/transaction/verify/{$reference}");

        if (!$response->successful() || $response->json('data.status') !== 'success') {
            Log::error("Paystack verification failed for reference: {$reference}", [
                'response' => $response->json()
            ]);
            return $this->handleFailure($reference);
        }

        $data = $response->json('data');
        $metadata = $data['metadata'] ?? [];

        // 1. Parent Tuition Payment (TUI_ prefix)
        if (str_starts_with($reference, 'TUI_')) {
            return $this->processTuitionPayment($data, $metadata);
        }

        // 2. Marketplace Direct Plugin Purchase (PLG- prefix)
        if (str_starts_with($reference, 'PLG-')) {
            return $this->processMarketplacePurchase($data, $metadata);
        }

        // 3. Plugin Bill Payment (BILL_ prefix)
        if (str_starts_with($reference, 'BILL_')) {
            return $this->processPluginBillPayment($reference, $data);
        }

        // 4. Platform Subscription Renewal (SUB_ prefix)
        if (str_starts_with($reference, 'SUB_')) {
            return $this->processSubscriptionRenewal($reference, $data);
        }

        return redirect()->route('dashboard')->with('error', 'Unknown payment reference prefix.');
    }

    private function processTuitionPayment(array $data, array $metadata)
    {
        $studentId = $metadata['student_id'] ?? null;
        $plan = $metadata['installment_plan'] ?? 'full';
        $installmentNumber = $metadata['installment_number'] ?? null;
        $term = $metadata['term'] ?? null;
        $session = $metadata['session'] ?? null;
        $tenantId = $metadata['tenant_id'] ?? null;
        $parentId = $metadata['parent_id'] ?? null;

        if (!$studentId || !$tenantId || !$parentId) {
            return redirect()->route('parent.pay')->with('error', 'Invalid payment metadata details.');
        }

        // Prevent duplicate processing
        $existingTx = Transaction::where('receipt_number', $data['reference'])->first();
        if ($existingTx) {
            return redirect()->route('parent.pay')->with('status', 'This transaction was already registered.');
        }

        $student = Student::findOrFail($studentId);
        $parent = User::findOrFail($parentId);
        $amountPaid = (float) ($data['amount'] / 100);

        // Record the transaction
        $transaction = Transaction::create([
            'tenant_id'          => $tenantId,
            'student_id'         => $student->id,
            'type'               => 'Income',
            'category'           => 'Tuition',
            'term'               => $term,
            'session'            => $session,
            'amount_paid'        => $amountPaid,
            'payment_method'     => 'Transfer',
            'installment_plan'   => $plan,
            'installment_number' => $plan !== 'full' ? $installmentNumber : null,
            'receipt_number'     => $data['reference'],
            'date'               => now()->toDateString(),
            'is_void'            => false,
        ]);

        // Send WhatsApp receipt if parent has a WhatsApp number
        if ($parent->whatsapp_phone) {
            $currency      = config('academyhub.currency_symbol', '₦');
            $formattedAmt  = number_format($transaction->amount_paid, 2);
            $schoolName    = config('academyhub.school_name', 'AcademyHub');
            $planDesc      = match ($plan) {
                'two_installments' => "Installment {$installmentNumber} of 2",
                'monthly'          => "Monthly Installment #{$installmentNumber}",
                default            => 'Full Payment',
            };

            $msg = "💳 *Payment Received Successfully!*\n\n" .
                   "Thank you, *{$parent->name}*. We have successfully processed your tuition payment:\n\n" .
                   "• *Student:* {$student->full_name}\n" .
                   "• *Class:* " . ($student->schoolClass?->name ?? 'N/A') . "\n" .
                   "• *Term / Session:* Term {$term} ({$session})\n" .
                   "• *Payment Plan:* {$planDesc}\n" .
                   "• *Amount Paid:* {$currency}{$formattedAmt}\n" .
                   "• *Receipt No:* {$transaction->receipt_number}\n\n" .
                   "🏫 *{$schoolName}*";

            $this->sendOutboundWhatsApp($parent->whatsapp_phone, $msg);
        }

        // Store status in session and redirect back to parent pay
        session()->flash('parent_pay_success', true);
        session()->flash('receipt_number', $transaction->receipt_number);
        session()->flash('receipt_date', now()->format('F j, Y, g:i a'));
        session()->flash('payment_amount', $amountPaid);
        session()->flash('installment_label', $plan !== 'full' ? "Installment #{$installmentNumber}" : 'Full Payment');

        return redirect()->route('parent.pay')->with('status', 'Tuition payment processed successfully!');
    }

    private function processMarketplacePurchase(array $data, array $metadata)
    {
        $componentId = $metadata['component_id'] ?? null;
        $tenantId = $metadata['tenant_id'] ?? null;
        $userId = $metadata['user_id'] ?? null;

        if (!$componentId || !$tenantId || !$userId) {
            return redirect()->route('marketplace')->with('error', 'Invalid marketplace checkout metadata.');
        }

        $tenant = Tenant::findOrFail($tenantId);
        $user = User::findOrFail($userId);
        $component = MarketplaceComponent::findOrFail($componentId);

        // Check if amount matches
        $amountPaidKobo = (int) ($data['amount'] ?? 0);
        $expectedKobo = (int) ($component->price * 100);

        if ($amountPaidKobo < $expectedKobo) {
            return redirect()->route('marketplace')->with('error', 'Payment amount mismatch.');
        }

        $setupFee = (float) $component->setup_fee;
        $usageFee = (float) $component->usage_fee_per_student;

        // Install component
        $tenant->marketplaceComponents()->syncWithoutDetaching([
            $component->id => [
                'installed_at'          => now(),
                'uninstalled_at'        => null,
                'status'                => 'active',
                'setup_fee'             => $setupFee,
                'usage_fee_per_student' => $usageFee,
                'price_paid'            => $setupFee,
            ]
        ]);

        $tenant->marketplaceComponents()->updateExistingPivot($component->id, [
            'installed_at'          => now(),
            'uninstalled_at'        => null,
            'status'                => 'active',
            'setup_fee'             => $setupFee,
            'usage_fee_per_student' => $usageFee,
            'price_paid'            => $setupFee,
        ]);

        $component->increment('installs');

        if ($setupFee > 0) {
            TenantPluginBill::create([
                'tenant_id'                => $tenant->id,
                'marketplace_component_id' => $component->id,
                'bill_type'                => 'setup',
                'term_name'                => null,
                'session_name'             => null,
                'student_count'            => null,
                'setup_fee'                => $setupFee,
                'usage_fee_per_student'    => 0,
                'total_due'                => $setupFee,
                'status'                   => 'paid',
                'paid_at'                  => now(),
            ]);
        }

        return redirect()->route('marketplace')->with('status', 'Plugin payment successful! Module is now activated.');
    }

    private function processPluginBillPayment(string $reference, array $data)
    {
        if (preg_match('/^BILL_(\d+)_/', $reference, $matches)) {
            $billId = (int) $matches[1];
            $bill = TenantPluginBill::findOrFail($billId);

            if ($bill->status !== 'paid') {
                $bill->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

                Audit::log('billing.plugin_bill_paid', $bill, [
                    'bill_id' => $bill->id,
                    'amount' => (string) $bill->total_due,
                ]);
            }

            return redirect()->route('billing.index')->with('status', 'Bill payment verified and marked as paid.');
        }

        return redirect()->route('billing.index')->with('error', 'Invalid bill reference pattern.');
    }

    private function processSubscriptionRenewal(string $reference, array $data)
    {
        $newExpiry = now()->addYear();

        // Extend settings.json subscription_due_date
        $settingsPath = TenantSettings::settingsPath();
        $existing = file_exists($settingsPath) ? (json_decode(file_get_contents($settingsPath), true) ?? []) : [];
        $existing['subscription_due_date'] = $newExpiry->toDateString();
        file_put_contents($settingsPath, json_encode($existing, JSON_PRETTY_PRINT));

        $tenantId = TenantSettings::tenantId();
        if ($tenantId) {
            $tenant = Tenant::find($tenantId);
            if ($tenant) {
                $tenant->update([
                    'expires_at' => $newExpiry,
                ]);
            }
        }

        \Illuminate\Support\Facades\Cache::forget(TenantSettings::settingsCacheKey());

        return redirect()->route('settings.subscription')->with('status', 'Subscription renewed successfully!');
    }

    private function handleFailure(string $reference)
    {
        if (str_starts_with($reference, 'TUI_')) {
            return redirect()->route('parent.pay')->with('error', 'Tuition payment verification failed.');
        }
        if (str_starts_with($reference, 'PLG-')) {
            return redirect()->route('marketplace')->with('error', 'Marketplace plugin payment verification failed.');
        }
        if (str_starts_with($reference, 'BILL_')) {
            return redirect()->route('billing.index')->with('error', 'Invoice payment verification failed.');
        }
        if (str_starts_with($reference, 'SUB_')) {
            return redirect()->route('settings.subscription')->with('error', 'Subscription renewal verification failed.');
        }

        return redirect()->route('dashboard')->with('error', 'Payment verification failed.');
    }

    private function sendOutboundWhatsApp(string $toPhone, string $messageText): void
    {
        try {
            $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;
            if ($tenant) {
                $tenantActive = ($tenant->status === 'active') && (!$tenant->expires_at || !$tenant->expires_at->isPast());
                $botActive    = $tenant->activeMarketplaceComponents()->where('slug', 'whatsapp-bot')->exists();
                if (!$tenantActive || !$botActive) {
                    return;
                }
            }

            $token         = config('services.whatsapp.token');
            $phoneNumberId = config('services.whatsapp.phone_number_id');

            if (empty($token) || empty($phoneNumberId)) {
                return;
            }

            Http::withOptions(['verify' => false])
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type'  => 'application/json',
                ])
                ->post("https://graph.facebook.com/v19.0/{$phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'recipient_type'    => 'individual',
                    'to'                => $toPhone,
                    'type'              => 'text',
                    'text'              => [
                        'preview_url' => false,
                        'body'        => $messageText,
                    ],
                ]);
        } catch (\Exception $e) {
            Log::error('Outbound billing alert callback exception: ' . $e->getMessage());
        }
    }
}
