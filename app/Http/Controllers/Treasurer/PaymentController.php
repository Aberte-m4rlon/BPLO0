<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $statuses = [
            Payment::STATUS_PENDING,
            Payment::STATUS_PROCESSING,
            Payment::STATUS_PAID,
            Payment::STATUS_FAILED,
            Payment::STATUS_CANCELLED,
        ];

        $payments = Payment::query()
            ->with('application')
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = '%'.$request->query('q').'%';
                $query->where(function ($q) use ($term): void {
                    $q->where('tracking_number', 'like', $term)
                        ->orWhere('reference_number', 'like', $term);
                });
            })
            ->when(
                $request->filled('status') && in_array($request->query('status'), $statuses, true),
                fn ($query) => $query->where('status', $request->query('status')),
            )
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'collected_total' => (float) Payment::where('status', Payment::STATUS_PAID)->sum('amount'),
            'awaiting_total' => (float) Payment::where('status', Payment::STATUS_PROCESSING)
                ->where('verification_status', Payment::VERIFY_UNVERIFIED)
                ->sum('amount'),
            'awaiting_count' => Payment::where('status', Payment::STATUS_PROCESSING)
                ->where('verification_status', Payment::VERIFY_UNVERIFIED)
                ->count(),
            'paid_count' => Payment::where('status', Payment::STATUS_PAID)->count(),
            'failed_count' => Payment::where('status', Payment::STATUS_FAILED)->count(),
        ];

        return view('treasurer.index', [
            'payments' => $payments,
            'statuses' => $statuses,
            'summary' => $summary,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $statuses = [
            Payment::STATUS_PENDING,
            Payment::STATUS_PROCESSING,
            Payment::STATUS_PAID,
            Payment::STATUS_FAILED,
            Payment::STATUS_CANCELLED,
        ];

        $rows = Payment::query()
            ->with('application')
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = '%'.$request->query('q').'%';
                $query->where(function ($q) use ($term): void {
                    $q->where('tracking_number', 'like', $term)
                        ->orWhere('reference_number', 'like', $term);
                });
            })
            ->when(
                $request->filled('status') && in_array($request->query('status'), $statuses, true),
                fn ($query) => $query->where('status', $request->query('status')),
            )
            ->latest('created_at')
            ->get();

        $filename = 'bongabong-treasury-collections-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($rows): void {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }

            fputcsv($out, [
                'Tracking No.',
                'Business Name',
                'Barangay',
                'Applicant Name',
                'Method',
                'Reference No.',
                'Amount (PHP)',
                'Payment Status',
                'Verification Status',
                'Submitted At',
                'Verified At',
            ]);

            foreach ($rows as $payment) {
                fputcsv($out, [
                    $payment->tracking_number,
                    $payment->application?->business_name ?? '',
                    $payment->application?->barangay ?? '',
                    $payment->application?->applicant_name ?? '',
                    \App\Support\PaymentMethods::label($payment->method),
                    $payment->reference_number ?? '',
                    number_format((float) $payment->amount, 2, '.', ''),
                    $payment->status,
                    $payment->verification_status,
                    $payment->created_at?->format('Y-m-d H:i:s') ?? '',
                    $payment->verified_at?->format('Y-m-d H:i:s') ?? '',
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function show(Payment $payment): View
    {
        $payment->load(['application', 'transactions']);

        return view('treasurer.show', [
            'payment' => $payment,
            'application' => $payment->application,
        ]);
    }

    public function confirm(Payment $payment, PaymentService $payments): RedirectResponse
    {
        if (! $payment->isAwaitingVerification()) {
            return back()->with('error', 'This payment is not awaiting verification.');
        }

        try {
            // 'bplo_confirm' is the service's staff-confirmed source string; it
            // is kept unchanged so ManualGateway still accepts the verification.
            $payments->verify($payment, ['source' => 'bplo_confirm'], request()->user());
        } catch (\Throwable $e) {
            return back()->with('error', 'Payment confirmation failed: '.$e->getMessage());
        }

        return redirect()
            ->route('treasurer.payments.show', $payment)
            ->with('success', 'Payment confirmed. The application is now payment-verified and ready for permit release.');
    }

    public function reject(Request $request, Payment $payment, PaymentService $payments): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ], [
            'reason.required' => 'Please state why the payment was not accepted.',
        ]);

        try {
            $payments->reject($payment, $validated['reason'], $request->user());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('treasurer.payments.show', $payment)
            ->with('success', 'Payment rejected. The applicant has been asked to pay again.');
    }

    public function proof(Payment $payment): StreamedResponse
    {
        abort_if($payment->proof_path === null || ! Storage::disk('local')->exists($payment->proof_path), 404);

        return Storage::disk('local')->response($payment->proof_path, $payment->proof_original_name);
    }
}
