<?php

namespace App\Http\Controllers;

use App\Services\ShortUrlCache;
use Illuminate\Http\RedirectResponse;

class ShortUrlRedirectController extends Controller
{
    public function __invoke(string $code, ShortUrlCache $cache): RedirectResponse
    {
        $shortUrl = $cache->resolve($code);

        abort_unless($shortUrl, 404);

        $cache->recordHit($shortUrl['id']);

        return redirect()->away($shortUrl['original_url']);
    }
}
