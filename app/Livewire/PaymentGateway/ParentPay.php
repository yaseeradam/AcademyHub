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
    public bool $isGatewayApproved = false;

    // Card inputs
    public string $card_number = '';
    public string $card_expiry = '';
    public string $card_cvv    = '';

    // Success State
    public bool $paymentSuccess  = false;
    public string $receiptNumber = '';
    public string $receiptDate   = '';

    // Term and Session Selectors
    public int $selectedTerm       = 1;
    public string $selectedSession = '';
    public array $sessions         = [];

    // ── Installment Plan ───────────────────────────────────────────
    /** Plans the school has enabled for this student's class fee. */
    public array $enabledPlans = ['full' => true];

    /** Plan the parent has chosen: full | two_installments | monthly */
    public string $selectedPlan = 'full';

    /**
     * The amount that will be charged for the selected plan in this transaction.
     * Computed from $amount_due and how much has already been paid under that plan.
     */
    public float $paymentAmount = 0.0;

    /**
     * Which installment number will this payment be?
     * (1 = first, 2 = second, etc.)
     */
    public int $installmentNumber = 1;

    /** Human-readable label for the current installment position */
    public string $installmentLabel = '';

    public function mount(): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'parent', 403);

        $termObj = AcademicTerm::active();
        $this->selectedTerm    = $termObj?->term_number ?? 1;
        $this->selectedSession = $termObj?->academicSession?->name
            ?? AcademicSession::activeName()
            ?? $this->defaultSession();

        $this->loadSessions();
        $this->loadParentStudents();
    }

    private function loadSessions(): void
    {
        $this->sessions = AcademicSession::query()
            ->orderByDesc('name')
            ->pluck('name')
            ->toArray();

        if (empty($this->sessions)) {
            $this->sessions = [$this->defaultSession()];
        }
    }

    private function defaultSession(): string
    {
        $y = (int) now()->format('Y');
        return "{$y}/" . ($y + 1);
    }

    public function updatedSelectedTerm(): void
    {
        $this->calculateBalances();
    }

    public function updatedSelectedSession(): void
    {
        $this->calculateBalances();
    }

    public function updatedSelectedStudentId(): void
    {
        $this->calculateBalances();
    }

    public function updatedSelectedPlan(): void
    {
        $this->recalculatePaymentAmount();
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

    private function calculateBalances(): void
    {
        if (!$this->selectedStudentId) {
            return;
        }

        $student  = Student::findOrFail($this->selectedStudentId);
        $tenantId = $student->tenant_id;
        $tenant   = $student->tenant;

        // Verify gateway status
        $this->isGatewayApproved = (
            ($tenant->settings['payment_gateway']['subaccount_status'] ?? 'not_submitted') === 'approved'
        );

        // Fetch Fee Structure for this student's class
        $fee = FeeStructure::where('tenant_id', $tenantId)
            ->where('class_id', $student->class_id)
            ->where('term', $this->selectedTerm)
            ->where('session', $this->selectedSession)
            ->first();

        $this->amount_due = $fee ? (float) $fee->amount_due : 0.0;

        // Load enabled plans from the fee structure
        $this->enabledPlans = $fee ? $fee->enabledPlans() : ['full' => true];

        // If the currently selected plan is no longer enabled, reset to full
        if (!isset($this->enabledPlans[$this->selectedPlan])) {
            $this->selectedPlan = 'full';
        }

        // Fetch total paid tuition for this term/session
        $this->amount_paid = (float) Transaction::where('tenant_id', $tenantId)
            ->where('student_id', $student->id)
            ->where('category', 'Tuition')
            ->where('term', $this->selectedTerm)
            ->where('session', $this->selectedSession)
            ->where('is_void', false)
            ->sum('amount_paid');

        $this->outstanding_balance = max(0.0, $this->amount_due - $this->amount_paid);

        $this->recalculatePaymentAmount();
    }

    /**
     * Calculate what the parent will actually pay now based on their chosen plan.
     */
    private function recalculatePaymentAmount(): void
    {
        if (!$this->selectedStudentId || $this->amount_due <= 0) {
            $this->paymentAmount    = 0.0;
            $this->installmentLabel = '';
            return;
        }

        $student  = Student::find($this->selectedStudentId);
        $tenantId = $student?->tenant_id;

        switch ($this->selectedPlan) {
            case 'two_installments':
                $this->computeTwoInstallments($tenantId, $student);
                break;

            case 'monthly':
                $this->computeMonthlyInstallment($tenantId, $student);
                break;

            case 'full':
            default:
                $this->selectedPlan      = 'full';
                $this->paymentAmount     = $this->outstanding_balance;
                $this->installmentNumber = 1;
                $this->installmentLabel  = 'Full Payment';
                break;
        }

        // Clamp so we never charge more than what's outstanding
        $this->paymentAmount = min($this->paymentAmount, $this->outstanding_balance);
        $this->paymentAmount = max(0.0, $this->paymentAmount);
    }

    private function computeTwoInstallments(?int $tenantId, $student): void
    {
        if (!$tenantId || !$student) return;

        // How many valid installment 1 payments under this plan exist?
        $installment1Count = Transaction::where('tenant_id', $tenantId)
            ->where('student_id', $student->id)
            ->where('category', 'Tuition')
            ->where('term', $this->selectedTerm)
            ->where('session', $this->selectedSession)
            ->where('installment_plan', 'two_installments')
            ->where('installment_number', 1)
            ->where('is_void', false)
            ->count();

        $halfAmount = round($this->amount_due / 2, 2);

        if ($installment1Count === 0) {
            // Paying the first half
            $this->installmentNumber = 1;
            $this->paymentAmount     = $halfAmount;
            $this->installmentLabel  = "Installment 1 of 2 — first half";
        } else {
            // First half is done, paying the remainder
            $this->installmentNumber = 2;
            $this->paymentAmount     = $this->outstanding_balance;
            $this->installmentLabel  = "Installment 2 of 2 — final balance";
        }
    }

    private function computeMonthlyInstallment(?int $tenantId, $student): void
    {
        if (!$tenantId || !$student) return;

        // Count previous monthly installments already paid
        $paidInstallments = Transaction::where('tenant_id', $tenantId)
            ->where('student_id', $student->id)
            ->where('category', 'Tuition')
            ->where('term', $this->selectedTerm)
            ->where('session', $this->selectedSession)
            ->where('installment_plan', 'monthly')
            ->where('is_void', false)
            ->count();

        // A secondary school term is roughly 3 months
        $months        = 3;
        $monthlyAmount = round($this->amount_due / $months, 2);

        $this->installmentNumber = $paidInstallments + 1;

        if ($this->installmentNumber >= $months) {
            // Last month: pay whatever remains
            $this->paymentAmount    = $this->outstanding_balance;
            $this->installmentLabel = "Month {$this->installmentNumber} of {$months} — final balance";
        } else {
            $this->paymentAmount    = $monthlyAmount;
            $this->installmentLabel = "Month {$this->installmentNumber} of {$months}";
        }
    }

    public function processCardPayment(): void
    {
        $this->validate([
            'selectedStudentId' => 'required|exists:students,id',
            'card_number'       => 'required|string|min:16',
            'card_expiry'       => 'required|string|min:5',
            'card_cvv'          => 'required|string|min:3|max:4',
        ]);

        if (!$this->isGatewayApproved) {
            $this->dispatch('alert', message: 'Online payments are currently disabled for this school as setup is pending admin verification.', type: 'error');
            return;
        }

        if ($this->outstanding_balance <= 0) {
            $this->dispatch('alert', message: 'This student has no outstanding tuition balance for the current term.', type: 'info');
            return;
        }

        if ($this->paymentAmount <= 0) {
            $this->dispatch('alert', message: 'Payment amount could not be calculated. Please refresh and try again.', type: 'error');
            return;
        }

        $student  = Student::findOrFail($this->selectedStudentId);
        $tenantId = $student->tenant_id;
        $parent   = auth()->user();

        // Record the transaction with installment metadata
        $transaction = Transaction::create([
            'tenant_id'          => $tenantId,
            'student_id'         => $student->id,
            'type'               => 'Income',
            'category'           => 'Tuition',
            'term'               => $this->selectedTerm,
            'session'            => $this->selectedSession,
            'amount_paid'        => $this->paymentAmount,
            'payment_method'     => 'Transfer',
            'installment_plan'   => $this->selectedPlan,
            'installment_number' => $this->selectedPlan !== 'full' ? $this->installmentNumber : null,
            'date'               => now()->toDateString(),
            'is_void'            => false,
        ]);

        // Send WhatsApp receipt if parent has a WhatsApp number
        if ($parent->whatsapp_phone) {
            $currency      = config('myacademy.currency_symbol', '₦');
            $formattedAmt  = number_format($transaction->amount_paid, 2);
            $schoolName    = config('myacademy.school_name', 'AcademyHub');
            $planDesc      = match ($this->selectedPlan) {
                'two_installments' => "Installment {$this->installmentNumber} of 2",
                'monthly'          => "Monthly Installment #{$this->installmentNumber}",
                default            => 'Full Payment',
            };

            $msg = "💳 *Payment Received Successfully!*\n\n" .
                   "Thank you, *{$parent->name}*. We have successfully processed your tuition payment:\n\n" .
                   "• *Student:* {$student->full_name}\n" .
                   "• *Class:* " . ($student->schoolClass?->name ?? 'N/A') . "\n" .
                   "• *Term / Session:* Term {$this->selectedTerm} ({$this->selectedSession})\n" .
                   "• *Payment Plan:* {$planDesc}\n" .
                   "• *Amount Paid:* {$currency}{$formattedAmt}\n" .
                   "• *Receipt No:* {$transaction->receipt_number}\n\n" .
                   "🏫 *{$schoolName}*";

            $this->sendOutboundWhatsApp($parent->whatsapp_phone, $msg);
        }

        // Set success state
        $this->receiptNumber = $transaction->receipt_number;
        $this->receiptDate   = now()->format('F j, Y, g:i a');
        $this->paymentSuccess = true;

        $this->dispatch('alert', message: 'Payment successfully processed! Receipt has been registered.', type: 'success');

        // Clear card form and refresh balances
        $this->reset(['card_number', 'card_expiry', 'card_cvv']);
        $this->calculateBalances();
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
            \Illuminate\Support\Facades\Log::error('Outbound billing alert exception: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.payment-gateway.parent-pay');
    }
}
