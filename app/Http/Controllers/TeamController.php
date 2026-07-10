<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user('api');
        $managedUsers = User::query()
            ->where('created_by', $user->id)
            ->with('adminInvitation')
            ->latest()
            ->get();

        return view('team.index', [
            'user' => $user,
            'managedUsers' => $managedUsers,
            'teamStats' => [
                'users' => $managedUsers->count(),
                'admins' => $managedUsers->where('role', UserRole::Admin)->count(),
                'members' => $managedUsers->where('role', UserRole::Member)->count(),
            ],
        ]);
    }
}
