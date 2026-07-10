<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Jobs\SendAdminInvitation;
use App\Mail\AdminInvitationMail;
use App\Models\AdminInvitation;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_a_company_and_its_primary_admin(): void
    {
        Queue::fake();
        $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $this->authenticateAs($superAdmin);

        $this->post(route('companies.store'), [
            'company_name' => 'Acme Travel',
            'admin_name' => 'Acme Admin',
            'admin_email' => 'admin@acme.test',
        ])->assertRedirect(route('dashboard').'#company-management');

        $company = Company::where('slug', 'acme-travel')->firstOrFail();
        $admin = User::where('email', 'admin@acme.test')->firstOrFail();
        $invitation = $admin->adminInvitation()->firstOrFail();

        $this->assertSame(UserRole::Admin, $admin->role);
        $this->assertSame($company->id, $admin->company_id);
        $this->assertSame($superAdmin->id, $admin->created_by);
        $this->assertSame($superAdmin->id, $invitation->invited_by);
        $this->assertTrue($invitation->isPending());
        Queue::assertPushed(SendAdminInvitation::class);
    }

    public function test_super_admin_can_invite_an_admin_to_an_existing_company(): void
    {
        Queue::fake();
        $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $company = $this->company('Northwind');
        $this->authenticateAs($superAdmin);

        $this->post(route('admin-invitations.store'), [
            'name' => 'Northwind Admin',
            'email' => 'admin@northwind.test',
            'role' => UserRole::Admin->value,
            'company_id' => $company->id,
        ])->assertRedirect(route('dashboard').'#company-management');

        $admin = User::where('email', 'admin@northwind.test')->firstOrFail();
        $this->assertSame($company->id, $admin->company_id);
        $this->assertSame($superAdmin->id, $admin->created_by);
        Queue::assertPushed(SendAdminInvitation::class);
    }

    public function test_admin_can_create_admins_and_members_for_their_own_company(): void
    {
        Queue::fake();
        $company = $this->company('Contoso');
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'company_id' => $company->id,
        ]);
        $this->authenticateAs($admin);

        foreach ([UserRole::Admin, UserRole::Member] as $role) {
            $this->post(route('admin-invitations.store'), [
                'name' => ucfirst($role->value).' User',
                'email' => $role->value.'@contoso.test',
                'role' => $role->value,
            ])->assertRedirect(route('dashboard').'#team-management');
        }

        foreach ([UserRole::Admin, UserRole::Member] as $role) {
            $createdUser = User::where('email', $role->value.'@contoso.test')->firstOrFail();
            $this->assertSame($role, $createdUser->role);
            $this->assertSame($company->id, $createdUser->company_id);
            $this->assertSame($admin->id, $createdUser->created_by);
        }

        Queue::assertPushed(SendAdminInvitation::class, 2);
    }

    public function test_admin_sees_only_users_created_by_them(): void
    {
        $company = $this->company('Fabrikam');
        $firstAdmin = User::factory()->create([
            'role' => UserRole::Admin,
            'company_id' => $company->id,
        ]);
        $secondAdmin = User::factory()->create([
            'role' => UserRole::Admin,
            'company_id' => $company->id,
        ]);
        User::factory()->create([
            'email' => 'visible@fabrikam.test',
            'company_id' => $company->id,
            'created_by' => $firstAdmin->id,
        ]);
        User::factory()->create([
            'email' => 'hidden@fabrikam.test',
            'company_id' => $company->id,
            'created_by' => $secondAdmin->id,
        ]);
        $this->authenticateAs($firstAdmin);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('visible@fabrikam.test')
            ->assertDontSee('hidden@fabrikam.test');
    }

    public function test_super_admin_sees_all_companies_and_their_admins(): void
    {
        $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $firstCompany = $this->company('First Company');
        $secondCompany = $this->company('Second Company');
        User::factory()->create([
            'email' => 'admin@first.test',
            'role' => UserRole::Admin,
            'company_id' => $firstCompany->id,
        ]);
        User::factory()->create([
            'email' => 'admin@second.test',
            'role' => UserRole::Admin,
            'company_id' => $secondCompany->id,
        ]);
        $this->authenticateAs($superAdmin);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('First Company')
            ->assertSeeText('Second Company')
            ->assertSee('admin@first.test')
            ->assertSee('admin@second.test');
    }

    public function test_admin_cannot_resend_another_admins_invitation(): void
    {
        Queue::fake();
        $company = $this->company('Scoped Company');
        $firstAdmin = User::factory()->create([
            'role' => UserRole::Admin,
            'company_id' => $company->id,
        ]);
        $secondAdmin = User::factory()->create([
            'role' => UserRole::Admin,
            'company_id' => $company->id,
        ]);
        $createdUser = User::factory()->create([
            'company_id' => $company->id,
            'created_by' => $secondAdmin->id,
        ]);
        $invitation = $this->invitation($createdUser, $secondAdmin);
        $this->authenticateAs($firstAdmin);

        $this->post(route('admin-invitations.resend', $invitation))
            ->assertForbidden();

        Queue::assertNothingPushed();
    }

    public function test_member_cannot_create_users(): void
    {
        Queue::fake();
        $company = $this->company('Member Company');
        $member = User::factory()->create([
            'role' => UserRole::Member,
            'company_id' => $company->id,
        ]);
        $this->authenticateAs($member);

        $this->post(route('admin-invitations.store'), [
            'name' => 'Blocked User',
            'email' => 'blocked@example.com',
            'role' => UserRole::Member->value,
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'blocked@example.com']);
        Queue::assertNothingPushed();
    }

    public function test_invited_member_can_set_a_password_and_activate_the_account(): void
    {
        $token = Str::random(64);
        $company = $this->company('Invite Company');
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'company_id' => $company->id,
        ]);
        $member = User::factory()->create([
            'name' => 'New Member',
            'email' => 'new-member@example.com',
            'email_verified_at' => null,
            'role' => UserRole::Member,
            'company_id' => $company->id,
            'created_by' => $admin->id,
        ]);
        $invitation = $this->invitation($member, $admin, $token);

        $this->get(route('admin-invitations.accept', ['token' => $token]))
            ->assertOk()
            ->assertSee('Set up your account')
            ->assertSeeText('Invite Company')
            ->assertSee($member->email);

        $this->post(route('admin-invitations.complete', ['token' => $token]), [
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('new-secure-password', $member->fresh()->password));
        $this->assertNotNull($member->fresh()->email_verified_at);
        $this->assertNotNull($invitation->fresh()->accepted_at);

        $this->get(route('admin-invitations.accept', ['token' => $token]))
            ->assertNotFound();
    }

    public function test_invitation_job_sends_role_aware_email_for_current_token(): void
    {
        Mail::fake();
        $token = Str::random(64);
        $company = $this->company('Mail Company');
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'company_id' => $company->id,
        ]);
        $member = User::factory()->create([
            'role' => UserRole::Member,
            'company_id' => $company->id,
            'created_by' => $admin->id,
        ]);
        $invitation = $this->invitation($member, $admin, $token);

        $job = new SendAdminInvitation($invitation, $token);
        $this->assertStringNotContainsString($token, serialize($job));
        $job->handle();

        Mail::assertSent(AdminInvitationMail::class, function (AdminInvitationMail $mail) use ($member, $token) {
            return $mail->hasTo($member->email)
                && $mail->envelope()->subject === 'You have been invited as a Member'
                && str_ends_with($mail->invitationUrl, $token);
        });
    }

    public function test_public_registration_is_disabled(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register')->assertNotFound();
    }

    private function company(string $name): Company
    {
        return Company::create([
            'name' => $name,
            'slug' => Str::slug($name),
        ]);
    }

    private function invitation(User $user, User $inviter, ?string $token = null): AdminInvitation
    {
        $token ??= Str::random(64);

        return AdminInvitation::create([
            'user_id' => $user->id,
            'invited_by' => $inviter->id,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addHours(72),
        ]);
    }

    private function authenticateAs(User $user): void
    {
        $token = Auth::guard('api')->login($user);

        $this->withUnencryptedCookie('token', $token);
    }
}
