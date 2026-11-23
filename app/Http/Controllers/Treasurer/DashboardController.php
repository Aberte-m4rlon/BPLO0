<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\FeeRate;
use App\Models\Payment;
use App\Services\FeeComputationService;
use App\Support\PaymentMethods;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(FeeComputationService $fees): View
    {
        $dataset = $this->buildFinancialDataset($fees);

        $pending = Payment::query()
            ->with('application')
            ->where('status', Payment::STATUS_PROCESSING)
            ->where('verification_status', Payment::VERIFY_UNVERIFIED)
            ->latest('created_at')
            ->limit(15)
            ->get();

        $recentVerified = Payment::query()
            ->with('application')
            ->where('status', Payment::STATUS_PAID)
            ->orderByDesc('verified_at')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        return view('treasurer.dashboard', [
            'counts' => $dataset['counts'],
            'totals' => $dataset['totals'],
            'analytics' => $dataset['analytics'],
            'pending' => $pending,
            'recentVerified' => $recentVerified,
        ]);
    }

    public function analytics(Request $request, FeeComputationService $fees): View
    {
        $period = (string) $request->query('period', 'all');
        $from = match ($period) {
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => null,
        };
        if (! in_array($period, ['all', 'month', 'year'], true)) {
            $period = 'all';
        }

        $dataset = $this->buildFinancialDataset($fees, $from);

        $recentTransactions = Payment::query()
            ->with('application')
            ->when($from !== null, fn ($q) => $q->where('created_at', '>=', $from))
            ->latest('created_at')
            ->limit(25)
            ->get();

        return view('treasurer.analytics', [
            'period' => $period,
            'totals' => $dataset['totals'],
            'analytics' => $dataset['analytics'],
            'recentTransactions' => $recentTransactions,
        ]);
    }

    /**
     * Aggregate financial KPIs, monthly series, method breakdown, fee-category revenue,
     * and Bongabong barangay collections directly from database records.
     *
     * @return array{counts: array<string, mixed>, totals: array<string, mixed>, analytics: array<string, mixed>}
     */
    private function buildFinancialDataset(FeeComputationService $fees, ?CarbonInterface $from = null): array
    {
        $allPayments = Payment::query()
            ->with('application:id,business_name,barangay,status')
            ->when($from !== null, fn ($q) => $q->where('created_at', '>=', $from))
            ->get();

        $paidPayments = $allPayments->where('status', Payment::STATUS_PAID);
        $awaitingPayments = $allPayments->filter(
            fn (Payment $p) => $p->status === Payment::STATUS_PROCESSING && $p->verification_status === Payment::VERIFY_UNVERIFIED
        );
        $pendingPayments = $allPayments->where('status', Payment::STATUS_PENDING);
        $failedPayments = $allPayments->where('status', Payment::STATUS_FAILED);
        $verifiedPayments = $allPayments->where('verification_status', Payment::VERIFY_VERIFIED);

        $totalCollected = (float) $paidPayments->sum('amount');
        $awaitingAmount = (float) $awaitingPayments->sum('amount');
        $awaitingCount = $awaitingPayments->count();

        // Include unpaid APPROVED_FOR_PAYMENT applications without active Payment records yet.
        $approvedAwaitingStart = Application::query()
            ->where('status', Application::STATUS_APPROVED_FOR_PAYMENT)
            ->whereDoesntHave('payments', fn ($q) => $q->whereIn('status', [Payment::STATUS_PAID, Payment::STATUS_PROCESSING]))
            ->get();
        $unstartedAssessmentAmount = 0.0;
        foreach ($approvedAwaitingStart as $app) {
            $unstartedAssessmentAmount += $fees->computeFor($app)->total;
        }
        $pendingAmount = (float) $pendingPayments->sum('amount') + $unstartedAssessmentAmount;
        $pendingCount = $pendingPayments->count() + $approvedAwaitingStart->count();

        $verifiedCount = $verifiedPayments->count();
        $verifiedTodayCount = $verifiedPayments->filter(fn (Payment $p) => $p->verified_at?->isToday())->count();
        $verifiedTodayAmount = (float) $paidPayments
            ->filter(fn (Payment $p) => ($p->verified_at ?? $p->payment_date ?? $p->created_at)?->isToday())
            ->sum('amount');

        // Always compute current vs previous calendar month across all paid payments.
        $allPaid = $from === null ? $paidPayments : Payment::query()->where('status', Payment::STATUS_PAID)->get();
        $thisMonthStart = now()->startOfMonth();
        $prevMonthStart = now()->subMonthNoOverflow()->startOfMonth();
        $prevMonthEnd = now()->subMonthNoOverflow()->endOfMonth();

        $collectedThisMonth = 0.0;
        $thisMonthCount = 0;
        $collectedPrevMonth = 0.0;

        foreach ($allPaid as $payment) {
            $dt = $payment->verified_at ?? $payment->payment_date ?? $payment->created_at;
            if ($dt === null) {
                continue;
            }
            if ($dt->greaterThanOrEqualTo($thisMonthStart)) {
                $collectedThisMonth += (float) $payment->amount;
                $thisMonthCount++;
            } elseif ($dt->between($prevMonthStart, $prevMonthEnd)) {
                $collectedPrevMonth += (float) $payment->amount;
            }
        }

        if ($collectedPrevMonth > 0) {
            $monthPct = (int) round((($collectedThisMonth - $collectedPrevMonth) / $collectedPrevMonth) * 100);
            $monthTrend = ($monthPct >= 0 ? '+' : '').$monthPct.'%';
        } else {
            $monthTrend = $collectedThisMonth > 0 ? '+100%' : '+0%';
        }

        $nonCancelled = $allPayments->where('status', '!=', Payment::STATUS_CANCELLED)->count();
        $collectionRate = $nonCancelled > 0 ? round(($paidPayments->count() / $nonCancelled) * 100, 1) : 0.0;
        $averagePayment = $paidPayments->count() > 0 ? round($totalCollected / $paidPayments->count(), 2) : 0.0;

        // 1. Monthly Series (last 6 months)
        $monthlySeries = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonthsNoOverflow($i);
            $key = $month->format('Y-m');
            $monthlySeries[$key] = [
                'label' => $month->format('M Y'),
                'short' => $month->format('M'),
                'amount' => 0.0,
                'count' => 0,
            ];
        }
        foreach ($allPaid as $payment) {
            $dt = $payment->verified_at ?? $payment->payment_date ?? $payment->created_at;
            if ($dt === null) {
                continue;
            }
            $key = Carbon::parse($dt)->format('Y-m');
            if (isset($monthlySeries[$key])) {
                $monthlySeries[$key]['amount'] += (float) $payment->amount;
                $monthlySeries[$key]['count']++;
            }
        }
        $maxMonthly = max(array_column($monthlySeries, 'amount') ?: [0]);
        foreach ($monthlySeries as &$row) {
            $row['bar_pct'] = $maxMonthly > 0 ? max(8, (int) round(($row['amount'] / $maxMonthly) * 100)) : 8;
        }
        unset($row);

        // 2. Payment Method Breakdown
        $methodKeys = array_unique(array_merge(
            array_keys((array) config('permit.payment.methods', ['gcash' => true, 'maya' => true, 'qrph' => true])),
            $paidPayments->pluck('method')->filter()->all(),
        ));
        $methodBreakdown = [];
        foreach ($methodKeys as $methodKey) {
            $subset = $paidPayments->where('method', $methodKey);
            $amount = (float) $subset->sum('amount');
            $methodBreakdown[] = [
                'key' => $methodKey,
                'label' => PaymentMethods::label((string) $methodKey),
                'amount' => $amount,
                'count' => $subset->count(),
                'share_pct' => $totalCollected > 0 ? round(($amount / $totalCollected) * 100, 1) : 0.0,
            ];
        }
        usort($methodBreakdown, fn (array $a, array $b) => $b['amount'] <=> $a['amount']);

        // 3. Fee Category Breakdown (decoded from Payment::breakdown_json['lines'])
        $categoryMap = [];
        foreach ($paidPayments as $payment) {
            $lines = $payment->breakdown_json['lines'] ?? [];
            foreach ($lines as $line) {
                $code = (string) ($line['code'] ?? $line['name'] ?? 'other');
                $name = (string) ($line['name'] ?? ucfirst(str_replace('_', ' ', $code)));
                $lineAmount = (float) ($line['amount'] ?? 0);
                if (! isset($categoryMap[$code])) {
                    $categoryMap[$code] = [
                        'code' => $code,
                        'name' => $name,
                        'amount' => 0.0,
                        'tx_count' => 0,
                    ];
                }
                $categoryMap[$code]['amount'] += $lineAmount;
                $categoryMap[$code]['tx_count']++;
            }
        }
        if ($categoryMap === []) {
            $activeSchedules = FeeRate::with('feeType')->where('is_active', true)->get();
            foreach ($activeSchedules as $schedule) {
                if (! $schedule->feeType) {
                    continue;
                }
                $code = $schedule->feeType->code;
                $categoryMap[$code] = [
                    'code' => $code,
                    'name' => $schedule->feeType->name,
                    'amount' => 0.0,
                    'tx_count' => 0,
                ];
            }
        }
        $categoryTotal = array_sum(array_column($categoryMap, 'amount'));
        $feeCategoryBreakdown = [];
        foreach ($categoryMap as $item) {
            $item['share_pct'] = $categoryTotal > 0 ? round(($item['amount'] / $categoryTotal) * 100, 1) : 0.0;
            $feeCategoryBreakdown[] = $item;
        }
        usort($feeCategoryBreakdown, fn (array $a, array $b) => $b['amount'] <=> $a['amount']);

        // 4. Barangay Collection Breakdown
        $barangayMap = [];
        foreach ($paidPayments as $payment) {
            $brgy = trim((string) ($payment->application?->barangay ?? 'Unspecified'));
            if ($brgy === '') {
                $brgy = 'Unspecified';
            }
            if (! isset($barangayMap[$brgy])) {
                $barangayMap[$brgy] = [
                    'barangay' => $brgy,
                    'amount' => 0.0,
                    'tx_count' => 0,
                ];
            }
            $barangayMap[$brgy]['amount'] += (float) $payment->amount;
            $barangayMap[$brgy]['tx_count']++;
        }
        $barangayCollections = array_values($barangayMap);
        foreach ($barangayCollections as &$row) {
            $row['share_pct'] = $totalCollected > 0 ? round(($row['amount'] / $totalCollected) * 100, 1) : 0.0;
        }
        unset($row);
        usort($barangayCollections, fn (array $a, array $b) => $b['amount'] <=> $a['amount']);

        // 5. Status Pipeline Summary
        $statusPipeline = [
            [
                'label' => 'Verified & Collected',
                'tone' => 'green',
                'count' => $paidPayments->count(),
                'amount' => $totalCollected,
            ],
            [
                'label' => 'Awaiting Verification',
                'tone' => 'amber',
                'count' => $awaitingCount,
                'amount' => $awaitingAmount,
            ],
            [
                'label' => 'Pending Assessments',
                'tone' => 'blue',
                'count' => $pendingCount,
                'amount' => $pendingAmount,
            ],
            [
                'label' => 'Rejected / Failed',
                'tone' => 'red',
                'count' => $failedPayments->count(),
                'amount' => (float) $failedPayments->sum('amount'),
            ],
        ];

        $counts = [
            'awaiting' => $awaitingCount,
            'verified_today' => $verifiedTodayCount,
            'total_verified' => $verifiedCount,
            'total_collected' => $totalCollected,
        ];

        $totals = [
            'total_collected' => $totalCollected,
            'collected_this_month' => $collectedThisMonth,
            'this_month_count' => $thisMonthCount,
            'collected_prev_month' => $collectedPrevMonth,
            'month_trend' => $monthTrend,
            'awaiting_amount' => $awaitingAmount,
            'awaiting_count' => $awaitingCount,
            'pending_amount' => $pendingAmount,
            'pending_count' => $pendingCount,
            'verified_count' => $verifiedCount,
            'verified_today_count' => $verifiedTodayCount,
            'verified_today_amount' => $verifiedTodayAmount,
            'failed_count' => $failedPayments->count(),
            'failed_amount' => (float) $failedPayments->sum('amount'),
            'average_payment' => $averagePayment,
            'collection_rate' => $collectionRate,
            'total_transactions' => $allPayments->count(),
        ];

        return [
            'counts' => $counts,
            'totals' => $totals,
            'analytics' => [
                'monthly_series' => array_values($monthlySeries),
                'method_breakdown' => $methodBreakdown,
                'fee_category_breakdown' => $feeCategoryBreakdown,
                'barangay_collections' => array_slice($barangayCollections, 0, 6),
                'status_pipeline' => $statusPipeline,
            ],
        ];
    }
}
