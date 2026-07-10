<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class AnalyticsReport
{
    public function build(User $viewer, bool $isSuperAdminPage, array $filters): array
    {
        $usersQuery = User::query()
            ->whereIn('role', [UserRole::Admin, UserRole::Member]);

        if (! $isSuperAdminPage) {
            $usersQuery->where(function (Builder $query) use ($viewer) {
                $query->where('id', $viewer->id)
                    ->orWhere('created_by', $viewer->id);
            });
        }

        $userOptions = (clone $usersQuery)
            ->with('company:id,name')
            ->orderBy('name')
            ->get(['id', 'company_id', 'name', 'email', 'role']);

        $filteredUsers = clone $usersQuery;

        if ($isSuperAdminPage && ! empty($filters['company_id'])) {
            $filteredUsers->where('company_id', $filters['company_id']);
        }

        if (! empty($filters['role'])) {
            $filteredUsers->where('role', $filters['role']);
        }

        if (! empty($filters['user_id'])) {
            $filteredUsers->where('id', $filters['user_id']);
        }

        $dateFilter = function (Builder $query) use ($filters) {
            $this->applyDateFilters($query, $filters);
        };

        $shortUrlsQuery = ShortUrl::query()
            ->whereIn('user_id', (clone $filteredUsers)->select('id'));

        $this->applyDateFilters($shortUrlsQuery, $filters);

        $users = (clone $filteredUsers)
            ->with('company:id,name')
            ->withCount(['shortUrls as urls_count' => $dateFilter])
            ->withSum(['shortUrls as clicks_count' => $dateFilter], 'hits')
            ->orderBy('name')
            ->paginate(10, ['*'], 'users_page')
            ->withQueryString();

        $shortUrls = (clone $shortUrlsQuery)
            ->with('user.company:id,name')
            ->latest()
            ->paginate(15, ['*'], 'links_page')
            ->withQueryString();

        return [
            'filters' => $filters,
            'companies' => $isSuperAdminPage
                ? Company::query()->orderBy('name')->get(['id', 'name'])
                : collect(),
            'userOptions' => $userOptions,
            'users' => $users,
            'shortUrls' => $shortUrls,
            'stats' => [
                'users' => (clone $filteredUsers)->count(),
                'admins' => (clone $filteredUsers)->where('role', UserRole::Admin)->count(),
                'members' => (clone $filteredUsers)->where('role', UserRole::Member)->count(),
                'links' => (clone $shortUrlsQuery)->count(),
                'clicks' => (int) (clone $shortUrlsQuery)->sum('hits'),
            ],
        ];
    }

    private function applyDateFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }
    }
}
