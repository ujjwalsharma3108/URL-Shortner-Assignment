<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Company;
use App\Services\UserInvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    public function store(Request $request, UserInvitationService $invitations): RedirectResponse
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:150', 'unique:companies,name'],
            'admin_name' => ['required', 'string', 'max:100'],
            'admin_email' => ['required', 'string', 'email:rfc', 'max:255', 'not_regex:/[\r\n]/', 'unique:users,email'],
        ]);

        DB::transaction(function () use ($data, $invitations, $request) {
            $company = Company::create([
                'name' => $data['company_name'],
                'slug' => $this->uniqueSlug($data['company_name']),
            ]);

            $invitations->create(
                $request->user('api'),
                $company,
                UserRole::Admin,
                [
                    'name' => $data['admin_name'],
                    'email' => $data['admin_email'],
                ],
            );
        });

        return redirect(route('dashboard').'#company-management')
            ->with('status', "{$data['company_name']} and its primary admin were created. The invitation has been queued.");
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'company';
        $slug = $base;
        $suffix = 2;

        while (Company::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
