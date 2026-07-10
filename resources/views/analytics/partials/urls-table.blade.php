<section class="panel">
    <header class="panel-header"><div><h2 class="panel-title">Short URLs and clicks</h2><p class="panel-subtitle">Every link owned by the filtered Admins and Members.</p></div><span class="count-chip">{{ number_format($shortUrls->total()) }} URLs</span></header>
    <div class="table-wrap"><table class="data-table"><thead><tr><th>Short URL</th><th>Destination</th><th>Owner</th><th>Role</th>@if($isSuperAdminPage)<th>Company</th>@endif<th>Created</th><th>Clicks</th></tr></thead><tbody>
    @forelse($shortUrls as $shortUrl)
        <tr><td class="url-cell"><a class="short-link" href="{{ $shortUrl->shortUrl() }}" target="_blank" rel="noopener">{{ $shortUrl->shortUrl() }}</a></td><td class="url-cell"><a class="long-link" href="{{ $shortUrl->original_url }}" target="_blank" rel="noopener">{{ $shortUrl->original_url }}</a></td><td><p class="person-name">{{ $shortUrl->user->name }}</p><p class="person-email">{{ $shortUrl->user->email }}</p></td><td><span class="table-role {{ $shortUrl->user->isMember() ? 'member' : '' }}">{{ ucfirst($shortUrl->user->role->value) }}</span></td>@if($isSuperAdminPage)<td>{{ $shortUrl->user->company?->name ?? 'No company' }}</td>@endif<td>{{ $shortUrl->created_at->format('d M Y') }}</td><td><span class="number">{{ number_format($shortUrl->hits) }}</span></td></tr>
    @empty
        <tr><td class="empty-state" colspan="{{ $isSuperAdminPage ? 7 : 6 }}">No short URLs match these filters.</td></tr>
    @endforelse
    </tbody></table></div>
    @if($shortUrls->hasPages())<nav class="pagination"><span class="pagination-copy">Page {{ $shortUrls->currentPage() }} of {{ $shortUrls->lastPage() }}</span><div class="pagination-actions">@if($shortUrls->onFirstPage())<span class="page-button disabled">Previous</span>@else<a class="page-button" href="{{ $shortUrls->previousPageUrl() }}">Previous</a>@endif @if($shortUrls->hasMorePages())<a class="page-button" href="{{ $shortUrls->nextPageUrl() }}">Next</a>@else<span class="page-button disabled">Next</span>@endif</div></nav>@endif
</section>
