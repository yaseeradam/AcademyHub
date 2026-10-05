<?php

namespace App\Livewire\Procurement;

use App\Models\AcademicSession;
use App\Models\AcademicTerm;
use App\Models\ProcurementRecord;
use App\Models\Transaction;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.app')]
#[Title('School Cash Flow & Financial Intelligence')]
class CashFlow extends Component
{
    public string $timeframe = 'this_term'; // 'this_month', 'this_term', 'this_year', 'all', 'custom'
    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?string $session = null;
    public ?int $term = null;

    // Interactive Ledger Filters
    public string $ledgerTab = 'all'; // 'all', 'inflow', 'outflow'
    public string $ledgerSearch = '';
    public int $ledgerLimit = 20;

    public function boot(): void
    {
        abort_unless(
            in_array(auth()->user()?->role, ['admin', 'bursar', 'proprietor'], true) || auth()->user()?->is_super_admin,
            403,
            'Only administrators, bursars, and proprietors have access to school cash flow intelligence.'
        );
    }

    public function mount(): void
    {
        $this->session = AcademicSession::activeName() ?? (date('Y') . '/' . (date('Y') + 1));
        $this->term = AcademicTerm::activeTermNumber();
        $this->applyTimeframeDates();
    }

    public function __get($property)
    {
        if ($property === 'financialMetrics') {
            return $this->financialMetrics();
        }

        if ($property === 'ledgerEntries') {
            return $this->ledgerEntries();
        }

        return parent::__get($property);
    }

    public function updatedTimeframe(): void
    {
        $this->applyTimeframeDates();
    }

    public function updatedStartDate(): void
    {
        $this->timeframe = 'custom';
    }

    public function updatedEndDate(): void
    {
        $this->timeframe = 'custom';
    }

    public function updatedLedgerSearch(): void
    {
        $this->ledgerLimit = 20;
    }

    public function updatedLedgerTab(): void
    {
        $this->ledgerLimit = 20;
    }

    public function loadMoreLedger(): void
    {
        $this->ledgerLimit += 25;
    }

    private function applyTimeframeDates(): void
    {
        $today = Carbon::today();

        if ($this->timeframe === 'this_month') {
            $this->startDate = $today->copy()->startOfMonth()->toDateString();
            $this->endDate = $today->copy()->endOfMonth()->toDateString();
        } elseif ($this->timeframe === 'this_term') {
            $activeTerm = AcademicTerm::active();
            if ($activeTerm && $activeTerm->start_date && $activeTerm->end_date) {
                $this->startDate = $activeTerm->start_date->toDateString();
                $this->endDate = $activeTerm->end_date->toDateString();
            } else {
                $this->startDate = $today->copy()->subMonths(3)->startOfMonth()->toDateString();
                $this->endDate = $today->toDateString();
            }
        } elseif ($this->timeframe === 'this_year') {
            $this->startDate = $today->copy()->startOfYear()->toDateString();
            $this->endDate = $today->copy()->endOfYear()->toDateString();
        } elseif ($this->timeframe === 'all') {
            $this->startDate = null;
            $this->endDate = null;
        }
    }

    #[Computed]
    public function financialMetrics(): array
    {
        $tenantId = auth()->user()?->tenant_id ?? 1;

        // Inflows (Fee Payments & Income Transactions)
        $inflowQuery = Transaction::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('type', 'Income')
            ->where(function ($q) {
                $q->whereNull('is_void')->orWhere('is_void', false);
            });

        if ($this->startDate) {
            $inflowQuery->whereDate('date', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $inflowQuery->whereDate('date', '<=', $this->endDate);
        }

        $totalInflow = (float) $inflowQuery->sum('amount_paid');
        $inflowCount = $inflowQuery->count();

        // Inflows by category
        $inflowsByCategory = (clone $inflowQuery)
            ->selectRaw('COALESCE(category, "Tuition & School Fees") as category_name, SUM(amount_paid) as total')
            ->groupBy('category_name')
            ->orderByDesc('total')
            ->pluck('total', 'category_name')
            ->toArray();

        // Outflows (Procurement & Expense Records)
        $outflowQuery = ProcurementRecord::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('status', '!=', 'cancelled');

        if ($this->startDate) {
            $outflowQuery->whereDate('purchased_at', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $outflowQuery->whereDate('purchased_at', '<=', $this->endDate);
        }

        $totalOutflow = (float) $outflowQuery->sum('total_amount');
        $outflowCount = $outflowQuery->count();

        // Outflows by category
        $outflowsByCategory = (clone $outflowQuery)
            ->selectRaw('COALESCE(category, "General & Miscellaneous") as category_name, SUM(total_amount) as total')
            ->groupBy('category_name')
            ->orderByDesc('total')
            ->pluck('total', 'category_name')
            ->toArray();

        // Net Cash Flow calculations
        $netPosition = $totalInflow - $totalOutflow;
        $operatingRatio = $totalInflow > 0 ? round(($totalOutflow / $totalInflow) * 100, 1) : ($totalOutflow > 0 ? 100 : 0);
        $profitMargin = $totalInflow > 0 ? round(($netPosition / $totalInflow) * 100, 1) : ($netPosition < 0 ? -100 : 0);

        // Health Status Classification
        if ($netPosition >= 0) {
            if ($operatingRatio <= 45.0) {
                $healthStatus = 'Liquidity Fortress';
                $healthBadge = 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/30';
                $healthDescription = "Excellent financial strength. Operational expenditures consume only {$operatingRatio}% of revenue, preserving capital for school investments.";
            } elseif ($operatingRatio <= 75.0) {
                $healthStatus = 'Strong Surplus';
                $healthBadge = 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/30';
                $healthDescription = "Healthy operations with {$profitMargin}% retained margin. Procurement costs are well-aligned with collected student fees.";
            } else {
                $healthStatus = 'Moderate Surplus';
                $healthBadge = 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/30';
                $healthDescription = "Operating near capacity ({$operatingRatio}% spend). Recommend periodic audit of recurring facility, fuel, and supplies costs.";
            }
        } else {
            $healthStatus = 'Operating Deficit';
            $healthBadge = 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/30';
            $healthDescription = "Operating deficit of ₦" . number_format(abs($netPosition), 2) . ". Outflows currently exceed incoming fee collections in this period.";
        }

        return [
            'totalInflow'        => $totalInflow,
            'inflowCount'        => $inflowCount,
            'inflowsByCategory'  => $inflowsByCategory,
            'totalOutflow'       => $totalOutflow,
            'outflowCount'       => $outflowCount,
            'outflowsByCategory' => $outflowsByCategory,
            'netPosition'        => $netPosition,
            'operatingRatio'     => $operatingRatio,
            'profitMargin'       => $profitMargin,
            'healthStatus'       => $healthStatus,
            'healthBadge'        => $healthBadge,
            'healthDescription'  => $healthDescription,
        ];
    }

    #[Computed]
    public function ledgerEntries(): array
    {
        $tenantId = auth()->user()?->tenant_id ?? 1;
        $entries = collect();

        // 1. Inflows
        if ($this->ledgerTab === 'all' || $this->ledgerTab === 'inflow') {
            $inflowQ = Transaction::withoutGlobalScopes()
                ->with(['student'])
                ->where('tenant_id', $tenantId)
                ->where('type', 'Income')
                ->where(function ($q) {
                    $q->whereNull('is_void')->orWhere('is_void', false);
                });

            if ($this->startDate) {
                $inflowQ->whereDate('date', '>=', $this->startDate);
            }
            if ($this->endDate) {
                $inflowQ->whereDate('date', '<=', $this->endDate);
            }
            if (!empty($this->ledgerSearch)) {
                $s = '%' . trim($this->ledgerSearch) . '%';
                $inflowQ->where(function ($q) use ($s) {
                    $q->where('category', 'like', $s)
                        ->orWhere('receipt_number', 'like', $s)
                        ->orWhere('payment_method', 'like', $s)
                        ->orWhereHas('student', function ($sq) use ($s) {
                            $sq->where('first_name', 'like', $s)
                                ->orWhere('last_name', 'like', $s)
                                ->orWhere('admission_number', 'like', $s);
                        });
                });
            }

            $inflows = $inflowQ->latest('date')->latest('id')->limit(80)->get();
            foreach ($inflows as $in) {
                $studentName = $in->student ? ($in->student->first_name . ' ' . $in->student->last_name) : null;
                $entries->push([
                    'id'             => 'inflow-' . $in->id,
                    'type'           => 'inflow',
                    'date'           => $in->date ?? $in->created_at,
                    'title'          => $studentName ? ($studentName . ' • Fee Payment') : ($in->category ?? 'Income Inflow'),
                    'subtitle'       => $in->receipt_number ? ('Receipt #' . $in->receipt_number) : 'Direct Fee Collection',
                    'category'       => $in->category ?: 'Tuition & Fees',
                    'amount'         => (float) $in->amount_paid,
                    'payment_method' => $in->payment_method ?: 'Direct',
                    'status'         => 'Reconciled',
                ]);
            }
        }

        // 2. Outflows
        if ($this->ledgerTab === 'all' || $this->ledgerTab === 'outflow') {
            $outflowQ = ProcurementRecord::withoutGlobalScopes()
                ->where('tenant_id', $tenantId)
                ->where('status', '!=', 'cancelled');

            if ($this->startDate) {
                $outflowQ->whereDate('purchased_at', '>=', $this->startDate);
            }
            if ($this->endDate) {
                $outflowQ->whereDate('purchased_at', '<=', $this->endDate);
            }
            if (!empty($this->ledgerSearch)) {
                $s = '%' . trim($this->ledgerSearch) . '%';
                $outflowQ->where(function ($q) use ($s) {
                    $q->where('item_name', 'like', $s)
                        ->orWhere('vendor_name', 'like', $s)
                        ->orWhere('category', 'like', $s)
                        ->orWhere('receipt_number', 'like', $s);
                });
            }

            $outflows = $outflowQ->latest('purchased_at')->latest('id')->limit(80)->get();
            foreach ($outflows as $out) {
                $entries->push([
                    'id'             => 'outflow-' . $out->id,
                    'type'           => 'outflow',
                    'date'           => $out->purchased_at ?? $out->created_at,
                    'title'          => $out->item_name,
                    'subtitle'       => $out->vendor_name ? ('Vendor: ' . $out->vendor_name) : ($out->receipt_number ? 'Ref: ' . $out->receipt_number : 'Procurement Outlay'),
                    'category'       => $out->category ?: 'General & Supplies',
                    'amount'         => (float) $out->total_amount,
                    'payment_method' => $out->payment_method ?: 'Bank Transfer',
                    'status'         => ucfirst($out->status ?: 'approved'),
                ]);
            }
        }

        // Sort descending by date
        return $entries->sortByDesc(function ($item) {
            return $item['date'] ? Carbon::parse($item['date'])->timestamp : 0;
        })->values()->take($this->ledgerLimit)->all();
    }

    public function exportFinancialStatement(): StreamedResponse
    {
        $metrics = $this->financialMetrics;
        $filename = "School_Cash_Flow_Statement_" . date('Y_m_d') . ".csv";

        return response()->streamDownload(function () use ($metrics) {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['AcademyHub Executive Cash Flow & Financial Statement']);
            fputcsv($out, ['Timeframe:', $this->timeframe, 'Start Date:', $this->startDate ?? 'All Time', 'End Date:', $this->endDate ?? 'Present']);
            fputcsv($out, ['Generated At:', now()->format('Y-m-d H:i:s')]);
            fputcsv($out, []);

            fputcsv($out, ['=== EXECUTIVE SUMMARY ===']);
            fputcsv($out, ['Metric', 'Amount (NGN)']);
            fputcsv($out, ['Total Revenue Inflow (Fee Collections)', number_format($metrics['totalInflow'], 2, '.', '')]);
            fputcsv($out, ['Total Operating Outflow (Procurement Expenses)', number_format($metrics['totalOutflow'], 2, '.', '')]);
            fputcsv($out, ['Net School Position (Surplus / Deficit)', number_format($metrics['netPosition'], 2, '.', '')]);
            fputcsv($out, ['Operating Expense Ratio', $metrics['operatingRatio'] . '%']);
            fputcsv($out, ['Net Margin', $metrics['profitMargin'] . '%']);
            fputcsv($out, ['Financial Health Assessment', $metrics['healthStatus']]);
            fputcsv($out, []);

            fputcsv($out, ['=== REVENUE INFLOWS BY CATEGORY ===']);
            fputcsv($out, ['Category / Fee Component', 'Amount (NGN)']);
            foreach ($metrics['inflowsByCategory'] as $cat => $amt) {
                fputcsv($out, [$cat, number_format($amt, 2, '.', '')]);
            }
            fputcsv($out, []);

            fputcsv($out, ['=== OPERATING OUTFLOWS BY CATEGORY ===']);
            fputcsv($out, ['Procurement Category', 'Amount (NGN)']);
            foreach ($metrics['outflowsByCategory'] as $cat => $amt) {
                fputcsv($out, [$cat, number_format($amt, 2, '.', '')]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function render()
    {
        return view('livewire.procurement.cash-flow', [
            'metrics'       => $this->financialMetrics,
            'ledgerEntries' => $this->ledgerEntries,
        ]);
    }
}
