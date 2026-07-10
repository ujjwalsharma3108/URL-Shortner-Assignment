<?php

namespace App\Http\Controllers;

use App\Models\AdminInvitation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcceptAdminInvitationController extends Controller
{
    public function show(string $token): View
    {
        $invitation = AdminInvitation::findValidByToken($token);

        abort_if($invitation === null, 404);

        return view('auth.accept-admin-invitation', [
            'invitation' => $invitation,
            'token' => $token,
        ]);
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        DB::transaction(function () use ($data, $token) {
            $invitation = AdminInvitation::query()
                ->where('token_hash', hash('sha256', $token))
                ->whereNull('accepted_at')
                ->where('expires_at', '>', now())
                ->lockForUpdate()
                ->firstOrFail();

            $invitation->user->forceFill([
                'password' => $data['password'],
                'email_verified_at' => now(),
            ])->save();

            $invitation->update([
                'accepted_at' => now(),
            ]);
        });

        return redirect()->route('login')
            ->with('status', 'Your account is ready. You can now sign in.');
    }
}
