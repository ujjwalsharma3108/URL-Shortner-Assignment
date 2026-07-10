<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_sees_all_admins_members_urls_and_clicks(): void
    {
        $firstCompany = $this->company('Analytics One');
        $secondCompany = $this->company('Analytics Two');
        $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $admin = $this->user('First Analytics Admin', UserRole::Admin, $firstCompany);
        $member = $this->user('First Analytics Member', UserRole::Member, $firstCompany);
        $otherAdmin = $this->user('Second Analytics Admin', UserRole::Admin, $secondCompany);

        $this->shortUrl($admin, 'alladm1', 'https://example.com/all-admin', 4);
        $this->shortUrl($member, 'allmem1', 'https://example.com/all-member', 7);
        $this->shortUrl($otherAdmin, 'alladm2', 'https://example.com/other-admin', 5);
        $this->authenticateAs($superAdmin);

        $this->get(route('super-admin.analytics'))
            ->assertOk()
            ->assertSeeText('Super Admin Analytics')
            ->assertSeeText('First Analytics Admin')
            ->assertSeeText('First Analytics Member')
            ->assertSeeText('Second Analytics Admin')
            ->assertSeeText('Admin')
            ->assertSeeText('Member')
            ->assertSee('https://example.com/all-admin')
            ->assertSee('https://example.com/all-member')
            ->assertSee('https://example.com/other-admin')
            ->assertSeeText('Total clicks');
    }

    public function test_super_admin_can_filter_analytics_by_company_and_role(): void
    {
        $firstCompany = $this->company('Filter One');
        $secondCompany = $this->company('Filter Two');
        $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $firstAdmin = $this->user('Filtered Admin', UserRole::Admin, $firstCompany);
        $firstMember = $this->user('Filtered Member', UserRole::Member, $firstCompany);
        $secondMember = $this->user('Other Company Member', UserRole::Member, $secondCompany);

        $this->shortUrl($firstAdmin, 'filter1', 'https://example.com/filtered-admin', 2);
        $this->shortUrl($firstMember, 'filter2', 'https://example.com/filtered-member', 3);
        $this->shortUrl($secondMember, 'filter3', 'https://example.com/other-company-member', 9);
        $this->authenticateAs($superAdmin);

        $this->get(route('super-admin.analytics', [
            'company_id' => $firstCompany->id,
            'role' => UserRole::Member->value,
        ]))
            ->assertOk()
            ->assertSeeText('Filtered Member')
            ->assertSee('https://example.com/filtered-member')
            ->assertDontSee('https://example.com/filtered-admin')
            ->assertDontSee('https://example.com/other-company-member');
    }

    public function test_admin_analytics_contains_self_and_users_created_by_them_only(): void
    {
        $company = $this->company('Scoped Analytics');
        $admin = $this->user('Analytics Owner', UserRole::Admin, $company);
        $otherAdmin = $this->user('Other Analytics Admin', UserRole::Admin, $company);
        $managedAdmin = $this->user('Managed Analytics Admin', UserRole::Admin, $company, $admin);
        $managedMember = $this->user('Managed Analytics Member', UserRole::Member, $company, $admin);
        $hiddenMember = $this->user('Hidden Analytics Member', UserRole::Member, $company, $otherAdmin);

        $this->shortUrl($admin, 'owner01', 'https://example.com/owner-url', 2);
        $this->shortUrl($managedAdmin, 'manage1', 'https://example.com/managed-admin-url', 3);
        $this->shortUrl($managedMember, 'manage2', 'https://example.com/managed-member-url', 5);
        $this->shortUrl($hiddenMember, 'hidden1', 'https://example.com/hidden-member-url', 20);
        $this->authenticateAs($admin);

        $this->get(route('admin.analytics'))
            ->assertOk()
            ->assertSeeText('Admin Analytics')
            ->assertSeeText('Analytics Owner')
            ->assertSeeText('Managed Analytics Admin')
            ->assertSeeText('Managed Analytics Member')
            ->assertSee('https://example.com/owner-url')
            ->assertSee('https://example.com/managed-admin-url')
            ->assertSee('https://example.com/managed-member-url')
            ->assertDontSeeText('Hidden Analytics Member')
            ->assertDontSee('https://example.com/hidden-member-url');

        $this->get(route('admin.analytics', ['role' => UserRole::Member->value]))
            ->assertOk()
            ->assertSee('https://example.com/managed-member-url')
            ->assertDontSee('https://example.com/owner-url')
            ->assertDontSee('https://example.com/managed-admin-url');
    }

    public function test_analytics_date_filters_apply_to_urls_and_clicks(): void
    {
        $company = $this->company('Date Analytics');
        $admin = $this->user('Date Admin', UserRole::Admin, $company);
        $oldUrl = $this->shortUrl($admin, 'oldurl1', 'https://example.com/old-url', 30);
        $newUrl = $this->shortUrl($admin, 'newurl1', 'https://example.com/new-url', 6);

        DB::table('short_urls')->where('id', $oldUrl->id)->update([
            'created_at' => '2026-06-01 10:00:00',
            'updated_at' => '2026-06-01 10:00:00',
        ]);
        DB::table('short_urls')->where('id', $newUrl->id)->update([
            'created_at' => '2026-07-10 10:00:00',
            'updated_at' => '2026-07-10 10:00:00',
        ]);
        $this->authenticateAs($admin);

        $this->get(route('admin.analytics', [
            'from' => '2026-07-01',
            'to' => '2026-07-31',
        ]))
            ->assertOk()
            ->assertSee('https://example.com/new-url')
            ->assertDontSee('https://example.com/old-url');
    }

    public function test_analytics_pages_are_restricted_to_their_exact_roles(): void
    {
        $company = $this->company('Access Analytics');
        $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $admin = $this->user('Access Admin', UserRole::Admin, $company);
        $member = $this->user('Access Member', UserRole::Member, $company);

        $this->authenticateAs($superAdmin);
        $this->get(route('admin.analytics'))->assertForbidden();

        $this->authenticateAs($admin);
        $this->get(route('super-admin.analytics'))->assertForbidden();

        $this->authenticateAs($member);
        $this->get(route('super-admin.analytics'))->assertForbidden();
        $this->get(route('admin.analytics'))->assertForbidden();
    }

    private function company(string $name): Company
    {
        return Company::create([
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(5)),
        ]);
    }

    private function user(
        string $name,
        UserRole $role,
        Company $company,
        ?User $creator = null,
    ): User {
        return User::factory()->create([
            'name' => $name,
            'role' => $role,
            'company_id' => $company->id,
            'created_by' => $creator?->id,
        ]);
    }

    private function shortUrl(User $user, string $code, string $url, int $hits): ShortUrl
    {
        $shortUrl = ShortUrl::create([
            'user_id' => $user->id,
            'code' => $code,
            'original_url' => $url,
        ]);
        $shortUrl->forceFill(['hits' => $hits])->save();

        return $shortUrl->fresh();
    }

    private function authenticateAs(User $user): void
    {
        $token = Auth::guard('api')->login($user);

        $this->withUnencryptedCookie('token', $token);
    }
}
