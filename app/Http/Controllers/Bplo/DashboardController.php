<?php

namespace App\Http\Controllers\Bplo;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\Notification;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $counts = [
            'total' => Application::whereNotNull('tracking_number')->count(),
            'pending_review' => Application::where('status', Application::STATUS_SUBMITTED)->count(),
            'in_review' => Application::where('status', Application::STATUS_UNDER_REVIEW)->count(),
            'for_correction' => Application::where('status', Application::STATUS_FOR_CORRECTION)->count(),
            'approved' => Application::where('status', Application::STATUS_APPROVED_FOR_PAYMENT)->count(),
            'awaiting_payment' => Application::where('status', Application::STATUS_PAYMENT_PENDING)->count(),
            'paid' => Application::where('status', Application::STATUS_PAYMENT_VERIFIED)->count(),
            'released' => Application::where('status', Application::STATUS_PERMIT_RELEASED)->count(),
        ];

        $recent = Application::whereNotNull('tracking_number')
            ->latest('submitted_at')
            ->limit(10)
            ->get();

        $reviewQueue = Application::query()
            ->whereIn('status', [Application::STATUS_SUBMITTED, Application::STATUS_UNDER_REVIEW])
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [Application::STATUS_SUBMITTED])
            ->oldest('submitted_at')
            ->limit(5)
            ->get();

        $releaseQueue = Application::query()
            ->where('status', Application::STATUS_PAYMENT_VERIFIED)
            ->oldest('updated_at')
            ->limit(5)
            ->get();

        $recentActivities = AuditLog::with('actor')
            ->latest('created_at')
            ->limit(4)
            ->get();

        $notifications = auth()->check()
            ? Notification::where('user_id', auth()->id())
                ->latest('created_at')
                ->limit(4)
                ->get()
            : collect();

        return view('bplo.dashboard', [
            'counts' => $counts,
            'recent' => $recent,
            'reviewQueue' => $reviewQueue,
            'releaseQueue' => $releaseQueue,
            'recentActivities' => $recentActivities,
            'notifications' => $notifications,
        ]);
    }
}
