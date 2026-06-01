<?php

namespace App\Livewire\PaymentGateway;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\Student;
use App\Models\FeeStructure;
use App\Models\AcademicTerm;
use App\Models\AcademicSession;
use App\Models\Transaction;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

#[Layout('layouts.app')]
#[Title('Secure Tuition Payment Center')]
class ParentPay extends Component
{
    public $students = [];
    public ?int $selectedStudentId = null;
    public float $amount_due = 0.0;
    public float $amount_paid = 0.0;
    public float $outstanding_balance = 0.0;

    // Card inputs
    public string $card_number = '';
    public string $card_expiry = '';
    public string $card_cvv = '';

    // Success State
    public bool $paymentSuccess = false;
    public string $receiptNumber = '';
    public string $receiptDate = '';

    public function mount(): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'parent', 403);

        $this->loadParentStudents();
    }

    private function loadParentStudents(): void
    {
        $user = auth()->user();
        $this->students = $user->students()
            ->with(['schoolClass'])
            ->get();

        if ($this->students->isNotEmpty()) {
            $this->selectedStudentId = $this->students->first()->id;
            $this->calculateBalances();
        }
    }

    public function updatedSelectedStudentId(): void
    {
        $this->calculateBalances();
    }

    private function calculateBalances(): void
    {
        if (!$this->selectedStudentId) {
            return;
        }

        $student = Student::findOrFail($this->selectedStudentId);
        $tenantId = $student->tenant_id;

        $termObj = AcademicTerm::active();
        $term = $termObj?->term_number ?? 1;
        $session = $termObj?->academicSession?->name ?? AcademicSession::activeName() ?? date('Y') . '/' . (date('Y') + 1);

        // Fetch Fee Structure
        $fee = FeeStructure::where('tenant_id', $tenantId)
            ->where('class_id', $student->class_id)
            ->where('term', $term)
            ->where('session', $session)
            ->first();

        $this->amount_due = $fee ? (float) $fee->amount_due : 0.0;

        // Fetch paid tuition
        $this->amount_paid = (float) Transaction::where('tenant_id', $tenantId)
            ->where('student_id', $student->id)
            ->where('category', 'Tuition')
            ->where('term', $term)
            ->where('session', $session)
            ->where('is_void', false)
            ->sum('amount_paid');

        $this->outstanding_balance = max(0.0, $this->amount_due - $this->amount_paid);
    }

    public function processCardPayment(): void
    {
        $this->validate([
            'selectedStudentId' => 'required|exists:students,id',
            'card_number'       => 'required|string|min:16',
            'card_expiry'       => 'required|string|min:5',
            'card_cvv'          => 'required|string|min:3|max:4',
        ]);

        if ($this->outstanding_balance <= 0) {
            $this->dispatch('alert', message: 'This student has no outstanding tuition balance for the current term.', type: 'info');
            return;
        }

        $student = Student::findOrFail($this->selectedStudentId);
        $tenantId = $student->tenant_id;
        $parent = auth()->user();

        $termObj = AcademicTerm::active();
        $term = $termObj?->term_number ?? 1;
        $session = $termObj?->academicSession?->name ?? AcademicSession::activeName() ?? date('Y') . '/' . (date('Y') + 1);

        // Record incoming transaction
        $transaction = Transaction::create([
            'tenant_id'      => $tenantId,
            'student_id'     => $student->id,
            'type'           => 'Income',
            'category'       => 'Tuition',
            'term'           => $term,
            'session'        => $session,
            'amount_paid'    => $this->outstanding_balance,
            'payment_method' => 'Transfer',
            'date'           => now()->toDateString(),
            'is_void'        => false,
        ]);

        // Send dynamic push notification receipt if Parent has WhatsApp number
        if ($parent->whatsapp_phone) {
            $currency = config('myacademy.currency_symbol', '₦');
            $formattedAmount = number_format($transaction->amount_paid, 2);
            $schoolName = config('myacademy.school_name', 'AcademyHub');

            $msg = "💳 *Payment Received Successfully!*\n\n" .
                   "Thank you, *{$parent->name}*. We have successfully processed your tuition payment via Web QuickPay:\n\n" .
                   "• *Student:* {$student->full_name}\n" .
                   "• *Class:* " . ($student->schoolClass?->name ?? 'N/A') . "\n" .
                   "• *Term / Session:* Term {$term} ({$session})\n" .
                   "• *Amount Paid:* {$currency}{$formattedAmount}\n" .
                   "• *Receipt No:* {$transaction->receipt_number}\n\n" .
                   "🏫 *{$schoolName}*";

            // Trigger outbound support notification
            $this->sendOutboundWhatsApp($parent->whatsapp_phone, $msg);
        }

        // Set state
        $this->receiptNumber = $transaction->receipt_number;
        $this->receiptDate = now()->format('F j, Y, g:i a');
        $this->paymentSuccess = true;

        $this->dispatch('alert', message: 'Payment successfully processed! Receipt has been registered.', type: 'success');

        // Clear forms
        $this->reset(['card_number', 'card_expiry', 'card_cvv']);
        $this->calculateBalances();
    }

    private function sendOutboundWhatsApp(string $toPhone, string $messageText): void
    {
        try {
            $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;
            if ($tenant) {
                $tenantActive = ($tenant->status === 'active') && (!$tenant->expires_at || !$tenant->expires_at->isPast());
                $botActive = $tenant->activeMarketplaceComponents()->where('slug', 'whatsapp-bot')->exists();
                if (!$tenantActive || !$botActive) {
                    return;
                }
            }

            $token = config('services.whatsapp.token');
            $phoneNumberId = config('services.whatsapp.phone_number_id');

            if (empty($token) || empty($phoneNumberId)) {
                return;
            }

            Http::withOptions(['verify' => false])
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json'
                ])
                ->post("https://graph.facebook.com/v19.0/{$phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $toPhone,
                    'type' => 'text',
                    'text' => [
                        'preview_url' => false,
                        'body' => $messageText
                    ]
                ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Outbound billing alert exception: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $termObj = AcademicTerm::active();
        $term = $termObj?->term_number ?? 1;
        $session = $termObj?->academicSession?->name ?? AcademicSession::activeName() ?? date('Y') . '/' . (date('Y') + 1);

        return view('livewire.payment-gateway.parent-pay', compact('term', 'session'));
    }
}
