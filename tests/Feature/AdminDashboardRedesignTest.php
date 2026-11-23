<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardRedesignTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $role, string $email, string $name = 'Test User'): User
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => 'Password123!',
            'is_active' => true,
        ]);
        $user->role = $role;
        $user->save();

        return $user;
    }

    public function test_admin_dashboard_renders_non_financial_bongabong_metrics(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN, 'admin@bongabong.test', 'Admin User');
        $applicant = $this->createUser(User::ROLE_APPLICANT, 'applicant@bongabong.test', 'Juan Dela Cruz');

        $this->actingAs($applicant)
            ->post('/applications', [
                'applicant_name' => $applicant->name,
                'contact_number' => '09171234567',
                'email' => $applicant->email,
                'business_name' => 'Bongabong Hardware',
                'business_address' => 'Poblacion, Bongabong',
                'barangay' => 'Poblacion',
                'business_nature' => 'Retail',
                'ownership_type' => 'Sole Proprietorship',
                'number_of_employees' => 4,
                'dti_sec_type' => 'DTI',
                'dti_sec_number' => 'DTI-9991',
            ])
            ->assertRedirect();

        AuditLog::create([
            'actor_id' => $admin->id,
            'action' => 'auth.login',
            'subject_type' => User::class,
            'subject_id' => $admin->id,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertSee('is-glass-portal', false);
        $response->assertSee('is-admin-portal', false);
        $response->assertSee('id="desktop-sidebar"', false);
        $response->assertSee('id="sidebar-collapse-btn"', false);
        $response->assertSee('id="mobile-navigation-drawer"', false);
        $response->assertSee('id="mobile-drawer-open"', false);
        $response->assertSee('adm-mobile-bottom-nav', false);
        $response->assertSee('ADMINISTRATION WORKSPACE');
        $response->assertSee('Good day,');
        $response->assertSee('Admin User!');
        $response->assertSee('Total Users');
        $response->assertSee('Total Application');
        $response->assertSee('Total Permits');
        $response->assertSee('Active Permits');
        $response->assertSee('Expired Permits');
        $response->assertDontSee('Total Payments');
        $response->assertDontSee('/admin/fee-types', false);
        $response->assertDontSee('/admin/fee-rates', false);
        $response->assertDontSee('Bansud');
        $response->assertSee('Bongabong Business Permits Office');
        $response->assertSee('Recent Activities');
        $response->assertSee('Quick Administration');
        $response->assertSee('Users &amp; Roles', false);
        $response->assertSee('Requirements');
        $response->assertSee('Audit Logs');
        $response->assertSee('Settings');
        $response->assertSee('adm-activity-mobile-card', false);
        $response->assertSee('AUTH LOGIN');

        $response->assertViewHas('counts', fn (array $counts) => $counts['users'] === 2
            && $counts['applications'] === 1
            && ! array_key_exists('payments', $counts));
    }

    public function test_admin_subpages_share_admin_shell_and_roles_remain_isolated(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN, 'admin2@bongabong.test', 'Admin User');
        $bplo = $this->createUser(User::ROLE_BPLO, 'bplo@bongabong.test', 'BPLO Officer');

        foreach ([
            '/admin/users',
            '/admin/requirement-types',
            '/admin/audit-logs',
            '/admin/settings',
        ] as $route) {
            $res = $this->actingAs($admin)->get($route);
            $res->assertOk();
            $res->assertSee('is-admin-portal', false);
        }

        // Financial management routes belong to the Treasurer portal, not Admin.
        $this->actingAs($admin)->get('/treasurer/dashboard')->assertForbidden();
        $this->actingAs($admin)->get('/treasurer/fee-types')->assertForbidden();
        $this->actingAs($admin)->get('/treasurer/fee-rates')->assertForbidden();

        $this->actingAs($bplo)->get('/admin/dashboard')->assertForbidden();
        $bploDash = $this->actingAs($bplo)->get('/bplo/dashboard');
        $bploDash->assertOk();
        $bploDash->assertDontSee('is-admin-portal', false);
        $bploDash->assertDontSee('is-glass-portal', false);
    }
}
