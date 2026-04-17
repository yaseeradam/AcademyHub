<?php

namespace App\Livewire\Billing;

use App\Support\Audit;
use App\Models\AcademicSession;
use App\Models\FeeStructure;
use App\Models\Student;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Billing')]
class Index extends Component
{
    public string $tab = 'transactions';

    public ?int $studentId = null;
    public ?int $selectedClassId = null;
    public string $type = 'Income';
    public string $category = 'Tuition';
    public ?int $term = null;
    public ?string $session = null;
    public string $paymentMethod = 'Cash';
    public string $amountPaid = '';
    public string $date = '';

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

        $allowedTabs = ['debtors'];
        if ($canTransactions) {
            $allowedTabs[] = 'transactions';
        }
        if ($canFees) {
            $allowedTabs[] = 'fees';
        }

        $defaultTab = $canTransactions ? 'transactions' : ($canFees ? 'fees' : 'debtors');
        $this->tab = ($requestedTab && in_array($requestedTab, $allowedTabs, true)) ? $requestedTab : $defaultTab;
        $this->date = now()->toDateString();
        $this->session = $this->session ?? $this->defaultSession();
        $this->term = $this->term ?? 1;

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

    #[Computed]
    public function students()
    {
        $query = Student::query()
            ->with(['schoolClass', 'section'])
            ->where('status', 'Active')
            ->orderBy('last_name');

        // Filter by selected class if specified
        if ($this->selectedClassId) {
            $query->where('class_id', $this->selectedClassId);
        }

        return $query->get();
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
        $term = $this->debtorsTerm;
        $session = $this->debtorsSession;

        $feeRows = FeeStructure::query()
            ->where('category', $category)
            ->where(function ($q) use ($term) {
                if ($term === null) {
                    $q->whereNull('term');
                } else {
                    $q->whereNull('term')->orWhere('term', $term);
                }
            })
            ->where(function ($q) use ($session) {
                if (!$session) {
                    $q->whereNull('session');
                } else {
                    $q->whereNull('session')->orWhere('session', $session);
                }
            })
            ->get(['class_id', 'term', 'session', 'amount_due']);

        $feesByClass = $feeRows
            ->groupBy('class_id')
            ->map(function ($rows) use ($term, $session) {
                $best = null;
                $bestScore = -1;

                foreach ($rows as $row) {
                    $score = 0;
                    if ($row->term !== null) {
                        $score += 2;
                    }
                    if ($row->session !== null) {
                        $score += 1;
                    }

                    if ($score > $bestScore) {
                        $best = $row;
                        $bestScore = $score;
                    }
                }

                return $best?->amount_due ?? 0;
            });

        $paidQuery = Transaction::query()
            ->where('type', 'Income')
            ->where('category', $category)
            ->where('is_void', false);

        if ($term !== null) {
            $paidQuery->where('term', $term);
        }
        if ($session) {
            $paidQuery->where('session', $session);
        }

        $paidByStudent = $paidQuery
            ->selectRaw('student_id, COALESCE(SUM(amount_paid),0) as paid')
            ->groupBy('student_id')
            ->pluck('paid', 'student_id');

        return Student::query()
            ->with(['schoolClass', 'section'])
            ->where('status', 'Active')
            ->get()
            ->map(function (Student $student) use ($feesByClass, $paidByStudent) {
                $due = (float) ($feesByClass->get($student->class_id, 0) ?? 0);
                $paid = (float) ($paidByStudent[$student->id] ?? 0);
                $balance = max(0, $due - $paid);

                return [
                    'student' => $student,
                    'due' => $due,
                    'paid' => $paid,
                    'balance' => $balance,
                ];
            })
            ->filter(fn(array $row) => $row['balance'] > 0)
            ->sortByDesc('balance')
            ->values();
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
                    Rule::exists('students', 'id'),
                ],
                'type' => ['required', Rule::in(['Income', 'Expense'])],
                'category' => ['required', 'string', 'max:255'],
                'term' => ['nullable', 'integer', 'between:1,3'],
                'session' => ['nullable', 'string', 'max:9'],
                'paymentMethod' => ['nullable', Rule::in(['Cash', 'Transfer', 'POS'])],
                'amountPaid' => ['required', 'numeric', 'min:0'],
                'date' => ['required', 'date'],
            ], [
                'studentId.required' => 'Please select a student for income transactions.',
                'studentId.exists' => 'The selected student does not exist.',
                'amountPaid.required' => 'Please enter the amount paid.',
                'amountPaid.min' => 'Amount must be greater than zero.',
                'date.required' => 'Please select a transaction date.',
            ]);

            $transaction = Transaction::query()->create([
                'student_id' => $data['studentId'],
                'type' => $data['type'],
                'category' => $data['category'],
                'term' => $data['type'] === 'Income' ? $data['term'] : null,
                'session' => $data['type'] === 'Income' ? $data['session'] : null,
                'payment_method' => $data['type'] === 'Income' ? $data['paymentMethod'] : null,
                'amount_paid' => $data['amountPaid'],
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
            ]);

            $this->reset(['studentId', 'amountPaid']);
            $this->amountPaid = '';

            // Force refresh of computed properties
            $this->dispatch('$refresh');

            $this->dispatch('alert', message: 'Transaction saved successfully!', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('alert', message: 'Failed to save transaction. Please try again.', type: 'error');
        }
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

    public function render()
    {
        $user = auth()->user();
        abort_unless($user && ($user->hasPermission('billing.transactions') || $user->hasPermission('fees.manage')), 403);

        return view('livewire.billing.index');
    }
}
