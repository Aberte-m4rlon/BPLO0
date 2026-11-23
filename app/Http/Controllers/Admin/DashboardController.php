<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\Permit;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = now()->startOfDay();

        $counts = [
            'users' => User::count(),
            'applications' => Application::count(),
            'permits' => Permit::count(),
            'active_permits' => Permit::where('status', Permit::STATUS_ACTIVE)
                ->whereDate('valid_until', '>=', $today)
                ->count(),
            'expired_permits' => Permit::where('status', Permit::STATUS_ACTIVE)
                ->whereDate('valid_until', '<', $today)
                ->count(),
        ];

        $recentActivities = AuditLog::with('actor')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $trends = [
            'users' => $this->dailyGrowthBadge(User::class, $counts['users']),
            'applications' => $this->dailyGrowthBadge(Application::class, $counts['applications']),
            'permits' => $this->dailyGrowthBadge(Permit::class, $counts['permits']),
            'active_permits' => $counts['permits'] > 0
                ? '+'.(int) round(($counts['active_permits'] / $counts['permits']) * 100).'%'
                : '+0%',
            'expired_permits' => $counts['permits'] > 0
                ? '+'.(int) round(($counts['expired_permits'] / $counts['permits']) * 100).'%'
                : '+0%',
        ];

        return view('admin.dashboard', [
            'counts' => $counts,
            'trends' => $trends,
            'recentActivities' => $recentActivities,
        ]);
    }

    /**
     * Compute a real database-backed percentage delta for records added today vs prior records.
     *
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $modelClass
     */
    private function dailyGrowthBadge(string $modelClass, int $total): string
    {
        if ($total <= 0) {
            return '+0%';
        }

        $todayCount = $modelClass::where('created_at', '>=', now()->startOfDay())->count();
        if ($todayCount <= 0) {
            return '+0%';
        }

        $prior = $total - $todayCount;
        if ($prior <= 0) {
            return '+100%';
        }

        return '+'.(int) min(999, round(($todayCount / $prior) * 100)).'%';
    }
}
