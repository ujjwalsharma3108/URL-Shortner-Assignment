<section class="panel">
    <header class="panel-header">
        <div>
            <h2 class="panel-title">All companies</h2>
            <p class="panel-subtitle">Admin and Member totals with administrator invitation status.</p>
        </div>
        <span class="count-chip">{{ $companies->count() }} companies</span>
    </header>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
            <tr>
                <th>Company</th>
                <th>Users</th>
                <th>Administrators</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($companies as $company)
                <tr>
                    <td>
                        <p class="person-name">{{ $company->name }}</p>
                        <p class="person-email">{{ $company->slug }}</p>
                    </td>
                    <td>
                        <span class="number">{{ $company->admins_count }}</span> admins ·
                        <span class="number">{{ $company->members_count }}</span> members
                    </td>
                    <td>
                        <div class="company-admin-list">
                            @forelse ($company->admins as $admin)
                                @php($invitation = $admin->adminInvitation)
                                <div class="company-admin">
                                    <div>
                                        <p class="person-name">{{ $admin->name }}</p>
                                        <p class="person-email">{{ $admin->email }}</p>
                                    </div>
                                    <div class="company-admin-actions">
                                        @if (! $invitation || $invitation->isAccepted())
                                            <span class="status-pill active">Active</span>
                                        @elseif ($invitation->isExpired())
                                            <span class="status-pill expired">Expired</span>
                                        @else
                                            <span class="status-pill pending">Pending</span>
                                        @endif

                                        @if ($invitation && ! $invitation->isAccepted())
                                            <form action="{{ route('admin-invitations.resend', $invitation) }}" method="POST">
                                                @csrf
                                                <button class="table-action" type="submit">Resend</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <span class="person-email">No administrators</span>
                            @endforelse
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="empty-state" colspan="3">No companies have been created yet.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
