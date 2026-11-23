<?php

namespace Tests\Feature;

use App\Models\Application;
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

    public function test_admin_dashboard_renders_redesigned_components_and_real_database_metrics(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN, 'admin@bansud.test', 'Admin User');
        $applicant = $this->createUser(User::ROLE_APPLICANT, 'applicant@bansud.test', 'Juan Dela Cruz');

        $this->actingAs($applicant)
            ->post('/applications', [
                'applicant_name' => $applicant->name,
                'contact_number' => '09171234567',
                'email' => $applicant->email,
                'business_name' => 'Bansud Hardware',
                'business_address' => 'Poblacion, Bansud',
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
        $response->assertSee('Total Payments');
        $response->assertSee('Expired Permits');
        $response->assertSee('Recent Activities');
        $response->assertSee('Quick Administration');
        $response->assertSee('Users &amp; Roles', false);
        $response->assertSee('Requirements');
        $response->assertSee('Fee Types');
        $response->assertSee('Fee Rates');
        $response->assertSee('Audit Logs');
        $response->assertSee('Settings');
        $response->assertSee('Bansud Business Permits Office');
        $response->assertSee('adm-activity-mobile-card', false);
        $response->assertSee('AUTH LOGIN');

        $response->assertViewHas('counts', fn (array $counts) => $counts['users'] === 2 && $counts['applications'] === 1);
    }

    public function test_admin_subpages_share_admin_shell_and_non_admins_remain_isolated(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN, 'admin2@bansud.test', 'Admin User');
        $bplo = $this->createUser(User::ROLE_BPLO, 'bplo@bansud.test', 'BPLO Officer');

        foreach ([
            '/admin/users',
            '/admin/requirement-types',
            '/admin/fee-types',
            '/admin/fee-rates',
            '/admin/audit-logs',
            '/admin/settings',
        ] as $route) {
            $res = $this->actingAs($admin)->get($route);
            $res->assertOk();
            $res->assertSee('is-admin-portal', false);
        }

        $this->actingAs($bplo)->get('/admin/dashboard')->assertForbidden();
        $bploDash = $this->actingAs($bplo)->get('/bplo/dashboard');
        $bploDash->assertOk();
        $bploDash->assertDontSee('is-admin-portal', false);
    }
}
