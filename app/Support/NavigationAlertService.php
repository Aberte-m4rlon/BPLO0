<?php

namespace App\Support;

use App\Models\Application;
use App\Models\AuditLog;
use App\Models\FeeRate;
use App\Models\FeeType;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Permit;
use App\Models\RequirementType;
use App\Models\User;

class NavigationAlertService
{
    /**
     * Build live navbar badge counts/dots and header notification items for the signed-in user.
     *
     * @return array{
     *     badges: array<string, array{count: int, dot: bool, tone: string, title: string}>,
     *     bell_count: int,
     *     unread_db_count: int,
     *     bell_items: array<int, array{title: string, body: string, url: string, tone: string, time: string, unread: bool}>
     * }
     */
    public function forUser(?User $user): array
    {
        if (! $user) {
            return [
                'badges' => [],
                'bell_count' => 0,
                'unread_db_count' => 0,
                'bell_items' => [],
            ];
        }

        $unreadDb = Notification::query()
            ->where('user_id', $user->getKey())
            ->whereNull('read_at')
            ->latest('created_at')
            ->limit(6)
            ->get();

        $unreadDbCount = Notification::query()
            ->where('user_id', $user->getKey())
            ->whereNull('read_at')
            ->count();

        $bellItems = [];
        foreach ($unreadDb as $notification) {
            $bellItems[] = [
                'title' => (string) $notification->title,
                'body' => (string) $notification->body,
                'url' => $this->notificationTargetUrl($user, $notification),
                'tone' => 'info',
                'time' => $notification->created_at?->diffForHumans(short: true) ?? 'now',
                'unread' => true,
            ];
        }

        $badges = [];
        $actionAlertCount = 0;

        if ($user->isTreasurer()) {
            $awaiting = Payment::query()
                ->where('status', Payment::STATUS_PROCESSING)
                ->where('verification_status', Payment::VERIFY_UNVERIFIED)
                ->count();

            $pendingPayments = Payment::query()
                ->where('status', Payment::STATUS_PENDING)
                ->count();

            $unstartedApproved = Application::query()
                ->where('status', Application::STATUS_APPROVED_FOR_PAYMENT)
                ->whereDoesntHave('payments', fn ($q) => $q->whereIn('status', [Payment::STATUS_PAID, Payment::STATUS_PROCESSING]))
                ->count();

            $pendingReceivables = $pendingPayments + $unstartedApproved;
            $verifiedToday = Payment::query()
                ->where('verification_status', Payment::VERIFY_VERIFIED)
                ->whereDate('verified_at', today())
                ->count();
            $totalPaid = Payment::query()->where('status', Payment::STATUS_PAID)->count();
            $activeFeeTypes = FeeType::where('is_active', true)->count();
            $activeFeeRates = FeeRate::where('is_active', true)->count();

            if ($awaiting > 0) {
                $badges['/treasurer/payments'] = [
                    'count' => $awaiting,
                    'dot' => true,
                    'tone' => 'danger',
                    'title' => "{$awaiting} awaiting verification",
                ];
                $bellItems[] = [
                    'title' => "{$awaiting} payment".($awaiting === 1 ? '' : 's').' awaiting verification',
                    'body' => 'Review submitted applicant receipts and confirm collections.',
                    'url' => url('/treasurer/payments?status=PROCESSING'),
                    'tone' => 'danger',
                    'time' => 'Queue',
                    'unread' => true,
                ];
            } elseif ($pendingReceivables > 0) {
                $badges['/treasurer/payments'] = [
                    'count' => $pendingReceivables,
                    'dot' => true,
                    'tone' => 'warning',
                    'title' => "{$pendingReceivables} pending assessments",
                ];
            }

            if ($pendingReceivables > 0) {
                $bellItems[] = [
                    'title' => "{$pendingReceivables} pending receivable".($pendingReceivables === 1 ? '' : 's'),
                    'body' => 'Approved applications awaiting fee payment submission.',
                    'url' => url('/treasurer/analytics'),
                    'tone' => 'warning',
                    'time' => 'Pending',
                    'unread' => true,
                ];
            }

            if ($awaiting > 0 || $pendingReceivables > 0) {
                $badges['/treasurer/dashboard'] = [
                    'count' => 0,
                    'dot' => true,
                    'tone' => $awaiting > 0 ? 'danger' : 'warning',
                    'title' => 'Active treasury items',
                ];
            }

            if ($verifiedToday > 0) {
                $badges['/treasurer/analytics'] = [
                    'count' => $verifiedToday,
                    'dot' => true,
                    'tone' => 'success',
                    'title' => "{$verifiedToday} verified today",
                ];
            } elseif ($totalPaid > 0) {
                $badges['/treasurer/analytics'] = [
                    'count' => $totalPaid,
                    'dot' => false,
                    'tone' => 'info',
                    'title' => "{$totalPaid} verified collections",
                ];
            }

            if ($activeFeeTypes > 0) {
                $badges['/treasurer/fee-types'] = [
                    'count' => $activeFeeTypes,
                    'dot' => false,
                    'tone' => 'subtle',
                    'title' => "{$activeFeeTypes} active fee types",
                ];
            }

            if ($activeFeeRates > 0) {
                $badges['/treasurer/fee-rates'] = [
                    'count' => $activeFeeRates,
                    'dot' => false,
                    'tone' => 'subtle',
                    'title' => "{$activeFeeRates} active fee rates",
                ];
            }

            $actionAlertCount = $awaiting + $pendingReceivables;
        } elseif ($user->isAdmin()) {
            $today = now()->startOfDay();
            $expiredPermits = Permit::query()
                ->where('status', Permit::STATUS_ACTIVE)
                ->whereDate('valid_until', '<', $today)
                ->count();
            $todayLogs = AuditLog::query()->where('created_at', '>=', $today)->count();
            $totalUsers = User::count();
            $activeRequirements = RequirementType::where('is_active', true)->count();

            if ($expiredPermits > 0 || $todayLogs > 0) {
                $badges['/admin/dashboard'] = [
                    'count' => 0,
                    'dot' => true,
                    'tone' => $expiredPermits > 0 ? 'danger' : 'info',
                    'title' => $expiredPermits > 0 ? "{$expiredPermits} expired permits" : "{$todayLogs} logs today",
                ];
            }

            if ($totalUsers > 0) {
                $badges['/admin/users'] = [
                    'count' => $totalUsers,
                    'dot' => false,
                    'tone' => 'subtle',
                    'title' => "{$totalUsers} registered users",
                ];
            }

            if ($activeRequirements > 0) {
                $badges['/admin/requirement-types'] = [
                    'count' => $activeRequirements,
                    'dot' => false,
                    'tone' => 'subtle',
                    'title' => "{$activeRequirements} active requirements",
                ];
            }

            if ($todayLogs > 0) {
                $badges['/admin/audit-logs'] = [
                    'count' => $todayLogs,
                    'dot' => true,
                    'tone' => 'info',
                    'title' => "{$todayLogs} audit events today",
                ];
                $bellItems[] = [
                    'title' => "{$todayLogs} system audit event".($todayLogs === 1 ? '' : 's').' today',
                    'body' => 'Review recent logins, role updates, and permit activity.',
                    'url' => url('/admin/audit-logs'),
                    'tone' => 'info',
                    'time' => 'Today',
                    'unread' => true,
                ];
            }

            if ($expiredPermits > 0) {
                $bellItems[] = [
                    'title' => "{$expiredPermits} expired permit".($expiredPermits === 1 ? '' : 's'),
                    'body' => 'Active permits past their valid-until date need review.',
                    'url' => url('/admin/dashboard'),
                    'tone' => 'danger',
                    'time' => 'Alert',
                    'unread' => true,
                ];
            }

            $actionAlertCount = $expiredPermits + ($todayLogs > 0 ? 1 : 0);
        } elseif ($user->isBplo()) {
            $reviewCount = Application::query()
                ->whereIn('status', [Application::STATUS_SUBMITTED, Application::STATUS_UNDER_REVIEW])
                ->count();
            $releaseCount = Application::query()
                ->where('status', Application::STATUS_PAYMENT_VERIFIED)
                ->count();

            if ($reviewCount + $releaseCount > 0) {
                $badges['/bplo/dashboard'] = [
                    'count' => 0,
                    'dot' => true,
                    'tone' => $reviewCount > 0 ? 'warning' : 'success',
                    'title' => 'Pending BPLO tasks',
                ];
            }

            if ($reviewCount > 0) {
                $badges['/bplo/applications'] = [
                    'count' => $reviewCount,
                    'dot' => true,
                    'tone' => 'danger',
                    'title' => "{$reviewCount} awaiting review",
                ];
                $bellItems[] = [
                    'title' => "{$reviewCount} application".($reviewCount === 1 ? '' : 's').' awaiting review',
                    'body' => 'Inspect submitted requirements and business details.',
                    'url' => url('/bplo/applications'),
                    'tone' => 'warning',
                    'time' => 'Queue',
                    'unread' => true,
                ];
            }

            if ($releaseCount > 0) {
                $badges['/bplo/permits'] = [
                    'count' => $releaseCount,
                    'dot' => true,
                    'tone' => 'success',
                    'title' => "{$releaseCount} ready for permit release",
                ];
                $bellItems[] = [
                    'title' => "{$releaseCount} permit".($releaseCount === 1 ? '' : 's').' ready to release',
                    'body' => 'Treasurer confirmed payment — issue official QR permit.',
                    'url' => url('/bplo/permits'),
                    'tone' => 'success',
                    'time' => 'Ready',
                    'unread' => true,
                ];
            }

            $actionAlertCount = $reviewCount + $releaseCount;
        } elseif ($user->isInspector()) {
            $today = now()->startOfDay();
            $soon = now()->addDays(30)->endOfDay();
            $expiredOrSoon = Permit::query()
                ->where('status', Permit::STATUS_ACTIVE)
                ->whereDate('valid_until', '<=', $soon)
                ->count();
            $expired = Permit::query()
                ->where('status', Permit::STATUS_ACTIVE)
                ->whereDate('valid_until', '<', $today)
                ->count();
            $activePermits = Permit::where('status', Permit::STATUS_ACTIVE)->count();

            if ($expiredOrSoon > 0) {
                $badges['/inspector/dashboard'] = [
                    'count' => 0,
                    'dot' => true,
                    'tone' => $expired > 0 ? 'danger' : 'warning',
                    'title' => "{$expiredOrSoon} permits need inspection",
                ];
                $badges['/inspector/permits'] = [
                    'count' => $expiredOrSoon,
                    'dot' => true,
                    'tone' => $expired > 0 ? 'danger' : 'warning',
                    'title' => "{$expiredOrSoon} expired or expiring soon",
                ];
                $bellItems[] = [
                    'title' => "{$expiredOrSoon} permit".($expiredOrSoon === 1 ? '' : 's').' expired or expiring soon',
                    'body' => 'Verify renewal compliance during field inspection.',
                    'url' => url('/inspector/permits'),
                    'tone' => $expired > 0 ? 'danger' : 'warning',
                    'time' => 'Monitor',
                    'unread' => true,
                ];
            } elseif ($activePermits > 0) {
                $badges['/inspector/permits'] = [
                    'count' => $activePermits,
                    'dot' => false,
                    'tone' => 'subtle',
                    'title' => "{$activePermits} active permits",
                ];
            }

            $actionAlertCount = $expiredOrSoon;
        } elseif ($user->isApplicant()) {
            $actionNeededApps = Application::query()
                ->where('user_id', $user->getKey())
                ->whereIn('status', [Application::STATUS_FOR_CORRECTION, Application::STATUS_APPROVED_FOR_PAYMENT])
                ->get();

            $draftCount = Application::query()
                ->where('user_id', $user->getKey())
                ->where('status', Application::STATUS_DRAFT)
                ->count();

            $dashAlert = $unreadDbCount + $actionNeededApps->count();
            if ($dashAlert > 0) {
                $badges['/dashboard'] = [
                    'count' => $dashAlert,
                    'dot' => true,
                    'tone' => 'danger',
                    'title' => "{$dashAlert} unread updates or actions",
                ];
            }

            if ($draftCount > 0) {
                $badges['/applications/create'] = [
                    'count' => $draftCount,
                    'dot' => true,
                    'tone' => 'warning',
                    'title' => "{$draftCount} draft application",
                ];
            }

            foreach ($actionNeededApps as $app) {
                $isPay = $app->status === Application::STATUS_APPROVED_FOR_PAYMENT;
                $bellItems[] = [
                    'title' => $isPay ? "Payment ready: {$app->business_name}" : "Correction needed: {$app->business_name}",
                    'body' => $isPay ? 'Submit your payment receipt so the Treasurer can verify it.' : 'Please review BPLO remarks and update your application.',
                    'url' => $isPay ? route('applications.pay', $app) : route('applications.show', $app),
                    'tone' => $isPay ? 'info' : 'warning',
                    'time' => 'Action',
                    'unread' => true,
                ];
            }

            $actionAlertCount = $actionNeededApps->count();
        }

        return [
            'badges' => $badges,
            'bell_count' => $unreadDbCount + $actionAlertCount,
            'unread_db_count' => $unreadDbCount,
            'bell_items' => array_slice($bellItems, 0, 8),
        ];
    }

    private function notificationTargetUrl(User $user, Notification $notification): string
    {
        if ($notification->application_id !== null) {
            if ($user->isApplicant()) {
                return url('/applications/'.$notification->application_id);
            }
            if ($user->isBplo()) {
                return url('/bplo/applications/'.$notification->application_id);
            }
        }

        return url($user->homePath());
    }
}
