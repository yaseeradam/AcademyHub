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
    
    // Gateway settings
    public string $public_key = '';
    public string $secret_key = '';
    public bool $sandbox_mode = true;

    protected $rules = [
        'selectedClass' => 'required|exists:classes,id',
        'amount_due'    => 'required|numeric|min:0',
        'public_key'    => 'nullable|string|max:255',
        'secret_key'    => 'nullable|string|max:255',
        'sandbox_mode'  => 'boolean',
    ];

    public function mount(): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin' || $user?->role === 'bursar', 403);

        $tenant = $user->tenant;
        $settings = $tenant->settings ?? [];

        // Load credentials from Tenant settings
        $this->public_key = $settings['payment_gateway']['public_key'] ?? 'pk_test_mock_12345';
        $this->secret_key = $settings['payment_gateway']['secret_key'] ?? 'sk_test_mock_56789';
        $this->sandbox_mode = (bool) ($settings['payment_gateway']['sandbox_mode'] ?? true);

        // Load classes
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
        $termObj = AcademicTerm::active();
        $term = $termObj?->term_number ?? 1;
        $session = $termObj?->academicSession?->name ?? AcademicSession::activeName() ?? date('Y') . '/' . (date('Y') + 1);

        $fee = FeeStructure::where('tenant_id', $tenantId)
            ->where('class_id', $this->selectedClass)
            ->where('category', 'Tuition')
            ->where('term', $term)
            ->where('session', $session)
            ->first();

        $this->amount_due = $fee ? (float) $fee->amount_due : 0.0;
    }

    public function saveFeeStructure(): void
    {
        $this->validate([
            'selectedClass' => 'required|exists:classes,id',
            'amount_due'    => 'required|numeric|min:0',
        ]);

        $tenantId = auth()->user()->tenant_id;
        $termObj = AcademicTerm::active();
        $term = $termObj?->term_number ?? 1;
        $session = $termObj?->academicSession?->name ?? AcademicSession::activeName() ?? date('Y') . '/' . (date('Y') + 1);

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

    public function saveGatewaySettings(): void
    {
        $this->validate([
            'public_key' => 'nullable|string',
            'secret_key' => 'nullable|string',
            'sandbox_mode' => 'boolean',
        ]);

        $tenant = auth()->user()->tenant;
        $settings = $tenant->settings ?? [];
        
        $settings['payment_gateway'] = [
            'public_key'   => $this->public_key,
            'secret_key'   => $this->secret_key,
            'sandbox_mode' => $this->sandbox_mode,
        ];

        $tenant->update(['settings' => $settings]);

        $this->dispatch('alert', message: 'Payment gateway configuration saved successfully!', type: 'success');
    }

    public function render()
    {
        $tenantId = auth()->user()->tenant_id;
        $classes = SchoolClass::where('tenant_id', $tenantId)->get();

        $termObj = AcademicTerm::active();
        $term = $termObj?->term_number ?? 1;
        $session = $termObj?->academicSession?->name ?? AcademicSession::activeName() ?? date('Y') . '/' . (date('Y') + 1);

        // Fetch transactions for ledger
        $ledger = Transaction::where('tenant_id', $tenantId)
            ->where('category', 'Tuition')
            ->with(['student.schoolClass'])
            ->orderByDesc('id')
            ->get();

        return view('livewire.payment-gateway.index', compact('classes', 'ledger', 'term', 'session'));
    }
}
