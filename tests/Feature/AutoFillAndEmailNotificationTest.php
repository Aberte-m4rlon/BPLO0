<?php

namespace Tests\Feature;

use App\Mail\ApplicationProgressMail;
use App\Models\Application;
use App\Models\ApplicationRequirement;
use App\Models\FeeRate;
use App\Models\FeeType;
use App\Models\Notification;
use App\Models\RequirementType;
use App\Models\User;
use App\Services\ApplicationWorkflowService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AutoFillAndEmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registered_user_details_auto_fill_new_application_form(): void
    {
        $response = $this->post('/register', [
            'name' => 'Maria Clara Santos',
            'email' => 'maria@example.com',
            'contact_number' => '09171234567',
            'address' => 'Sitio Mabuhay, Brgy. Poblacion, Bansud',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect('/dashboard');

        $this->assertDatabaseHas('users', [
            'email' => 'maria@example.com',
            'contact_number' => '09171234567',
            'address' => 'Sitio Mabuhay, Brgy. Poblacion, Bansud',
        ]);

        $formPage = $this->get('/applications/create');
        $formPage->assertOk();
        $formPage->assertSee('value="Maria Clara Santos"', false);
        $formPage->assertSee('value="09171234567"', false);
        $formPage->assertSee('value="maria@example.com"', false);
        $formPage->assertSee('value="Sitio Mabuhay, Brgy. Poblacion, Bansud"', false);
        $formPage->assertSee('value="Poblacion"', false);
    }

    public function test_returning_applicant_and_legacy_profile_backfill_work(): void
    {
        $user = User::create([
            'name' => 'Juan Reyes',
            'email' => 'juan@example.com',
            'password' => 'Password123!',
            'is_active' => true,
        ]);
        $user->role = User::ROLE_APPLICANT;
        $user->save();

        $this->actingAs($user)
            ->post('/applications', [
                'applicant_name' => 'Juan Reyes',
                'contact_number' => '09181112222',
                'email' => 'juan.business@example.com',
                'business_name' => 'Reyes General Store',
                'business_address' => 'Zone 2, Pag-asa, Bansud',
                'barangay' => 'Pag-asa',
                'business_nature' => 'Retail',
                'ownership_type' => 'Sole Proprietorship',
                'number_of_employees' => 3,
                'dti_sec_type' => 'DTI',
                'dti_sec_number' => 'DTI-2026-001',
            ])
            ->assertRedirect();

        $user->refresh();
        $this->assertSame('09181112222', $user->contact_number);
        $this->assertSame('Zone 2, Pag-asa, Bansud', $user->address);

        $secondCreate = $this->actingAs($user)->get('/applications/create');
        $secondCreate->assertOk();
        $secondCreate->assertSee('value="Reyes General Store"', false);
        $secondCreate->assertSee('value="Zone 2, Pag-asa, Bansud"', false);
        $secondCreate->assertSee('value="Pag-asa"', false);
        $secondCreate->assertSee('value="Retail"', false);
    }

    public function test_progress_events_automatically_email_applicant_and_avoid_duplicates(): void
    {
        Mail::fake();

        $applicant = User::create([
            'name' => 'Ana Bautista',
            'email' => 'ana.account@example.com',
            'contact_number' => '09175556666',
            'address' => 'Rosacara, Bansud',
            'password' => 'Password123!',
            'is_active' => true,
        ]);
        $applicant->role = User::ROLE_APPLICANT;
        $applicant->save();

        $bplo = User::create([
            'name' => 'BPLO Officer',
            'email' => 'bplo@example.com',
            'password' => 'Password123!',
            'is_active' => true,
        ]);
        $bplo->role = User::ROLE_BPLO;
        $bplo->save();

        $treasurer = User::create([
            'name' => 'Treasurer Officer',
            'email' => 'treasurer@example.com',
            'password' => 'Password123!',
            'is_active' => true,
        ]);
        $treasurer->role = User::ROLE_TREASURER;
        $treasurer->save();

        $feeType = FeeType::create([
            'name' => 'Mayor Permit Fee',
            'code' => 'mayor_fee',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        FeeRate::create([
            'fee_type_id' => $feeType->id,
            'label' => 'Standard Rate',
            'amount' => 500,
            'calc_type' => 'fixed',
            'is_active' => true,
            'effective_from' => now()->subDay(),
        ]);

        $reqType = RequirementType::create([
            'name' => 'Barangay Clearance',
            'code' => 'brgy_clearance',
            'is_required' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $application = new Application([
            'applicant_name' => 'Ana Bautista',
            'contact_number' => '09175556666',
            'email' => 'ana.business@example.com',
            'business_name' => 'Ana Bakery',
            'business_address' => 'Rosacara, Bansud',
            'barangay' => 'Rosacara',
            'business_nature' => 'Bakery',
            'ownership_type' => 'Sole Proprietorship',
            'number_of_employees' => 2,
        ]);
        $application->user()->associate($applicant);
        $application->status = Application::STATUS_DRAFT;
        $application->save();

        $req = new ApplicationRequirement([
            'requirement_type_id' => $reqType->id,
            'checklist_status' => ApplicationRequirement::CHECK_COMPLETE,
            'original_filename' => 'clearance.pdf',
            'stored_path' => 'requirements/1/clearance.pdf',
        ]);
        $req->application()->associate($application);
        $req->save();

        $workflow = app(ApplicationWorkflowService::class);
        $payments = app(PaymentService::class);

        // 1. Submit application -> email sent to both application email and user email.
        $workflow->submit($application, $applicant);
        Mail::assertSent(ApplicationProgressMail::class, function (ApplicationProgressMail $mail): bool {
            if ($mail->notificationType !== 'application.submitted') {
                return false;
            }

            $mail->assertSeeInHtml('Application Submitted');
            $mail->assertSeeInText('Ana Bakery');

            return $mail->hasTo('ana.business@example.com')
                && $mail->hasTo('ana.account@example.com');
        });

        // 2. Begin Review
        $workflow->beginReview($application, $bplo);
        Mail::assertSent(ApplicationProgressMail::class, fn (ApplicationProgressMail $mail) => $mail->notificationType === 'application.under_review');

        // 3. Return for Correction -> remark included
        $workflow->returnForCorrection($application, $bplo, 'Please upload a clearer Barangay Clearance.');
        Mail::assertSent(ApplicationProgressMail::class, fn (ApplicationProgressMail $mail) => $mail->notificationType === 'application.for_correction'
            && str_contains($mail->progressBody, 'Please upload a clearer Barangay Clearance.'));

        // 4. Resubmit -> Under Review -> Approved for Payment
        $workflow->submit($application, $applicant);
        $workflow->beginReview($application, $bplo);
        $workflow->approveForPayment($application, $bplo);
        Mail::assertSent(ApplicationProgressMail::class, fn (ApplicationProgressMail $mail) => $mail->notificationType === 'application.approved_for_payment');

        // 5. Submit Payment -> PaymentSubmitted email
        $badPayment = $payments->startPayment($application, 'gcash', $applicant, [
            'reference_number' => '1111222233334',
        ]);
        Mail::assertSent(ApplicationProgressMail::class, fn (ApplicationProgressMail $mail) => $mail->notificationType === 'payment.submitted');

        // 6. Reject Payment -> payment.rejected email with reason
        $payments->reject($badPayment, 'Reference number did not match.', $treasurer);
        Mail::assertSent(ApplicationProgressMail::class, fn (ApplicationProgressMail $mail) => $mail->notificationType === 'payment.rejected'
            && str_contains($mail->progressBody, 'Reference number did not match.'));

        // 7. Re-submit valid payment & confirm -> strictly 1 payment.confirmed email sent (no duplicate!)
        $payment = $payments->startPayment($application->fresh(), 'gcash', $applicant, [
            'reference_number' => '1234567890123',
        ]);
        $payments->verify($payment, ['source' => 'bplo_confirm'], $treasurer);
        $confirmedEmails = Mail::sent(ApplicationProgressMail::class, fn (ApplicationProgressMail $mail) => $mail->notificationType === 'payment.confirmed');
        $this->assertCount(1, $confirmedEmails);

        // 8. Release Permit -> PermitReleased email contains permit details
        $permit = $workflow->releasePermit($application->fresh(), $bplo);
        Mail::assertSent(ApplicationProgressMail::class, function (ApplicationProgressMail $mail) use ($permit): bool {
            if ($mail->notificationType !== 'permit.released') {
                return false;
            }

            $mail->assertSeeInHtml($permit->permit_number);

            return $mail->application?->permit?->permit_number === $permit->permit_number;
        });

        $this->assertDatabaseHas('notifications', [
            'user_id' => $applicant->id,
            'type' => 'permit.released',
            'email_status' => 'sent',
        ]);
    }

    public function test_mail_transport_failure_does_not_abort_application_transition(): void
    {
        Mail::shouldReceive('to')->once()->andThrow(new \RuntimeException('SMTP connection refused'));

        $applicant = User::create([
            'name' => 'Pedro Cruz',
            'email' => 'pedro@example.com',
            'contact_number' => '09179998888',
            'address' => 'Poblacion, Bansud',
            'password' => 'Password123!',
            'is_active' => true,
        ]);
        $applicant->role = User::ROLE_APPLICANT;
        $applicant->save();

        $application = new Application([
            'applicant_name' => 'Pedro Cruz',
            'contact_number' => '09179998888',
            'email' => 'pedro@example.com',
            'business_name' => 'Pedro Hardware',
            'business_address' => 'Poblacion, Bansud',
            'barangay' => 'Poblacion',
            'business_nature' => 'Hardware',
            'ownership_type' => 'Sole Proprietorship',
            'number_of_employees' => 4,
        ]);
        $application->user()->associate($applicant);
        $application->status = Application::STATUS_DRAFT;
        $application->save();

        app(ApplicationWorkflowService::class)->submit($application, $applicant);

        $this->assertSame(Application::STATUS_SUBMITTED, $application->fresh()->status);

        $notification = Notification::where('user_id', $applicant->id)->firstOrFail();
        $this->assertSame('failed', $notification->email_status);
    }
}
