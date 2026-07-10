<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\AdminInvitation;
use App\Models\Company;
use App\Services\UserInvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminInvitationController extends Controller
{
    public function store(Request $request, UserInvitationService $invitations): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email:rfc', 'max:255', 'not_regex:/[\r\n]/', 'unique:users,email'],
            'role' => ['required', Rule::in([UserRole::Admin->value, UserRole::Member->value])],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
        ]);

        $inviter = $request->user('api');
        $role = UserRole::from($data['role']);

        if ($inviter->isSuperAdmin()) {
            if ($role !== UserRole::Admin || empty($data['company_id'])) {
                throw ValidationException::withMessages([
                    'company_id' => 'Super admins must select a company and can invite administrators only.',
                ]);
            }

            $company = Company::findOrFail($data['company_id']);
            $anchor = 'company-management';
        } else {
            if (! $inviter->company) {
                throw ValidationException::withMessages([
                    'company_id' => 'Your administrator account is not assigned to a company.',
                ]);
            }

            $company = $inviter->company;
            $anchor = 'team-management';
        }

        $invitations->create($inviter, $company, $role, $data);

        $roleLabel = ucfirst($role->value);

        return redirect(route('dashboard').'#'.$anchor)
            ->with('status', "{$roleLabel} invitation for {$data['email']} has been queued.");
    }

    public function resend(
        Request $request,
        AdminInvitation $invitation,
        UserInvitationService $invitations,
    ): RedirectResponse {
        $inviter = $request->user('api');
        $invitation->loadMissing('user');

        abort_if(
            $inviter->isAdmin() && $invitation->user->created_by !== $inviter->id,
            403,
        );

        if ($invitation->isAccepted()) {
            return back()->withErrors([
                'invitation' => 'This user has already accepted the invitation.',
            ]);
        }

        $invitations->resend($invitation);
        $anchor = $inviter->isSuperAdmin() ? 'company-management' : 'team-management';

        return redirect(route('dashboard').'#'.$anchor)
            ->with('status', "A new invitation for {$invitation->user->email} has been queued.");
    }
}
