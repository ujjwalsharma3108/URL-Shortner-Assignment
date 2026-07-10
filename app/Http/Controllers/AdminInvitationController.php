<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Jobs\SendAdminInvitation;
use App\Models\AdminInvitation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminInvitationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email:rfc', 'max:255', 'not_regex:/[\r\n]/', 'unique:users,email'],
        ]);

        $token = Str::random(64);

        $invitation = DB::transaction(function () use ($data, $request, $token) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Str::random(64),
                'role' => UserRole::Admin,
            ]);

            return AdminInvitation::create([
                'user_id' => $user->id,
                'invited_by' => $request->user('api')->id,
                'token_hash' => hash('sha256', $token),
                'expires_at' => now()->addHours(config('auth.admin_invitation_expire_hours')),
            ]);
        });

        SendAdminInvitation::dispatch($invitation, $token);

        return redirect(route('dashboard').'#admin-management')
            ->with('status', "Invitation for {$data['email']} has been queued.");
    }

    public function resend(AdminInvitation $invitation): RedirectResponse
    {
        abort_unless($invitation->user->isAdmin(), 404);

        if ($invitation->isAccepted()) {
            return back()->withErrors([
                'invitation' => 'This administrator has already accepted the invitation.',
            ]);
        }

        $token = Str::random(64);

        $invitation->update([
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addHours(config('auth.admin_invitation_expire_hours')),
        ]);

        SendAdminInvitation::dispatch($invitation, $token);

        return redirect(route('dashboard').'#admin-management')
            ->with('status', "A new invitation for {$invitation->user->email} has been queued.");
    }
}
