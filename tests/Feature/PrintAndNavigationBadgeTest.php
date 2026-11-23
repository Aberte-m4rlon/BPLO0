<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\AuditLog;
use App\Models\FeeRate;
use App\Models\FeeType;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Permit;
use App\Models\RequirementType;
use App\Models\User;
use App\Services\ApplicationWorkflowService;
use App\Services\PaymentService;
use App\Services\PermitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PrintAndNavigationBadgeTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $role, string $email, string $name): User
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => 'Password123!',
            'contact_number' => '09171234567',
            'address' => 'Poblacion, Bongabong, Oriental Mindoro',
            'is_active' => true,
        ]);
        $user->role = $role;
        $user->save();

        return $user;
    }

    public function test_treasurer_and_admin_nav_badges_bell_popover_and_mark_read_work(): void
    {
        Mail::fake();

        $treasurer = $this->createUser(User::ROLE_TREASURER, 'treasurer@bongabong.test', 'Treasurer Staff');
        $admin = $this->createUser(User::ROLE_ADMIN, 'admin@bongabong.test', 'Admin User');
        $bplo = $this->createUser(User::ROLE_BPLO, 'bplo@bongabong.test', 'BPLO Officer');
        $applicant = $this->createUser(User::ROLE_APPLICANT, 'applicant@bongabong.test', 'Juan Dela Cruz');

        $feeType = FeeType::create([
            'name' => "Mayor's Permit Fee",
            'code' => 'mayors_permit',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        FeeRate::create([
            'fee_type_id' => $feeType->id,
            'label' => 'Standard Fee',
            'amount' => 1000,
            'calc_type' => 'fixed',
            'is_active' => true,
            'effective_from' => now()->subDay(),
        ]);

        RequirementType::create([
            'name' => 'Barangay Clearance',
            'code' => 'barangay_clearance',
            'is_required' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $workflow = app(ApplicationWorkflowService::class);
        $paymentService = app(PaymentService::class);

        $app = new Application([
            'applicant_name' => $applicant->name,
            'contact_number' => '09171234567',
            'email' => $applicant->email,
            'business_name' => 'Bongabong Bakery',
            'business_address' => 'Brgy. Poblacion, Bongabong',
            'barangay' => 'Poblacion',
            'business_nature' => 'Bakery',
            'ownership_type' => 'Sole Proprietorship',
            'number_of_employees' => 3,
            'dti_sec_type' => 'DTI',
            'dti_sec_number' => 'DTI-2026-888',
        ]);
        $app->user_id = $applicant->id;
        $app->status = Application::STATUS_DRAFT;
        $app->save();

        // BPLO queue badge check while SUBMITTED
        $workflow->submit($app, $applicant);
        $bploRes = $this->actingAs($bplo)->get('/bplo/dashboard');
        $bploRes->assertOk();
        $bploRes->assertSee('nav-badge tone-danger', false);
        $bploRes->assertSee('awaiting review');

        $workflow->beginReview($app, $bplo);
        $workflow->approveForPayment($app, $bplo);

        $payment = $paymentService->startPayment($app, 'gcash', $applicant, [
            'reference_number' => 'GCASH-88801',
            'payer_name' => $applicant->name,
        ]);

        // Unread DB notification for Treasurer
        Notification::create([
            'user_id' => $treasurer->id,
            'application_id' => $app->id,
            'type' => 'payment.submitted',
            'title' => 'New GCash Receipt',
            'body' => 'Bongabong Bakery submitted a payment.',
            'channel' => 'database',
        ]);

        $trsRes = $this->actingAs($treasurer)->get('/treasurer/dashboard');
        $trsRes->assertOk();
        $trsRes->assertSee('notif-count', false);
        $trsRes->assertSee('notif-popover', false);
        $trsRes->assertSee('New GCash Receipt');
        $trsRes->assertSee('1 payment awaiting verification');
        $trsRes->assertSee('nav-icon-badge', false);
        $trsRes->assertSee('nav-bottom-badge', false);

        // Mark unread DB notification as read
        $this->actingAs($treasurer)
            ->post('/notifications/read-all')
            ->assertRedirect();
        $this->assertSame(0, Notification::where('user_id', $treasurer->id)->whereNull('read_at')->count());

        // Admin audit badge + users badge check
        AuditLog::create([
            'actor_id' => $admin->id,
            'action' => 'auth.login',
            'subject_type' => User::class,
            'subject_id' => $admin->id,
            'created_at' => now(),
        ]);

        $admRes = $this->actingAs($admin)->get('/admin/dashboard');
        $admRes->assertOk();
        $admRes->assertSee('nav-badge tone-subtle', false);
        $admRes->assertSee('nav-badge tone-info', false);
        $admRes->assertSee('notif-popover', false);
    }

    public function test_print_views_and_permit_pdf_render_official_headers_and_certificate_layout(): void
    {
        Mail::fake();
        Storage::fake('local');

        $treasurer = $this->createUser(User::ROLE_TREASURER, 'treasurer2@bongabong.test', 'Treasurer Staff');
        $bplo = $this->createUser(User::ROLE_BPLO, 'bplo2@bongabong.test', 'BPLO Officer');
        $applicant = $this->createUser(User::ROLE_APPLICANT, 'applicant2@bongabong.test', 'Maria Santos');

        $feeType = FeeType::create([
            'name' => 'Business Tax',
            'code' => 'business_tax',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        FeeRate::create([
            'fee_type_id' => $feeType->id,
            'label' => 'Flat Tax',
            'amount' => 850,
            'calc_type' => 'fixed',
            'is_active' => true,
            'effective_from' => now()->subDay(),
        ]);

        $app = new Application([
            'applicant_name' => $applicant->name,
            'contact_number' => '09175551234',
            'email' => $applicant->email,
            'business_name' => 'Santos General Merchandise',
            'business_address' => 'Brgy. Poblacion, Bongabong',
            'barangay' => 'Poblacion',
            'business_nature' => 'Retail',
            'ownership_type' => 'Sole Proprietorship',
            'number_of_employees' => 2,
            'dti_sec_type' => 'DTI',
            'dti_sec_number' => 'DTI-2026-777',
        ]);
        $app->user_id = $applicant->id;
        $app->status = Application::STATUS_DRAFT;
        $app->save();

        $workflow = app(ApplicationWorkflowService::class);
        $workflow->submit($app, $applicant);
        $workflow->beginReview($app, $bplo);
        $workflow->approveForPayment($app, $bplo);

        $paymentService = app(PaymentService::class);
        $payment = $paymentService->startPayment($app, 'gcash', $applicant, [
            'reference_number' => 'GCASH-77701',
            'payer_name' => $applicant->name,
        ]);
        $paymentService->verify($payment, ['source' => 'bplo_confirm'], $treasurer);

        $permit = app(PermitService::class)->release($app->fresh(), $bplo);
        $this->assertInstanceOf(Permit::class, $permit);
        $this->assertTrue(Storage::disk('local')->exists($permit->pdf_path));
        $pdfBytes = (string) Storage::disk('local')->get($permit->pdf_path);
        $this->assertStringStartsWith('%PDF-', $pdfBytes);
        $this->assertSame(1, preg_match_all('/\/Type\s*\/Page\b(?!s)/', $pdfBytes));

        // Check Treasurer printable pages
        $analytics = $this->actingAs($treasurer)->get('/treasurer/analytics');
        $analytics->assertOk();
        $analytics->assertSee('print-only-header', false);
        $analytics->assertSee('Official Collections &amp; Revenue Analytics Report', false);
        $analytics->assertSee('print-only-footer', false);

        $dash = $this->actingAs($treasurer)->get('/treasurer/dashboard');
        $dash->assertOk();
        $dash->assertSee('print-only-header', false);
        $dash->assertSee('Treasury Dashboard Executive Summary');

        $queue = $this->actingAs($treasurer)->get('/treasurer/payments');
        $queue->assertOk();
        $queue->assertSee('print-only-header', false);
        $queue->assertSee('Municipal Payments &amp; Collections Ledger', false);

        $receipt = $this->actingAs($treasurer)->get(route('treasurer.payments.show', $payment));
        $receipt->assertOk();
        $receipt->assertSee('print-only-header', false);
        $receipt->assertSee('Official Payment Verification Receipt');
        $receipt->assertSee('Print Receipt');

        // Check Applicant Payment History & Official Mayor's Permit web print
        $history = $this->actingAs($applicant)->get(route('applications.payments', $app));
        $history->assertOk();
        $history->assertSee('print-only-header', false);
        $history->assertSee('Applicant Payment History Receipt');

        $cert = $this->actingAs($applicant)->get(route('applications.permit', $app));
        $cert->assertOk();
        $cert->assertSee('THIS PERMIT IS HEREBY GRANTED TO');
        $cert->assertSee('permit-cert-validity-grid', false);
        $cert->assertSee('Office of the Municipal Mayor');
    }
}
