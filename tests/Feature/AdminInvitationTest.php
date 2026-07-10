<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Jobs\SendAdminInvitation;
use App\Mail\AdminInvitationMail;
use App\Models\AdminInvitation;
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

    public function test_super_admin_can_create_an_admin_and_queue_an_invitation(): void
    {
        Queue::fake();
        $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $this->authenticateAs($superAdmin);

        $response = $this->post(route('admin-invitations.store'), [
            'name' => 'Invited Admin',
            'email' => 'invited@example.com',
        ]);

        $response->assertRedirect(route('dashboard').'#admin-management');

        $admin = User::where('email', 'invited@example.com')->firstOrFail();
        $invitation = $admin->adminInvitation()->firstOrFail();

        $this->assertSame(UserRole::Admin, $admin->role);
        $this->assertNull($admin->email_verified_at);
        $this->assertSame($superAdmin->id, $invitation->invited_by);
        $this->assertTrue($invitation->isPending());

        Queue::assertPushed(SendAdminInvitation::class, function (SendAdminInvitation $job) use ($invitation) {
            return $job->invitation->is($invitation)
                && str_contains($job->invitationUrl(), '/admin-invitations/accept/');
        });
    }

    public function test_admin_cannot_invite_another_admin(): void
    {
        Queue::fake();
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $this->authenticateAs($admin);

        $this->post(route('admin-invitations.store'), [
            'name' => 'Blocked Admin',
            'email' => 'blocked@example.com',
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'blocked@example.com']);
        Queue::assertNothingPushed();
    }

    public function test_invited_admin_can_set_a_password_and_activate_the_account(): void
    {
        $token = Str::random(64);
        $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $admin = User::factory()->create([
            'name' => 'New Admin',
            'email' => 'new-admin@example.com',
            'email_verified_at' => null,
            'role' => UserRole::Admin,
        ]);
        $invitation = AdminInvitation::create([
            'user_id' => $admin->id,
            'invited_by' => $superAdmin->id,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addHours(72),
        ]);

        $this->get(route('admin-invitations.accept', ['token' => $token]))
            ->assertOk()
            ->assertSee('Set up your admin account')
            ->assertSee($admin->email);

        $this->post(route('admin-invitations.complete', ['token' => $token]), [
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('new-secure-password', $admin->fresh()->password));
        $this->assertNotNull($admin->fresh()->email_verified_at);
        $this->assertNotNull($invitation->fresh()->accepted_at);

        $this->get(route('admin-invitations.accept', ['token' => $token]))
            ->assertNotFound();
    }

    public function test_invitation_job_sends_the_email_for_the_current_token(): void
    {
        Mail::fake();
        $token = Str::random(64);
        $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $invitation = AdminInvitation::create([
            'user_id' => $admin->id,
            'invited_by' => $superAdmin->id,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addHours(72),
        ]);

        $job = new SendAdminInvitation($invitation, $token);
        $this->assertStringNotContainsString($token, serialize($job));
        $job->handle();

        Mail::assertSent(AdminInvitationMail::class, function (AdminInvitationMail $mail) use ($admin, $token) {
            return $mail->hasTo($admin->email)
                && str_ends_with($mail->invitationUrl, $token);
        });
    }

    private function authenticateAs(User $user): void
    {
        $token = Auth::guard('api')->login($user);

        $this->withUnencryptedCookie('token', $token);
    }
}
