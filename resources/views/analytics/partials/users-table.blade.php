<section class="panel">
    <header class="panel-header"><div><h2 class="panel-title">Admin and Member performance</h2><p class="panel-subtitle">URL and click totals follow the selected filters.</p></div><span class="count-chip">{{ number_format($users->total()) }} users</span></header>
    <div class="table-wrap"><table class="data-table"><thead><tr><th>User</th><th>Role</th>@if($isSuperAdminPage)<th>Company</th>@endif<th>URLs</th><th>Clicks</th></tr></thead><tbody>
    @forelse($users as $directoryUser)
        <tr><td><p class="person-name">{{ $directoryUser->name }}</p><p class="person-email">{{ $directoryUser->email }}</p></td><td><span class="table-role {{ $directoryUser->isMember() ? 'member' : '' }}">{{ ucfirst($directoryUser->role->value) }}</span></td>@if($isSuperAdminPage)<td>{{ $directoryUser->company?->name ?? 'No company' }}</td>@endif<td><span class="number">{{ number_format($directoryUser->urls_count) }}</span></td><td><span class="number">{{ number_format($directoryUser->clicks_count ?? 0) }}</span></td></tr>
    @empty
        <tr><td class="empty-state" colspan="{{ $isSuperAdminPage ? 5 : 4 }}">No Admin or Member matches these filters.</td></tr>
    @endforelse
    </tbody></table></div>
    @if($users->hasPages())<nav class="pagination"><span class="pagination-copy">Page {{ $users->currentPage() }} of {{ $users->lastPage() }}</span><div class="pagination-actions">@if($users->onFirstPage())<span class="page-button disabled">Previous</span>@else<a class="page-button" href="{{ $users->previousPageUrl() }}">Previous</a>@endif @if($users->hasMorePages())<a class="page-button" href="{{ $users->nextPageUrl() }}">Next</a>@else<span class="page-button disabled">Next</span>@endif</div></nav>@endif
</section>
