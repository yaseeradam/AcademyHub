<?php

namespace App\Support;

use App\Models\AcademicSession;
use App\Models\AcademicTerm;
use App\Models\FeeStructure;
use App\Models\Student;
use App\Models\Transaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BillingService
{
    /**
     * Resolves the configured fee amount due for a class, category, session, and term.
     * 
     * Fallback hierarchy:
     * 1. Exact match on (class_id, category, session, term)
     * 2. Full-session flat fee where (class_id, category, session, term IS NULL)
     * 3. Base fee from an earlier term in the same session (term <= $term)
     * 4. Global default fee for the class where (class_id, category, session IS NULL)
     * 5. Fallback: 0.0
     */
    public static function resolveFeeAmount(
        int $classId,
        string $category = 'Tuition',
        ?int $term = null,
        ?string $session = null,
        ?int $tenantId = null
    ): float {
        $term = $term ?? AcademicTerm::activeTermNumber();
        $session = $session ?? (AcademicTerm::activeSessionName() ?: date('Y') . '/' . (date('Y') + 1));

        $query = FeeStructure::query()
            ->where('class_id', $classId)
            ->where('category', $category)
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId));

        $rows = $query->get(['id', 'term', 'session', 'amount_due']);
        if ($rows->isEmpty()) {
            return 0.0;
        }

        // 1. Exact match
        $exact = $rows->first(fn ($r) => (int) $r->term === (int) $term && $r->session === $session);
        if ($exact) {
            return (float) $exact->amount_due;
        }

        // 2. Full-session per-term fee (term is null, session matches)
        $sessionFlat = $rows->first(fn ($r) => is_null($r->term) && $r->session === $session);
        if ($sessionFlat) {
            return (float) $sessionFlat->amount_due;
        }

        // 3. Fallback to earlier term in same session
        $earlier = $rows->filter(fn ($r) => $r->session === $session && !is_null($r->term) && (int) $r->term <= (int) $term)
            ->sortByDesc('term')
            ->first();
        if ($earlier) {
            return (float) $earlier->amount_due;
        }

        // 4. Global default for class (session is null)
        $global = $rows->first(fn ($r) => is_null($r->session));
        if ($global) {
            return (float) $global->amount_due;
        }

        // 5. Fallback to any fee record for this class & category
        return (float) ($rows->first()?->amount_due ?? 0.0);
    }

    /**
     * Calculates the complete multi-term financial ledger for a single student.
     * 
     * Properly differentiates:
     * - Current Term Fee Due & Paid
     * - Previous Term Arrears (unpaid balances from earlier terms in the session)
     * - Total Cumulative Outstanding Debt
     */
    public static function getStudentTermLedger(
        Student|int $student,
        ?string $session = null,
        ?int $upToTerm = null,
        string $category = 'Tuition'
    ): array {
        if (is_int($student)) {
            $student = Student::with(['schoolClass', 'section'])->findOrFail($student);
        }

        $session = $session ?? (AcademicTerm::activeSessionName() ?: date('Y') . '/' . (date('Y') + 1));
        $upToTerm = $upToTerm ?? AcademicTerm::activeTermNumber();
        $upToTerm = max(1, min(3, (int) $upToTerm));

        // Fetch all transactions for this student, category, and session
        $transactions = Transaction::query()
            ->where('student_id', $student->id)
            ->where('type', 'Income')
            ->where('category', $category)
            ->where('session', $session)
            ->where('is_void', false)
            ->get(['term', 'amount_paid', 'date', 'receipt_number']);

        $termsBreakdown = [];
        $totalDue = 0.0;
        $totalPaid = 0.0;
        $pastArrears = 0.0;

        for ($t = 1; $t <= $upToTerm; $t++) {
            $termDue = self::resolveFeeAmount($student->class_id, $category, $t, $session, $student->tenant_id);
            $termPaid = (float) $transactions->where('term', $t)->sum('amount_paid');
            $termBalance = max(0.0, $termDue - $termPaid);

            $isCurrent = ($t === $upToTerm);
            if (! $isCurrent) {
                $pastArrears += $termBalance;
            }

            $totalDue += $termDue;
            $totalPaid += $termPaid;

            $termsBreakdown[$t] = [
                'term'        => $t,
                'term_label'  => "Term {$t}",
                'due'         => $termDue,
                'paid'        => $termPaid,
                'balance'     => $termBalance,
                'is_current'  => $isCurrent,
            ];
        }

        // Also check if there are unallocated payments (where term is null)
        $unallocatedPaid = (float) $transactions->whereNull('term')->sum('amount_paid');
        $totalPaid += $unallocatedPaid;

        $currentTermDue = $termsBreakdown[$upToTerm]['due'] ?? 0.0;
        $currentTermPaid = $termsBreakdown[$upToTerm]['paid'] ?? 0.0;
        $currentTermBalance = max(0.0, $currentTermDue - $currentTermPaid);

        // Net total balance considering all payments and dues
        $totalBalance = max(0.0, $totalDue - $totalPaid);

        // Recalculate past arrears if unallocated payments existed
        if ($unallocatedPaid > 0) {
            $pastArrears = max(0.0, $totalBalance - $currentTermBalance);
        }

        return [
            'student_id'            => $student->id,
            'admission_number'      => $student->admission_number,
            'student_name'          => $student->full_name,
            'class_id'              => $student->class_id,
            'class_name'            => $student->schoolClass?->name ?? 'Class',
            'session'               => $session,
            'target_term'           => $upToTerm,
            'category'              => $category,
            'current_term_due'      => $currentTermDue,
            'current_term_paid'     => $currentTermPaid,
            'current_term_balance'  => $currentTermBalance,
            'past_arrears'          => $pastArrears,
            'total_due'             => $totalDue,
            'total_paid'            => $totalPaid,
            'total_balance'         => $totalBalance,
            'has_arrears'           => $pastArrears > 0.0,
            'is_fully_paid'         => $totalBalance <= 0.0,
            'terms_breakdown'       => $termsBreakdown,
        ];
    }

    /**
     * Batch calculates debtors across active students using 2 pre-aggregated queries.
     * Prevents N+1 database queries while recognizing both current term dues and past arrears.
     * 
     * @return Collection<int, array>
     */
    public static function getDebtors(
        string $category = 'Tuition',
        ?int $term = null,
        ?string $session = null,
        ?int $classId = null,
        ?string $search = null
    ): Collection {
        $category = trim($category) !== '' ? trim($category) : 'Tuition';
        $term = $term ?? AcademicTerm::activeTermNumber();
        $term = max(1, min(3, (int) $term));
        $session = $session ?? (AcademicTerm::activeSessionName() ?: date('Y') . '/' . (date('Y') + 1));

        // 1. Preload fee structures for this session/category
        $feeRows = FeeStructure::query()
            ->where('category', $category)
            ->where(function ($q) use ($session) {
                $q->whereNull('session')->orWhere('session', $session);
            })
            ->get(['class_id', 'term', 'session', 'amount_due']);

        $feeCache = [];
        $resolveCachedFee = function (int $clsId, int $t) use (&$feeCache, $feeRows, $session) {
            $key = "{$clsId}_{$t}";
            if (isset($feeCache[$key])) {
                return $feeCache[$key];
            }

            $rows = $feeRows->where('class_id', $clsId);
            if ($rows->isEmpty()) {
                return $feeCache[$key] = 0.0;
            }

            // Exact match
            $exact = $rows->first(fn ($r) => (int) $r->term === $t && $r->session === $session);
            if ($exact) {
                return $feeCache[$key] = (float) $exact->amount_due;
            }

            // Flat session fee
            $sessionFlat = $rows->first(fn ($r) => is_null($r->term) && $r->session === $session);
            if ($sessionFlat) {
                return $feeCache[$key] = (float) $sessionFlat->amount_due;
            }

            // Earlier term
            $earlier = $rows->filter(fn ($r) => $r->session === $session && !is_null($r->term) && (int) $r->term <= $t)
                ->sortByDesc('term')
                ->first();
            if ($earlier) {
                return $feeCache[$key] = (float) $earlier->amount_due;
            }

            // Global default for class
            $global = $rows->first(fn ($r) => is_null($r->session));
            return $feeCache[$key] = (float) ($global?->amount_due ?? ($rows->first()?->amount_due ?? 0.0));
        };

        // 2. Pre-aggregate paid amounts by student_id and term in a single query
        $payments = Transaction::query()
            ->selectRaw('student_id, term, COALESCE(SUM(amount_paid), 0) as paid')
            ->where('type', 'Income')
            ->where('category', $category)
            ->where('is_void', false)
            ->where(function ($q) use ($session) {
                $q->whereNull('session')->orWhere('session', $session);
            })
            ->groupBy('student_id', 'term')
            ->get();

        $paymentsGrouped = $payments->groupBy('student_id');

        // 3. Query active students
        $studentQuery = Student::query()
            ->with(['schoolClass:id,name,level', 'section:id,name', 'user:id,name,email'])
            ->where('status', 'Active');

        if ($classId) {
            $studentQuery->where('class_id', $classId);
        }

        if ($search && trim($search) !== '') {
            $s = trim($search);
            $studentQuery->where(function ($q) use ($s) {
                $q->where('first_name', 'like', "%{$s}%")
                    ->orWhere('last_name', 'like', "%{$s}%")
                    ->orWhere('admission_number', 'like', "%{$s}%");
            });
        }

        return $studentQuery
            ->get()
            ->map(function (Student $student) use ($term, $resolveCachedFee, $paymentsGrouped) {
                $studentPayments = $paymentsGrouped->get($student->id, collect());

                $totalDue = 0.0;
                $totalPaid = (float) $studentPayments->sum('paid');
                $pastArrears = 0.0;
                $currentDue = 0.0;
                $currentPaid = 0.0;

                for ($t = 1; $t <= $term; $t++) {
                    $dueForTerm = $resolveCachedFee($student->class_id, $t);
                    $paidForTerm = (float) $studentPayments->where('term', $t)->sum('paid');

                    $totalDue += $dueForTerm;

                    if ($t === $term) {
                        $currentDue = $dueForTerm;
                        $currentPaid = $paidForTerm;
                    } else {
                        $pastArrears += max(0.0, $dueForTerm - $paidForTerm);
                    }
                }

                $totalBalance = max(0.0, $totalDue - $totalPaid);
                $currentBalance = max(0.0, $currentDue - $currentPaid);

                // Adjust arrears if unallocated payments covered them
                if ($totalBalance < ($pastArrears + $currentBalance)) {
                    $pastArrears = max(0.0, $totalBalance - $currentBalance);
                }

                return [
                    'student'          => $student,
                    'current_due'      => $currentDue,
                    'current_paid'     => $currentPaid,
                    'current_balance'  => $currentBalance,
                    'past_arrears'     => $pastArrears,
                    'due'              => $totalDue, // Total expected across session
                    'paid'             => $totalPaid, // Total paid across session
                    'balance'          => $totalBalance, // Total remaining debt
                    'has_arrears'      => $pastArrears > 0.0,
                ];
            })
            ->filter(fn (array $row) => $row['balance'] > 0.0)
            ->sortByDesc('balance')
            ->values();
    }
}
