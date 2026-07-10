<?php

namespace App\Http\Controllers;

use App\Models\ShortUrl;
use App\Services\ShortUrlCache;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShortUrlController extends Controller
{
    public function index(Request $request, ShortUrlCache $cache): View
    {
        $user = $request->user('api');
        $shortUrls = ShortUrl::query()
            ->when(
                $user->isSuperAdmin(),
                fn ($query) => $query->with('user.company'),
                fn ($query) => $query->where('user_id', $user->id),
            )
            ->latest()
            ->get();

        $shortUrls->each(function (ShortUrl $shortUrl) use ($cache) {
            $shortUrl->setAttribute('display_hits', $cache->hits($shortUrl));
        });

        return view('short-urls.index', [
            'user' => $user,
            'shortUrls' => $shortUrls,
            'urlStats' => [
                'links' => $shortUrls->count(),
                'hits' => $shortUrls->sum('display_hits'),
            ],
        ]);
    }

    public function store(Request $request, ShortUrlCache $cache): RedirectResponse
    {
        $data = $request->validate([
            'original_url' => ['required', 'string', 'url:http,https', 'max:2048'],
        ]);

        $shortUrl = ShortUrl::create([
            'user_id' => $request->user('api')->id,
            'code' => $this->uniqueCode(),
            'original_url' => $data['original_url'],
        ]);

        $cache->put($shortUrl);

        return redirect()->route('short-urls.index')
            ->with('status', 'Short URL created: '.$shortUrl->shortUrl());
    }

    private function uniqueCode(): string
    {
        $length = max(5, (int) config('shortener.code_length', 7));

        do {
            $code = Str::random($length);
        } while (ShortUrl::where('code', $code)->exists());

        return $code;
    }
}
