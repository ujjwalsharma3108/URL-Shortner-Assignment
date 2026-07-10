<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_dashboard(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_login_and_open_the_dashboard_with_the_auth_cookie(): void
    {
        $password = 'secure-password';
        $user = User::factory()->create([
            'name' => 'Dashboard User',
            'password' => Hash::make($password),
        ]);

        $loginResponse = $this->postJson(route('login.store'), [
            'email' => $user->email,
            'password' => $password,
        ]);

        $token = $loginResponse->json('access_token');

        $loginResponse
            ->assertOk()
            ->assertPlainCookie('token', $token);

        $this->withUnencryptedCookie('token', $token)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Welcome back, Dashboard!')
            ->assertSee($user->email);
    }
}
