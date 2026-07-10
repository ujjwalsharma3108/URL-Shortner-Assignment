<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
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
            'admins' => $user->isSuperAdmin()
                ? User::query()
                    ->where('role', UserRole::Admin)
                    ->with('adminInvitation')
                    ->latest()
                    ->get()
                : collect(),
        ]);
    }
}
