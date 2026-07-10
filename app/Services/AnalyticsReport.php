<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class AnalyticsReport
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function build(User $viewer, bool $isSuperAdminPage, array $filters): array
    {
        $directoryScope = $this->directoryScope($viewer, $isSuperAdminPage);

        $userOptions = (clone $directoryScope)
            ->with('company:id,name')
            ->orderBy('name')
            ->get(['id', 'company_id', 'name', 'email', 'role']);

        $filteredUsers = $this->applyDirectoryFilters(
            clone $directoryScope,
            $filters,
            $isSuperAdminPage,
        );

        $applyDateFilters = function (Builder $query) use ($filters): void {
            $this->applyDateFilters($query, $filters);
        };

        $shortUrlScope = ShortUrl::query()
            ->whereIn('user_id', (clone $filteredUsers)->select('id'));
        $this->applyDateFilters($shortUrlScope, $filters);

        return [
            'filters' => $filters,
            'companies' => $isSuperAdminPage
                ? Company::query()->orderBy('name')->get(['id', 'name'])
                : collect(),
            'userOptions' => $userOptions,
            'users' => (clone $filteredUsers)
                ->with('company:id,name')
                ->withCount(['shortUrls as urls_count' => $applyDateFilters])
                ->withSum(['shortUrls as clicks_count' => $applyDateFilters], 'hits')
                ->orderBy('name')
                ->paginate(10, ['*'], 'users_page')
                ->withQueryString(),
            'shortUrls' => (clone $shortUrlScope)
                ->with('user.company:id,name')
                ->latest()
                ->paginate(15, ['*'], 'links_page')
                ->withQueryString(),
            'stats' => [
                'users' => (clone $filteredUsers)->count(),
                'admins' => (clone $filteredUsers)->where('role', UserRole::Admin)->count(),
                'members' => (clone $filteredUsers)->where('role', UserRole::Member)->count(),
                'links' => (clone $shortUrlScope)->count(),
                'clicks' => (int) (clone $shortUrlScope)->sum('hits'),
            ],
        ];
    }

    private function directoryScope(User $viewer, bool $isSuperAdminPage): Builder
    {
        return User::query()
            ->whereIn('role', [UserRole::Admin, UserRole::Member])
            ->when(! $isSuperAdminPage, function (Builder $query) use ($viewer) {
                $query->where(function (Builder $query) use ($viewer) {
                    $query->whereKey($viewer->id)
                        ->orWhere('created_by', $viewer->id);
                });
            });
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function applyDirectoryFilters(
        Builder $query,
        array $filters,
        bool $isSuperAdminPage,
    ): Builder {
        return $query
            ->when(
                $isSuperAdminPage && isset($filters['company_id']),
                fn (Builder $query) => $query->where('company_id', $filters['company_id']),
            )
            ->when(
                isset($filters['role']),
                fn (Builder $query) => $query->where('role', $filters['role']),
            )
            ->when(
                isset($filters['user_id']),
                fn (Builder $query) => $query->whereKey($filters['user_id']),
            );
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function applyDateFilters(Builder $query, array $filters): void
    {
        $query
            ->when(
                isset($filters['from']),
                fn (Builder $query) => $query->whereDate('created_at', '>=', $filters['from']),
            )
            ->when(
                isset($filters['to']),
                fn (Builder $query) => $query->whereDate('created_at', '<=', $filters['to']),
            );
    }
}
