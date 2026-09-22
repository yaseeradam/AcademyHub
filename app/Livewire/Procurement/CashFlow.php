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

        return parent::__get($property);
    }

    public function updatedTimeframe(): void
    {
        $this->applyTimeframeDates();
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

        // Net Cash Flow
        $netPosition = $totalInflow - $totalOutflow;
        $operatingRatio = $totalInflow > 0 ? round(($totalOutflow / $totalInflow) * 100, 1) : 0;
        $profitMargin = $totalInflow > 0 ? round(($netPosition / $totalInflow) * 100, 1) : 0;

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
        ];
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
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function render()
    {
        return view('livewire.procurement.cash-flow', [
            'metrics' => $this->financialMetrics,
        ]);
    }
}
