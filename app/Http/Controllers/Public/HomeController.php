<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\TenantService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        protected TenantService $tenantService
    ) {}

    /**
     * Display the studio's landing page.
     */
    public function index(): View
    {
        $studio = $this->tenantService->current();

        // Get featured artists (active, ordered by sort_order)
        $featuredArtists = $studio->artists()
            ->active()
            ->with('portfolioImages')
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        return view('public.home', [
            'studio' => $studio,
            'featuredArtists' => $featuredArtists,
        ]);
    }
}
