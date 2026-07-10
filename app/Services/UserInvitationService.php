<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Jobs\SendAdminInvitation;
use App\Models\AdminInvitation;
use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class UserInvitationService
{
    /**
     * @param  array{name: string, email: string}  $attributes
     */
    public function create(
        User $inviter,
        Company $company,
        UserRole $role,
        array $attributes,
    ): AdminInvitation {
        if (! in_array($role, [UserRole::Admin, UserRole::Member], true)) {
            throw new InvalidArgumentException('Only administrators and members can be invited.');
        }

        if (! $inviter->isSuperAdmin()
            && (! $inviter->isAdmin() || $inviter->company_id !== $company->id)) {
            throw new AuthorizationException('You cannot invite users to this company.');
        }

        $token = Str::random(64);

        $invitation = DB::transaction(function () use ($attributes, $company, $inviter, $role, $token) {
            $user = User::create([
                'name' => $attributes['name'],
                'email' => $attributes['email'],
                'password' => Str::random(64),
                'role' => $role,
                'company_id' => $company->id,
                'created_by' => $inviter->id,
            ]);

            return AdminInvitation::create([
                'user_id' => $user->id,
                'invited_by' => $inviter->id,
                'token_hash' => hash('sha256', $token),
                'expires_at' => now()->addHours(config('auth.admin_invitation_expire_hours')),
            ]);
        });

        SendAdminInvitation::dispatch($invitation, $token);

        return $invitation;
    }

    public function resend(AdminInvitation $invitation): void
    {
        $token = Str::random(64);

        $invitation->update([
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addHours(config('auth.admin_invitation_expire_hours')),
        ]);

        SendAdminInvitation::dispatch($invitation, $token);
    }
}
