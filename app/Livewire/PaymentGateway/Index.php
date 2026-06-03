<?php

namespace App\Livewire\PaymentGateway;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\SchoolClass;
use App\Models\FeeStructure;
use App\Models\AcademicTerm;
use App\Models\AcademicSession;
use App\Models\Transaction;
use App\Support\TenantSettings;

#[Layout('layouts.app')]
#[Title('Payment Gateway Management Cockpit')]
class Index extends Component
{
    public ?int $selectedClass = null;
    public float $amount_due = 0.0;

    // Installment policy for the selected class
    public bool $plan_two_installments = false;
    public bool $plan_monthly = false;

    // Payout Settings
    public string $bank_name = '';
    public string $account_number = '';
    public string $account_name = '';
    public string $collection_timing = 'per_term';
    public string $subaccount_status = 'not_submitted';
    public bool $isEditingBankDetails = false;

    /**
     * Settlement timing options appropriate for Nigerian private secondary schools.
     */
    public const TIMING_OPTIONS = [
        'per_term'  => 'Per Term Payout (Recommended)',
        'monthly'   => 'Monthly Payout',
        'weekly'    => 'Weekly Payout',
        'on_demand' => 'On-Demand / Manual Request',
    ];

    protected function rules(): array
    {
        return [
            'selectedClass'      => 'required|exists:classes,id',
            'amount_due'         => 'required|numeric|min:0',
            'bank_name'          => 'required|string|max:255',
            'account_number'     => 'required|string|size:10|regex:/^[0-9]+$/',
            'account_name'       => 'required|string|max:255',
            'collection_timing'  => 'required|string|in:' . implode(',', array_keys(self::TIMING_OPTIONS)),
        ];
    }

    public function enableEditing(): void
    {
        $this->isEditingBankDetails = true;
    }

    public function mount(): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin' || $user?->role === 'bursar', 403);

        $tenant = $user->tenant;
        $settings = $tenant->settings ?? [];

        // Load bank/payout credentials from Tenant settings
        $this->bank_name        = $settings['payment_gateway']['bank_name'] ?? '';
        $this->account_number   = $settings['payment_gateway']['account_number'] ?? '';
        $this->account_name     = $settings['payment_gateway']['account_name'] ?? '';
        $this->collection_timing = $settings['payment_gateway']['collection_timing'] ?? 'per_term';
        $this->subaccount_status = $settings['payment_gateway']['subaccount_status'] ?? 'not_submitted';

        // Default editing state based on status
        $this->isEditingBankDetails = ($this->subaccount_status === 'not_submitted');

        // Load classes and default selection
        $classes = SchoolClass::where('tenant_id', $tenant->id)->get();
        if ($classes->isNotEmpty()) {
            $this->selectedClass = $classes->first()->id;
            $this->loadClassFee();
        }
    }

    public function updatedSelectedClass(): void
    {
        $this->loadClassFee();
    }

    private function loadClassFee(): void
    {
        $tenantId = auth()->user()->tenant_id;
        $termObj  = AcademicTerm::active();
        $term     = $termObj?->term_number ?? 1;
        $session  = $termObj?->academicSession?->name ?? AcademicSession::activeName() ?? date('Y') . '/' . (date('Y') + 1);

        $fee = FeeStructure::where('tenant_id', $tenantId)
            ->where('class_id', $this->selectedClass)
            ->where('category', 'Tuition')
            ->where('term', $term)
            ->where('session', $session)
            ->first();

        $this->amount_due = $fee ? (float) $fee->amount_due : 0.0;

        // Load installment policy for this class
        $plans = $fee?->installment_plans ?? [];
        $this->plan_two_installments = !empty($plans['two_installments']);
        $this->plan_monthly          = !empty($plans['monthly']);
    }

    public function saveFeeStructure(): void
    {
        $this->validate([
            'selectedClass' => 'required|exists:classes,id',
            'amount_due'    => 'required|numeric|min:0',
        ]);

        $tenantId = auth()->user()->tenant_id;
        $termObj  = AcademicTerm::active();
        $term     = $termObj?->term_number ?? 1;
        $session  = $termObj?->academicSession?->name ?? AcademicSession::activeName() ?? date('Y') . '/' . (date('Y') + 1);

        FeeStructure::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'class_id'  => $this->selectedClass,
                'category'  => 'Tuition',
                'term'      => $term,
                'session'   => $session,
            ],
            [
                'amount_due' => $this->amount_due,
            ]
        );

        $this->dispatch('alert', message: 'Tuition fee scale updated successfully!', type: 'success');
    }

    /**
     * Save the installment plans policy for the selected class fee structure.
     * Full payment is always enabled and cannot be toggled off.
     */
    public function saveInstallmentPolicy(): void
    {
        $tenantId = auth()->user()->tenant_id;
        $termObj  = AcademicTerm::active();
        $term     = $termObj?->term_number ?? 1;
        $session  = $termObj?->academicSession?->name ?? AcademicSession::activeName() ?? date('Y') . '/' . (date('Y') + 1);

        $fee = FeeStructure::where('tenant_id', $tenantId)
            ->where('class_id', $this->selectedClass)
            ->where('category', 'Tuition')
            ->where('term', $term)
            ->where('session', $session)
            ->first();

        if (!$fee) {
            $this->dispatch('alert', message: 'Please save the tuition amount first before configuring installment plans.', type: 'warning');
            return;
        }

        $fee->update([
            'installment_plans' => [
                'full'             => true,  // always on
                'two_installments' => $this->plan_two_installments,
                'monthly'          => $this->plan_monthly,
            ],
        ]);

        $this->dispatch('alert', message: 'Installment payment plans updated successfully!', type: 'success');
    }

    public function saveGatewaySettings(): void
    {
        $this->validate([
            'bank_name'         => 'required|string|max:255',
            'account_number'    => 'required|string|size:10|regex:/^[0-9]+$/',
            'account_name'      => 'required|string|max:255',
            'collection_timing' => 'required|string|in:' . implode(',', array_keys(self::TIMING_OPTIONS)),
        ]);

        $tenant   = auth()->user()->tenant;
        $settings = $tenant->settings ?? [];

        $this->subaccount_status = 'pending';

        $settings['payment_gateway'] = [
            'bank_name'         => $this->bank_name,
            'account_number'    => $this->account_number,
            'account_name'      => $this->account_name,
            'collection_timing' => $this->collection_timing,
            'subaccount_status' => $this->subaccount_status,
        ];

        $tenant->update(['settings' => $settings]);
        $this->isEditingBankDetails = false;

        $this->dispatch('alert', message: 'Bank details submitted successfully! Payout status is now pending review.', type: 'success');
    }

    public function render()
    {
        $tenantId = auth()->user()->tenant_id;
        $classes  = SchoolClass::where('tenant_id', $tenantId)->get();

        $termObj = AcademicTerm::active();
        $term    = $termObj?->term_number ?? 1;
        $session = $termObj?->academicSession?->name ?? AcademicSession::activeName() ?? date('Y') . '/' . (date('Y') + 1);

        // Fetch transactions for ledger
        $ledger = Transaction::where('tenant_id', $tenantId)
            ->where('category', 'Tuition')
            ->with(['student.schoolClass'])
            ->orderByDesc('id')
            ->get();

        $timingOptions = self::TIMING_OPTIONS;

        return view('livewire.payment-gateway.index', compact('classes', 'ledger', 'term', 'session', 'timingOptions'));
    }
}
