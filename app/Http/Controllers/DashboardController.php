<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user('api');

        return view('dashboard', [
            'user' => $user,
            'companies' => $user->isSuperAdmin()
                ? Company::query()
                    ->withDirectoryCounts()
                    ->with(['admins' => fn ($query) => $query
                        ->with('adminInvitation')
                        ->oldest()])
                    ->latest()
                    ->get()
                : collect(),
            'managedUsers' => $user->isAdmin()
                ? User::query()
                    ->where('created_by', $user->id)
                    ->with('adminInvitation')
                    ->latest()
                    ->get()
                : collect(),
        ]);
    }
}
