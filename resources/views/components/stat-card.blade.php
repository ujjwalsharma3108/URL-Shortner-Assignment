@props(['label', 'value', 'tone' => '', 'note' => null])

<article class="stat-card {{ $tone }}">
    <p class="stat-label">{{ $label }}</p>
    <p class="stat-value">{{ is_numeric($value) ? number_format($value) : $value }}</p>
    @if ($note)<p class="stat-note">{{ $note }}</p>@endif
</article>
