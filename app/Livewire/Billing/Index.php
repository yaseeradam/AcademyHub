<?php

namespace App\Livewire\Billing;

use App\Support\Audit;
use App\Support\BillingService;
use App\Models\AcademicSession;
use App\Models\FeeStructure;
use App\Models\Student;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Billing')]
class Index extends Component
{
    public string $tab = 'transactions';
    public string $errorMessage = '';

    public ?int $studentId = null;
    public string $studentSearch = '';
    public ?int $selectedClassId = null;
    public string $type = 'Income';
    public string $category = 'Tuition';
    public ?int $term = null;
    public ?string $session = null;
    public string $paymentMethod = 'Cash';
    public string $amountPaid = '';
    public string $reference = '';
    public string $payerName = '';
    public string $notes = '';
    public string $date = '';

    public bool $showReceiptModal = false;
    public ?int $receiptTransactionId = null;

    public ?int $registerClassId = null;
    public ?int $registerSectionId = null;
    public ?string $registerSession = null;
    public ?int $registerTerm = null;
    public string $registerCategory = 'Tuition';
    public string $registerSearch = '';

    public ?string $filterType = null;
    public string $filterCategory = '';
    public ?int $filterStudentId = null;
    public ?string $filterSession = null;
    public ?int $filterTerm = null;
    public ?string $filterFrom = null;
    public ?string $filterTo = null;
    public bool $includeVoided = false;

    public string $debtorsCategory = 'Tuition';
    public ?string $debtorsSession = null;
    public ?int $debtorsTerm = null;
    public ?int $debtorsClassId = null;
    public string $debtorsSearch = '';

    public ?int $feeClassId = null;
    public string $feeCategory = 'Tuition';
    public ?int $feeTerm = null;
    public ?string $feeSession = null;
    public string $feeAmountDue = '';
    public ?int $editingFeeId = null;

    public ?int $feeFilterClassId = null;
    public string $feeFilterCategory = '';
    public ?int $feeFilterTerm = null;
    public ?string $feeFilterSession = null;

    public ?int $voidingTransactionId = null;
    public string $voidReason = '';

    public function mount(): void
    {
        $user = auth()->user();
        $requestedTab = request('tab');
        $requestedTab = is_string($requestedTab) ? $requestedTab : null;

        $canTransactions = (bool) ($user?->hasPermission('billing.transactions') ?? false);
        $canFees = (bool) ($user?->hasPermission('fees.manage') ?? false);
        $canView = (bool) ($user?->hasPermission('billing.view') ?? false);

        $allowedTabs = ['plugin-bills'];
        if ($canTransactions || $canView) {
            $allowedTabs[] = 'debtors';
            $allowedTabs[] = 'class-register';
        }
        if ($canTransactions) {
            $allowedTabs[] = 'transactions';
        }
        if ($canFees) {
            $allowedTabs[] = 'fees';
        }

        $defaultTab = $canTransactions ? 'transactions' : ($canFees ? 'fees' : ($canView ? 'debtors' : 'plugin-bills'));
        $this->tab = ($requestedTab && in_array($requestedTab, $allowedTabs, true)) ? $requestedTab : $defaultTab;
        $this->date = now()->toDateString();
        $this->session = $this->session ?? $this->defaultSession();
        $this->term = $this->term ?? 1;

        $this->registerSession = $this->registerSession ?? $this->defaultSession();
        $this->registerTerm = $this->registerTerm ?? 1;
        if (! $this->registerClassId) {
            $this->registerClassId = \App\Models\SchoolClass::query()->orderBy('level')->orderBy('name')->value('id');
        }

        $this->debtorsCategory = $this->debtorsCategory ?: $this->category ?: 'Tuition';
        $this->debtorsSession = $this->debtorsSession ?? $this->defaultSession();
        $this->debtorsTerm = $this->debtorsTerm ?? 1;

        $this->feeSession = $this->feeSession ?? $this->defaultSession();
        $this->feeFilterSession = $this->feeFilterSession ?? $this->defaultSession();

        $this->filterFrom = now()->subDays(30)->toDateString();
        $this->filterTo = now()->toDateString();
    }

    public function updatedSelectedClassId(): void
    {
        $this->studentId = null; // Reset student selection when class changes
        $this->filterStudentId = null; // Reset filter student selection
        $this->dispatch('$refresh');
    }

    public function updatedFilterStudentId(): void
    {
        $this->dispatch('$refresh');
    }

    public function updatedTab(): void
    {
        $this->errorMessage = '';
        // Reset selections when switching tabs to avoid confusion
        if ($this->tab !== 'transactions') {
            $this->selectedClassId = null;
            $this->studentId = null;
        }
        $this->dispatch('$refresh');
    }

    public function updatedFilterType(): void
    {
        $this->dispatch('$refresh');
    }

    public function updatedFilterCategory(): void
    {
        $this->dispatch('$refresh');
    }

    public function updatedFilterSession(): void
    {
        $this->dispatch('$refresh');
    }

    public function updatedFilterTerm(): void
    {
        $this->dispatch('$refresh');
    }

    public function updatedFilterFrom(): void
    {
        $this->dispatch('$refresh');
    }

    public function updatedFilterTo(): void
    {
        $this->dispatch('$refresh');
    }

    public function updatedIncludeVoided(): void
    {
        $this->dispatch('$refresh');
    }

    public function updatedDebtorsCategory(): void
    {
        $this->dispatch('$refresh');
    }

    public function updatedDebtorsSession(): void
    {
        $this->dispatch('$refresh');
    }

    public function updatedDebtorsTerm(): void
    {
        $this->dispatch('$refresh');
    }

    public function updatedFeeFilterClassId(): void
    {
        $this->dispatch('$refresh');
    }

    public function updatedFeeFilterCategory(): void
    {
        $this->dispatch('$refresh');
    }

    public function updatedFeeFilterTerm(): void
    {
        $this->dispatch('$refresh');
    }

    public function updatedFeeFilterSession(): void
    {
        $this->dispatch('$refresh');
    }

    public function updatedRegisterClassId(): void
    {
        $this->registerSectionId = null;
        $this->dispatch('$refresh');
    }

    public function updatedRegisterSectionId(): void
    {
        $this->dispatch('$refresh');
    }

    public function updatedRegisterSession(): void
    {
        $this->dispatch('$refresh');
    }

    public function updatedRegisterTerm(): void
    {
        $this->dispatch('$refresh');
    }

    public function updatedRegisterCategory(): void
    {
        $this->dispatch('$refresh');
    }

    public function updatedRegisterSearch(): void
    {
        $this->dispatch('$refresh');
    }

    #[Computed]
    public function searchResults()
    {
        $term = trim($this->studentSearch);
        if (strlen($term) < 1) {
            return collect();
        }

        // If search string matches the current selected student's display text, don't show dropdown
        if ($this->selectedStudent && $this->studentSearch === ($this->selectedStudent->full_name . ' (' . ($this->selectedStudent->admission_number ?? 'No Adm #') . ')')) {
            return collect();
        }

        return Student::query()
            ->with(['schoolClass', 'section'])
            ->where('status', 'Active')
            ->where(function ($q) use ($term) {
                $q->where('first_name', 'like', "%{$term}%")
                  ->orWhere('last_name', 'like', "%{$term}%")
                  ->orWhere('admission_number', 'like', "%{$term}%");
            })
            ->when($this->selectedClassId, fn($q) => $q->where('class_id', $this->selectedClassId))
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function registerSections()
    {
        if (! $this->registerClassId) {
            return collect();
        }

        return \App\Models\Section::query()
            ->where('class_id', $this->registerClassId)
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function classRegisterData(): array
    {
        if (! $this->registerClassId) {
            return [
                'students' => [],
                'total_students' => 0,
                'total_due' => 0.0,
                'total_arrears' => 0.0,
                'total_expected' => 0.0,
                'total_paid' => 0.0,
                'total_balance' => 0.0,
                'collection_rate' => 0.0,
            ];
        }

        $category = trim($this->registerCategory) !== '' ? trim($this->registerCategory) : 'Tuition';
        $session = $this->registerSession ?: $this->defaultSession();
        $term = $this->registerTerm ? (int) $this->registerTerm : null;

        $query = Student::query()
            ->with(['schoolClass', 'section'])
            ->where('class_id', $this->registerClassId)
            ->where('status', 'Active')
            ->orderBy('last_name');

        if ($this->registerSectionId) {
            $query->where('section_id', $this->registerSectionId);
        }

        if (trim($this->registerSearch) !== '') {
            $s = trim($this->registerSearch);
            $query->where(function ($q) use ($s) {
                $q->where('first_name', 'like', "%{$s}%")
                  ->orWhere('last_name', 'like', "%{$s}%")
                  ->orWhere('admission_number', 'like', "%{$s}%");
            });
        }

        $students = $query->get();

        $rows = [];
        $totalDue = 0.0;
        $totalArrears = 0.0;
        $totalPaid = 0.0;
        $totalBalance = 0.0;

        foreach ($students as $student) {
            $ledger = BillingService::getStudentTermLedger($student, $session, $term, $category);
            $due = (float) ($ledger['current_term_due'] ?? $ledger['total_due']);
            $arrears = (float) ($ledger['past_arrears'] ?? 0);
            $paid = (float) ($ledger['total_paid'] ?? 0);
            $balance = (float) ($ledger['total_balance'] ?? 0);

            $totalDue += $due;
            $totalArrears += $arrears;
            $totalPaid += $paid;
            $totalBalance += $balance;

            $status = 'owing';
            if ($balance <= 0 && ($due > 0 || $paid > 0)) {
                $status = 'paid';
            } elseif ($paid > 0 && $balance > 0) {
                $status = 'partial';
            }

            $rows[] = [
                'student' => $student,
                'due' => $due,
                'arrears' => $arrears,
                'total_expected' => $due + $arrears,
                'paid' => $paid,
                'balance' => $balance,
                'status' => $status,
            ];
        }

        $totalExpected = $totalDue + $totalArrears;
        $rate = $totalExpected > 0 ? round(($totalPaid / $totalExpected) * 100, 1) : 0;

        return [
            'students' => $rows,
            'total_students' => count($rows),
            'total_due' => $totalDue,
            'total_arrears' => $totalArrears,
            'total_expected' => $totalExpected,
            'total_paid' => $totalPaid,
            'total_balance' => $totalBalance,
            'collection_rate' => min(100, max(0, $rate)),
        ];
    }

    #[Computed]
    public function receiptTransaction(): ?Transaction
    {
        if (! $this->receiptTransactionId) {
            return null;
        }

        return Transaction::query()
            ->with(['student.schoolClass', 'student.section'])
            ->find($this->receiptTransactionId);
    }

    #[Computed]
    public function students()
    {
        $query = Student::query()
            ->with(['schoolClass', 'section', 'user'])
            ->where('status', 'Active')
            ->orderBy('last_name');

        // Filter by selected class if specified
        if ($this->selectedClassId) {
            $query->where('class_id', $this->selectedClassId);
        }

        return $query->get();
    }

    #[Computed]
    public function selectedStudent(): ?Student
    {
        if (!$this->studentId) {
            return null;
        }

        return Student::query()->with(['schoolClass', 'section'])->find($this->studentId);
    }

    #[Computed]
    public function selectedStudentBalance(): ?array
    {
        if (!$this->studentId) {
            return null;
        }

        $student = $this->selectedStudent;
        if (!$student) {
            return null;
        }

        $category = trim($this->category) !== '' ? trim($this->category) : 'Tuition';
        $session = $this->session ?: $this->defaultSession();
        $term = $this->term ? (int) $this->term : null;

        $ledger = BillingService::getStudentTermLedger($student, $session, $term, $category);

        return [
            'due'               => $ledger['total_due'],
            'current_due'       => $ledger['current_term_due'],
            'current_paid'      => $ledger['current_term_paid'],
            'current_balance'   => $ledger['current_term_balance'],
            'past_arrears'      => $ledger['past_arrears'],
            'paid'              => $ledger['total_paid'],
            'balance'           => $ledger['total_balance'],
            'has_arrears'       => $ledger['has_arrears'],
            'has_fee_structure' => $ledger['total_due'] > 0,
            'terms_breakdown'   => $ledger['terms_breakdown'],
        ];
    }

    #[Computed]
    public function transactions()
    {
        $query = Transaction::query()
            ->with('student:id,first_name,last_name,admission_number') // Only load needed fields
            ->orderByDesc('date')
            ->orderByDesc('id');

        if (!$this->includeVoided) {
            $query->where('is_void', false);
        }

        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        if ($this->filterCategory) {
            $q = trim($this->filterCategory);
            $query->where('category', 'like', "%{$q}%");
        }

        if ($this->filterStudentId) {
            $query->where('student_id', $this->filterStudentId);
        } elseif ($this->selectedClassId) {
            // Filter by class if no specific student selected
            $query->whereHas('student', function($q) {
                $q->where('class_id', $this->selectedClassId);
            });
        }

        if ($this->filterSession) {
            $query->where('session', $this->filterSession);
        }

        if ($this->filterTerm) {
            $query->where('term', $this->filterTerm);
        }

        if ($this->filterFrom) {
            $query->whereDate('date', '>=', $this->filterFrom);
        }

        if ($this->filterTo) {
            $query->whereDate('date', '<=', $this->filterTo);
        }

        return $query->limit(100)->get();
    }

    #[Computed]
    public function debtors()
    {
        $category = trim($this->debtorsCategory) !== '' ? trim($this->debtorsCategory) : 'Tuition';
        $term = $this->debtorsTerm !== null && $this->debtorsTerm !== '' ? (int) $this->debtorsTerm : null;
        $session = $this->debtorsSession ?: null;
        $classId = $this->debtorsClassId ? (int) $this->debtorsClassId : null;
        $search = $this->debtorsSearch ?: null;

        return BillingService::getDebtors(
            category: $category,
            term: $term,
            session: $session,
            classId: $classId,
            search: $search
        );
    }

    #[Computed]
    public function availableFeeCategories(): array
    {
        $dbCats = FeeStructure::query()
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values()
            ->toArray();

        $defaults = ['Tuition', 'Development Levy', 'Uniform', 'Books', 'Transportation', 'Examination', 'Registration'];

        return array_values(array_unique(array_merge($defaults, $dbCats)));
    }

    #[Computed]
    public function availableSessions(): array
    {
        $sessions = \App\Models\AcademicSession::query()
            ->orderByDesc('name')
            ->pluck('name')
            ->filter()
            ->values()
            ->toArray();

        if (empty($sessions)) {
            $sessions = [
                date('Y') . '/' . (date('Y') + 1),
                (date('Y') - 1) . '/' . date('Y'),
            ];
        }

        return $sessions;
    }

    #[Computed]
    public function classes()
    {
        return \App\Models\SchoolClass::query()->orderBy('level')->orderBy('name')->get(['id', 'name', 'level']);
    }

    #[Computed]
    public function totalIncome()
    {
        return Transaction::query()
            ->where('is_void', false)
            ->where('type', 'Income')
            ->sum('amount_paid');
    }

    #[Computed]
    public function totalExpenses()
    {
        return Transaction::query()
            ->where('is_void', false)
            ->where('type', 'Expense')
            ->sum('amount_paid');
    }

    #[Computed]
    public function netBalance()
    {
        return $this->totalIncome - $this->totalExpenses;
    }

    #[Computed]
    public function feeStructures()
    {
        $query = FeeStructure::query()
            ->with('schoolClass:id,name,level')
            ->orderByDesc('id');

        if ($this->feeFilterClassId) {
            $query->where('class_id', $this->feeFilterClassId);
        }

        $category = trim($this->feeFilterCategory);
        if ($category !== '') {
            $query->where('category', 'like', "%{$category}%");
        }

        if ($this->feeFilterTerm) {
            $query->where('term', $this->feeFilterTerm);
        }

        $session = trim((string) ($this->feeFilterSession ?? ''));
        if ($session !== '') {
            $query->where('session', $session);
        }

        return $query->limit(100)->get();
    }

    public function startEditFee(int $feeId): void
    {
        $user = auth()->user();
        abort_unless($user && $user->hasPermission('fees.manage'), 403);

        $fee = FeeStructure::query()->findOrFail($feeId);

        $this->editingFeeId = $fee->id;
        $this->feeClassId = $fee->class_id;
        $this->feeCategory = (string) $fee->category;
        $this->feeTerm = $fee->term;
        $this->feeSession = $fee->session;
        $this->feeAmountDue = (string) $fee->amount_due;
    }

    public function cancelEditFee(): void
    {
        $user = auth()->user();
        abort_unless($user && $user->hasPermission('fees.manage'), 403);

        $this->editingFeeId = null;
        $this->feeClassId = null;
        $this->feeCategory = 'Tuition';
        $this->feeTerm = null;
        $this->feeSession = $this->defaultSession();
        $this->feeAmountDue = '';
    }

    public function saveFeeStructure(): void
    {
        $user = auth()->user();
        abort_unless($user && $user->hasPermission('fees.manage'), 403);

        try {
            $data = $this->validate([
                'feeClassId' => ['required', 'integer', Rule::exists('classes', 'id')],
                'feeCategory' => ['required', 'string', 'max:255'],
                'feeTerm' => ['nullable', 'integer', 'between:1,3'],
                'feeSession' => ['nullable', 'string', 'max:9'],
                'feeAmountDue' => ['required', 'numeric', 'min:0'],
            ], [
                'feeClassId.required' => 'Please select a class.',
                'feeCategory.required' => 'Please enter a fee category.',
                'feeAmountDue.required' => 'Please enter the amount due.',
                'feeAmountDue.min' => 'Amount must be greater than zero.',
            ]);

            if ($this->editingFeeId) {
                $fee = FeeStructure::query()->findOrFail($this->editingFeeId);
                $fee->update([
                    'class_id' => (int) $data['feeClassId'],
                    'category' => trim((string) $data['feeCategory']),
                    'term' => $data['feeTerm'] ? (int) $data['feeTerm'] : null,
                    'session' => $data['feeSession'] ? trim((string) $data['feeSession']) : null,
                    'amount_due' => $data['feeAmountDue'],
                ]);

                Audit::log('fees.structure_updated', $fee, [
                    'class_id' => $fee->class_id,
                    'category' => $fee->category,
                    'term' => $fee->term,
                    'session' => $fee->session,
                    'amount_due' => (string) $fee->amount_due,
                ]);
            } else {
                $fee = FeeStructure::query()->updateOrCreate(
                    [
                        'class_id' => (int) $data['feeClassId'],
                        'category' => trim((string) $data['feeCategory']),
                        'term' => $data['feeTerm'] ? (int) $data['feeTerm'] : null,
                        'session' => $data['feeSession'] ? trim((string) $data['feeSession']) : null,
                    ],
                    [
                        'amount_due' => $data['feeAmountDue'],
                    ]
                );

                Audit::log('fees.structure_saved', $fee, [
                    'class_id' => $fee->class_id,
                    'category' => $fee->category,
                    'term' => $fee->term,
                    'session' => $fee->session,
                    'amount_due' => (string) $fee->amount_due,
                ]);
            }

            $this->cancelEditFee();
            $this->dispatch('$refresh');

            $this->dispatch('alert', message: 'Fee structure saved successfully!', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('alert', message: 'Failed to save fee structure. Please try again.', type: 'error');
        }
    }

    public function deleteFeeStructure(int $feeId): void
    {
        $user = auth()->user();
        abort_unless($user && $user->hasPermission('fees.manage'), 403);

        $fee = FeeStructure::query()->findOrFail($feeId);
        $fee->delete();

        Audit::log('fees.structure_deleted', $fee, [
            'class_id' => $fee->class_id,
            'category' => $fee->category,
            'term' => $fee->term,
            'session' => $fee->session,
        ]);

        $this->dispatch('$refresh');
        $this->dispatch('alert', message: 'Fee structure deleted.', type: 'success');
    }

    public function selectStudent(int $id): void
    {
        $this->studentId = $id;
        $student = Student::with(['schoolClass', 'section'])->find($id);
        if ($student) {
            $this->selectedClassId = $student->class_id;
            $this->studentSearch = $student->full_name . ' (' . ($student->admission_number ?? 'No Adm #') . ')';

            // Auto prefill balance if available
            $info = $this->selectedStudentBalance;
            if ($info && $info['balance'] > 0) {
                $this->amountPaid = (string) $info['balance'];
            }
        }
    }

    public function clearSelectedStudent(): void
    {
        $this->studentId = null;
        $this->studentSearch = '';
        $this->amountPaid = '';
    }

    public function quickCollect(int $studentId, ?float $amount = null, ?string $category = null): void
    {
        $this->tab = 'transactions';
        $this->type = 'Income';
        if ($category) {
            $this->category = $category;
        }
        $this->selectStudent($studentId);
        if ($amount !== null && $amount > 0) {
            $this->amountPaid = (string) $amount;
        }
    }

    public function closeReceiptModal(): void
    {
        $this->showReceiptModal = false;
        $this->receiptTransactionId = null;
    }

    public function saveTransaction(): void
    {
        $user = auth()->user();
        abort_unless($user && $user->hasPermission('billing.transactions'), 403);

        try {
            $data = $this->validate([
                'studentId' => [
                    Rule::requiredIf(fn() => $this->type === 'Income'),
                    'nullable',
                    'integer',
                    Rule::exists('students', 'id')->when($user?->tenant_id, fn($q) => $q->where('tenant_id', $user->tenant_id)),
                ],
                'type' => ['required', Rule::in(['Income', 'Expense'])],
                'category' => ['required', 'string', 'max:255'],
                'term' => ['nullable', 'integer', 'between:1,3'],
                'session' => ['nullable', 'string', 'max:9'],
                'paymentMethod' => ['nullable', Rule::in(['Cash', 'Transfer', 'POS'])],
                'amountPaid' => ['required', 'numeric', 'min:0.01'],
                'date' => ['required', 'date'],
                'reference' => ['nullable', 'string', 'max:100'],
                'payerName' => ['nullable', 'string', 'max:255'],
                'notes' => ['nullable', 'string', 'max:500'],
            ], [
                'studentId.required' => 'Please select a student for income transactions.',
                'studentId.exists' => 'The selected student does not exist.',
                'amountPaid.required' => 'Please enter the amount paid.',
                'amountPaid.min' => 'Amount must be greater than zero.',
                'date.required' => 'Please select a transaction date.',
            ]);

            $transaction = Transaction::query()->create([
                'tenant_id' => $user?->tenant_id,
                'student_id' => $data['studentId'],
                'type' => $data['type'],
                'category' => $data['category'],
                'term' => $data['type'] === 'Income' ? $data['term'] : null,
                'session' => $data['type'] === 'Income' ? $data['session'] : null,
                'payment_method' => $data['type'] === 'Income' ? $data['paymentMethod'] : null,
                'amount_paid' => $data['amountPaid'],
                'reference' => $data['type'] === 'Income' && !empty($this->reference) ? trim($this->reference) : null,
                'payer_name' => $data['type'] === 'Income' && !empty($this->payerName) ? trim($this->payerName) : null,
                'notes' => !empty($this->notes) ? trim($this->notes) : null,
                'date' => Carbon::parse($data['date'])->toDateString(),
            ]);

            Audit::log('billing.transaction_created', $transaction, [
                'type' => $transaction->type,
                'student_id' => $transaction->student_id,
                'category' => $transaction->category,
                'term' => $transaction->term,
                'session' => $transaction->session,
                'amount_paid' => (string) $transaction->amount_paid,
                'payment_method' => $transaction->payment_method,
                'reference' => $transaction->reference,
                'payer_name' => $transaction->payer_name,
            ]);

            if ($transaction->type === 'Income') {
                $this->receiptTransactionId = $transaction->id;
                $this->showReceiptModal = true;
            }

            $this->reset(['studentId', 'studentSearch', 'amountPaid', 'reference', 'payerName', 'notes']);

            // Force refresh of computed properties
            $this->dispatch('$refresh');

            $this->dispatch('alert', message: 'Transaction saved successfully!', type: 'success');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Billing saveTransaction error: ' . $e->getMessage());
            $this->dispatch('alert', message: 'Failed to save transaction: ' . $e->getMessage(), type: 'error');
        }
    }

    public function setPaymentMethod(string $method): void
    {
        if (in_array($method, ['Cash', 'Transfer', 'POS'], true)) {
            $this->paymentMethod = $method;
        }
    }

    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    public function applyStudentBalance(): void
    {
        $info = $this->selectedStudentBalance;
        if ($info && $info['balance'] > 0) {
            $this->amountPaid = (string) $info['balance'];
        }
    }

    public function addQuickAmount(float $amount): void
    {
        $current = (float) ($this->amountPaid ?: 0);
        $newAmount = $current + $amount;
        $this->amountPaid = (string) $newAmount;
    }

    public function setQuickAmount(float $amount): void
    {
        $this->amountPaid = (string) $amount;
    }

    public function clearAmount(): void
    {
        $this->amountPaid = '';
    }

    public function startVoid(int $transactionId): void
    {
        $this->voidingTransactionId = $transactionId;
        $this->voidReason = '';
    }

    public function cancelVoid(): void
    {
        $this->voidingTransactionId = null;
        $this->voidReason = '';
    }

    public function confirmVoid(int $transactionId): void
    {
        $user = auth()->user();
        abort_unless($user && $user->hasPermission('billing.void'), 403);
        abort_unless((int) $this->voidingTransactionId === (int) $transactionId, 422);

        $data = $this->validate([
            'voidReason' => ['nullable', 'string', 'max:255'],
        ]);

        $transaction = Transaction::query()->findOrFail($transactionId);
        if ($transaction->is_void) {
            return;
        }

        $transaction->update([
            'is_void' => true,
            'void_reason' => $data['voidReason'] ?: null,
            'voided_at' => now(),
            'voided_by' => auth()->id(),
        ]);

        Audit::log('billing.transaction_voided', $transaction, [
            'reason' => $transaction->void_reason,
        ]);

        $this->cancelVoid();

        // Force refresh of computed properties
        $this->dispatch('$refresh');

        $this->dispatch('alert', message: 'Transaction voided.', type: 'warning');
    }

    private function defaultSession(): string
    {
        $active = AcademicSession::activeName();
        if ($active) {
            return $active;
        }

        $year = (int) now()->format('Y');
        $next = $year + 1;

        return "{$year}/{$next}";
    }

    #[Computed]
    public function pluginBills()
    {
        $tenant = auth()->user()?->tenant;
        if (!$tenant) {
            return collect();
        }
        return \App\Models\TenantPluginBill::query()
            ->with('marketplaceComponent')
            ->where('tenant_id', $tenant->id)
            ->orderByDesc('id')
            ->get();
    }

    public function payPluginBill(int $billId)
    {
        $user = auth()->user();
        abort_unless($user && ($user->hasPermission('billing.transactions') || $user->hasPermission('fees.manage')), 403);

        $tenant = $user->tenant;
        abort_unless($tenant, 404);

        $bill = \App\Models\TenantPluginBill::where('tenant_id', $tenant->id)->findOrFail($billId);

        if ($bill->status === 'paid') {
            $this->dispatch('alert', message: 'This bill is already paid.', type: 'info');
            return;
        }

        $amountInKobo = (int) round($bill->total_due * 100);
        $email = $user->email ?: ($tenant->contact_email ?: 'admin@school.com');
        $reference = 'BILL_' . $bill->id . '_' . uniqid() . '_' . time();

        $secretKey = config('services.paystack.secret_key');

        $response = Http::withToken($secretKey)
            ->withOptions(['verify' => false])
            ->post('https://api.paystack.co/transaction/initialize', [
                'email' => $email,
                'amount' => $amountInKobo,
                'reference' => $reference,
                'callback_url' => route('paystack.callback'),
                'metadata' => [
                    'payment_type' => 'billing',
                    'bill_id' => $bill->id,
                    'tenant_id' => $tenant->id,
                ]
            ]);

        if (!$response->successful() || !$response->json('status')) {
            Log::error("Paystack Bill Payment initialization failed", [
                'response' => $response->json()
            ]);
            $msg = $response->json('message') ?? 'Unable to connect to Paystack gateway.';
            $this->errorMessage = 'Payment initialization failed: ' . $msg;
            $this->dispatch('alert', message: $this->errorMessage, type: 'error');
            return;
        }

        $authorizationUrl = $response->json('data.authorization_url');
        return redirect()->away($authorizationUrl);
    }

    public function verifyPluginBillPayment(string $reference): void
    {
        $user = auth()->user();
        abort_unless($user && ($user->hasPermission('billing.transactions') || $user->hasPermission('fees.manage')), 403);

        $response = \Illuminate\Support\Facades\Http::withToken(config('services.paystack.secret_key'))
            ->withOptions(['verify' => false])
            ->timeout(10)
            ->get("https://api.paystack.co/transaction/verify/{$reference}");

        if ($response->successful() && $response->json('data.status') === 'success') {
            if (preg_match('/^BILL_(\d+)_/', $reference, $matches)) {
                $billId = (int) $matches[1];
                $tenant = $user->tenant;
                abort_unless($tenant, 404);

                $bill = \App\Models\TenantPluginBill::where('tenant_id', $tenant->id)->findOrFail($billId);
                
                $paidKobo = (int) $response->json('data.amount');
                $expectedKobo = (int) round($bill->total_due * 100);

                if ($paidKobo < $expectedKobo) {
                    $this->dispatch('alert', message: 'Paid amount does not match the bill amount due.', type: 'error');
                    return;
                }

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

                $this->dispatch('alert', message: 'Payment successful! Plugin bill marked as paid.', type: 'success');
            } else {
                $this->dispatch('alert', message: 'Invalid payment reference format.', type: 'error');
            }
        } else {
            $this->dispatch('alert', message: 'Payment verification failed. Please try again.', type: 'error');
        }
    }

    public function render()
    {
        $user = auth()->user();
        abort_unless($user && ($user->hasPermission('billing.transactions') || $user->hasPermission('fees.manage') || $user->hasPermission('billing.view')), 403);

        return view('livewire.billing.index');
    }
}
