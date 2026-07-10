<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\ShortUrl;
use App\Models\User;
use App\Services\ShortUrlCache;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, ShortUrlCache $cache): View
    {
        $user = $request->user('api');
        $query = ShortUrl::query();

        if ($user->isSuperAdmin()) {
            $query->with('user.company');
        } else {
            $query->where('user_id', $user->id);
        }

        $shortUrls = $query->latest()->get();

        $shortUrls->each(function (ShortUrl $shortUrl) use ($cache) {
            $shortUrl->setAttribute('display_hits', $cache->hits($shortUrl));
        });

        return view('dashboard', [
            'user' => $user,
            'recentShortUrls' => $shortUrls->take(5),
            'urlStats' => [
                'links' => $shortUrls->count(),
                'hits' => $shortUrls->sum('display_hits'),
            ],
            'directoryStats' => $this->directoryStats($user),
        ]);
    }

    private function directoryStats(User $user): array
    {
        if ($user->isSuperAdmin()) {
            return [
                'companies' => Company::query()->count(),
                'admins' => User::query()->where('role', UserRole::Admin)->count(),
                'members' => User::query()->where('role', UserRole::Member)->count(),
            ];
        }

        if ($user->isAdmin()) {
            $managedUsers = User::query()->where('created_by', $user->id);

            return [
                'team_users' => (clone $managedUsers)->count(),
                'admins' => (clone $managedUsers)->where('role', UserRole::Admin)->count(),
                'members' => (clone $managedUsers)->where('role', UserRole::Member)->count(),
            ];
        }

        return [];
    }
}
