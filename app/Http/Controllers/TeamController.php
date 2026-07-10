<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user('api');

        return view('team.index', [
            'user' => $user,
            'managedUsers' => User::query()
                ->where('created_by', $user->id)
                ->with('adminInvitation')
                ->latest()
                ->get(),
        ]);
    }
}
