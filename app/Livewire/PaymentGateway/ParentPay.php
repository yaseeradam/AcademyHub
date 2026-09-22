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
use Illuminate\Support\Facades\Log;

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
    public string $errorMessage = '';

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

        if (session('parent_pay_success')) {
            $this->paymentSuccess = true;
            $this->receiptNumber = session('receipt_number', '');
            $this->receiptDate = session('receipt_date', '');
            $this->paymentAmount = session('payment_amount', 0.0);
            $this->installmentLabel = session('installment_label', '');
        }

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
        $this->errorMessage = '';
        if (!$this->selectedStudentId) {
            return;
        }

        $student  = Student::findOrFail($this->selectedStudentId);
        $tenantId = $student->tenant_id;
        $tenant   = $student->tenant;

        // Verify gateway status and plugin activation
        $gatewayActive = $tenant->activeMarketplaceComponents()->where('slug', 'payment-gateway')->exists();
        $subaccountStatus = $tenant->settings['payment_gateway']['subaccount_status'] ?? 'not_submitted';

        $this->isGatewayApproved = $gatewayActive && ($subaccountStatus === 'approved');

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
        $this->errorMessage = '';
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

        $amountInKobo = (int) ($this->paymentAmount * 100);
        $reference = 'TUI_' . uniqid() . '_' . time();
        $email = str_replace('.local', '.com', $parent->email ?? 'parent@school.com');

        // Load the subaccount code if available
        $tenant = $student->tenant;
        $subaccount = $tenant->settings['payment_gateway']['subaccount_code'] ?? null;

        $secretKey = config('services.paystack.secret_key');

        $payload = [
            'email' => $email,
            'amount' => $amountInKobo,
            'reference' => $reference,
            'callback_url' => route('paystack.callback'),
            'metadata' => [
                'payment_type' => 'tuition',
                'student_id' => $student->id,
                'installment_plan' => $this->selectedPlan,
                'installment_number' => $this->selectedPlan !== 'full' ? $this->installmentNumber : null,
                'tenant_id' => $tenantId,
                'parent_id' => $parent->id,
                'term' => $this->selectedTerm,
                'session' => $this->selectedSession,
            ]
        ];

        if ($subaccount) {
            $payload['subaccount'] = $subaccount;
        }

        $response = Http::withToken($secretKey)
            ->withOptions(['verify' => false])
            ->post('https://api.paystack.co/transaction/initialize', $payload);

        if (!$response->successful() || !$response->json('status')) {
            Log::error("Paystack Tuition Payment initialization failed", [
                'response' => $response->json()
            ]);
            $msg = $response->json('message') ?? 'Unable to connect to Paystack gateway.';
            $this->errorMessage = 'Payment initialization failed: ' . $msg;
            $this->dispatch('alert', message: $this->errorMessage, type: 'error');
            return;
        }

        $authorizationUrl = $response->json('data.authorization_url');
        
        $this->redirect($authorizationUrl, navigate: false);
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
