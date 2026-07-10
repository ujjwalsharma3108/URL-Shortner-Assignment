<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ModuleNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_has_separate_company_url_and_analytics_pages(): void
    {
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $this->authenticateAs($user);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('short-urls.index'))
            ->assertSee(route('companies.index'))
            ->assertSee(route('super-admin.analytics'))
            ->assertDontSee(route('team.index'));

        $this->get(route('companies.index'))->assertOk()->assertSeeText('Company directory');
        $this->get(route('short-urls.index'))->assertOk()->assertSeeText('All short URLs');
    }

    public function test_admin_has_separate_team_url_and_analytics_pages(): void
    {
        $company = Company::create(['name' => 'Module Company', 'slug' => 'module-company']);
        $user = User::factory()->create([
            'role' => UserRole::Admin,
            'company_id' => $company->id,
        ]);
        $this->authenticateAs($user);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('short-urls.index'))
            ->assertSee(route('team.index'))
            ->assertSee(route('admin.analytics'))
            ->assertDontSee(route('companies.index'));

        $this->get(route('team.index'))->assertOk()->assertSeeText('Manage your team');
        $this->get(route('short-urls.index'))->assertOk()->assertSeeText('My short URLs');
    }

    public function test_role_specific_management_pages_remain_protected(): void
    {
        $company = Company::create(['name' => 'Protected Company', 'slug' => 'protected-company']);
        $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'company_id' => $company->id,
        ]);
        $member = User::factory()->create([
            'role' => UserRole::Member,
            'company_id' => $company->id,
        ]);

        $this->authenticateAs($superAdmin);
        $this->get(route('team.index'))->assertForbidden();

        $this->authenticateAs($admin);
        $this->get(route('companies.index'))->assertForbidden();

        $this->authenticateAs($member);
        $this->get(route('companies.index'))->assertForbidden();
        $this->get(route('team.index'))->assertForbidden();
        $this->get(route('short-urls.index'))->assertOk();
    }

    private function authenticateAs(User $user): void
    {
        $token = Auth::guard('api')->login($user);

        $this->withUnencryptedCookie('token', $token);
    }
}
