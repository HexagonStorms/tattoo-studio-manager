<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Services\TenantService;
use Illuminate\View\View;

class ArtistController extends Controller
{
    public function __construct(
        protected TenantService $tenantService
    ) {}

    /**
     * Display a listing of all active artists.
     */
    public function index(): View
    {
        $studio = $this->tenantService->current();

        $artists = $studio->artists()
            ->active()
            ->with(['featuredImages' => fn($q) => $q->limit(1)])
            ->orderBy('sort_order')
            ->get();

        return view('public.artists.index', [
            'studio' => $studio,
            'artists' => $artists,
        ]);
    }

    /**
     * Display the specified artist's profile and portfolio.
     */
    public function show(string $slug): View
    {
        $studio = $this->tenantService->current();

        $artist = $studio->artists()
            ->where('slug', $slug)
            ->active()
            ->with('portfolioImages')
            ->firstOrFail();

        // Get other artists for "See also" section
        $otherArtists = $studio->artists()
            ->active()
            ->where('id', '!=', $artist->id)
            ->with(['featuredImages' => fn($q) => $q->limit(1)])
            ->orderBy('sort_order')
            ->limit(3)
            ->get();

        return view('public.artists.show', [
            'studio' => $studio,
            'artist' => $artist,
            'otherArtists' => $otherArtists,
        ]);
    }
}
