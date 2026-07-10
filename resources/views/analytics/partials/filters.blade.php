<section class="panel filter-panel" aria-labelledby="filter-title">
    <div class="filter-head">
        <h2 id="filter-title">Filter statistics</h2>
        <p class="filter-hint">Date range filters URLs by their creation date.</p>
    </div>

    <form method="GET" action="{{ route($routeName) }}">
        <div class="filter-grid {{ $isSuperAdminPage ? 'super' : '' }}">
            @if ($isSuperAdminPage)
                <div class="field">
                    <label for="company-id">Company</label>
                    <select id="company-id" name="company_id">
                        <option value="">All companies</option>
                        @foreach ($companies as $company)
                            <option
                                value="{{ $company->id }}"
                                @selected((string) ($filters['company_id'] ?? '') === (string) $company->id)
                            >
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="field">
                <label for="role">Role</label>
                <select id="role" name="role">
                    <option value="">Admins &amp; Members</option>
                    <option value="admin" @selected(($filters['role'] ?? '') === 'admin')>Admin</option>
                    <option value="member" @selected(($filters['role'] ?? '') === 'member')>Member</option>
                </select>
            </div>

            <div class="field">
                <label for="user-id">User</label>
                <select id="user-id" name="user_id">
                    <option value="">All users</option>
                    @foreach ($userOptions as $optionUser)
                        <option
                            value="{{ $optionUser->id }}"
                            @selected((string) ($filters['user_id'] ?? '') === (string) $optionUser->id)
                        >
                            {{ $optionUser->name }} · {{ ucfirst($optionUser->role->value) }}
                            @if ($isSuperAdminPage)
                                · {{ $optionUser->company?->name ?? 'No company' }}
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="from">From date</label>
                <input id="from" name="from" type="date" value="{{ $filters['from'] ?? '' }}">
            </div>

            <div class="field">
                <label for="to">To date</label>
                <input id="to" name="to" type="date" value="{{ $filters['to'] ?? '' }}">
            </div>

            <div class="filter-actions">
                <button class="button" type="submit">Apply</button>
                <a class="button secondary" href="{{ route($routeName) }}">Reset</a>
            </div>
        </div>
    </form>
</section>
