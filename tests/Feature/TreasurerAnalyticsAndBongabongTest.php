<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\FeeRate;
use App\Models\FeeType;
use App\Models\Payment;
use App\Models\User;
use App\Services\ApplicationWorkflowService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TreasurerAnalyticsAndBongabongTest extends TestCase
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

    public function test_municipality_config_and_treasurer_totals_analytics_and_fee_management(): void
    {
        Mail::fake();

        $this->assertSame('Municipality of Bongabong', config('permit.municipality'));
        $this->assertSame('BONGABONG', config('permit.tracking_prefix'));
        $this->assertSame('BONGABONG', config('permit.sms.sender_id'));

        $treasurer = $this->createUser(User::ROLE_TREASURER, 'treasurer@bongabong.test', 'Treasurer Staff');
        $bplo = $this->createUser(User::ROLE_BPLO, 'bplo@bongabong.test', 'BPLO Officer');
        $applicant1 = $this->createUser(User::ROLE_APPLICANT, 'owner1@bongabong.test', 'Pedro Reyes');
        $applicant2 = $this->createUser(User::ROLE_APPLICANT, 'owner2@bongabong.test', 'Elena Cruz');

        $mayorFee = FeeType::create([
            'name' => "Mayor's Permit Fee",
            'code' => 'mayors_permit_fee',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $sanitaryFee = FeeType::create([
            'name' => 'Sanitary Inspection Fee',
            'code' => 'sanitary_fee',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        FeeRate::create([
            'fee_type_id' => $mayorFee->id,
            'label' => 'Standard Permit Fee',
            'amount' => 1200,
            'calc_type' => 'fixed',
            'is_active' => true,
            'effective_from' => now()->subDay(),
        ]);
        FeeRate::create([
            'fee_type_id' => $sanitaryFee->id,
            'label' => 'Standard Sanitary Fee',
            'amount' => 300,
            'calc_type' => 'fixed',
            'is_active' => true,
            'effective_from' => now()->subDay(),
        ]);

        $workflow = app(ApplicationWorkflowService::class);
        $paymentService = app(PaymentService::class);

        $app1 = new Application([
            'applicant_name' => $applicant1->name,
            'contact_number' => '09171234567',
            'email' => $applicant1->email,
            'business_name' => 'Bongabong Agri Supply',
            'business_address' => 'Brgy. Poblacion, Bongabong',
            'barangay' => 'Poblacion',
            'business_nature' => 'Retail',
            'ownership_type' => 'Sole Proprietorship',
            'number_of_employees' => 4,
            'dti_sec_type' => 'DTI',
            'dti_sec_number' => 'DTI-2026-101',
        ]);
        $app1->user_id = $applicant1->id;
        $app1->status = Application::STATUS_DRAFT;
        $app1->save();

        $workflow->submit($app1, $applicant1);
        $workflow->beginReview($app1, $bplo);
        $workflow->approveForPayment($app1, $bplo);

        $paidPayment = $paymentService->startPayment($app1, 'gcash', $applicant1, [
            'reference_number' => 'GCASH-BG-1001',
            'payer_name' => $applicant1->name,
        ]);
        $paymentService->verify($paidPayment, ['source' => 'bplo_confirm'], $treasurer);

        $app2 = new Application([
            'applicant_name' => $applicant2->name,
            'contact_number' => '09172223344',
            'email' => $applicant2->email,
            'business_name' => 'Carmundo Marine Goods',
            'business_address' => 'Brgy. Carmundo, Bongabong',
            'barangay' => 'Carmundo',
            'business_nature' => 'Trading',
            'ownership_type' => 'Sole Proprietorship',
            'number_of_employees' => 2,
            'dti_sec_type' => 'DTI',
            'dti_sec_number' => 'DTI-2026-202',
        ]);
        $app2->user_id = $applicant2->id;
        $app2->status = Application::STATUS_DRAFT;
        $app2->save();

        $workflow->submit($app2, $applicant2);
        $workflow->beginReview($app2, $bplo);
        $workflow->approveForPayment($app2, $bplo);

        $paymentService->startPayment($app2, 'maya', $applicant2, [
            'reference_number' => 'MAYA-BG-2002',
            'payer_name' => $applicant2->name,
        ]);

        // Treasurer dashboard displays totals, analytics, fee controls, and Bongabong branding.
        $dash = $this->actingAs($treasurer)->get('/treasurer/dashboard');
        $dash->assertOk();
        $dash->assertSee('is-glass-portal', false);
        $dash->assertSee('is-treasurer-portal', false);
        $dash->assertSee('Municipality of Bongabong');
        $dash->assertSee('Bongabong Business Permits Office');
        $dash->assertDontSee('Bansud');
        $dash->assertSee('Total Collected');
        $dash->assertSee('Collected This Month');
        $dash->assertSee('Awaiting Verification');
        $dash->assertSee('Pending Receivables');
        $dash->assertSee('Verified Payments');
        $dash->assertSee('1,500.00');
        $dash->assertSee('Monthly Collection Trend');
        $dash->assertSee('Revenue by Fee Category');
        $dash->assertSee('Collections by Barangay (Bongabong)');
        $dash->assertSee("Mayor's Permit Fee");
        $dash->assertSee('Sanitary Inspection Fee');
        $dash->assertSee('Poblacion');
        $dash->assertSee('GCash');
        $dash->assertSee('Carmundo Marine Goods');

        // Dedicated analytics page works across period filters.
        foreach (['all', 'month', 'year'] as $period) {
            $analyticsRes = $this->actingAs($treasurer)->get('/treasurer/analytics?period='.$period);
            $analyticsRes->assertOk();
            $analyticsRes->assertSee('Collections &amp; Revenue Analytics', false);
        }

        // Payments queue & CSV export work cleanly.
        $queueRes = $this->actingAs($treasurer)->get('/treasurer/payments');
        $queueRes->assertOk();
        $queueRes->assertSee('1,500.00');

        $exportRes = $this->actingAs($treasurer)->get('/treasurer/payments/export');
        $exportRes->assertOk();
        $this->assertStringContainsString('text/csv', (string) $exportRes->headers->get('content-type'));
        $csv = $exportRes->streamedContent();
        $this->assertStringContainsString('Bongabong Agri Supply', $csv);
        $this->assertStringContainsString('1500.00', $csv);

        // Treasurer manages Fee Types and Fee Rates.
        $this->actingAs($treasurer)->get('/treasurer/fee-types')->assertOk();
        $this->actingAs($treasurer)->post('/treasurer/fee-types', [
            'name' => 'Garbage Fee',
            'code' => 'garbage_fee',
            'is_active' => '1',
            'sort_order' => '3',
        ])->assertRedirect(route('treasurer.fee-types.index'));

        $this->actingAs($treasurer)->get('/treasurer/fee-rates')->assertOk();
        $garbage = FeeType::where('code', 'garbage_fee')->firstOrFail();
        $this->actingAs($treasurer)->post('/treasurer/fee-rates', [
            'fee_type_id' => $garbage->id,
            'label' => 'Municipal Garbage Rate',
            'amount' => '250.00',
            'calc_type' => 'fixed',
            'is_active' => '1',
        ])->assertRedirect(route('treasurer.fee-rates.index'));
    }
}
