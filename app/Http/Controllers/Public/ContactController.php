<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\TenantService;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function __construct(
        protected TenantService $tenantService
    ) {}

    /**
     * Display the studio's contact information.
     */
    public function index(): View
    {
        $studio = $this->tenantService->current();

        return view('public.contact', [
            'studio' => $studio,
        ]);
    }
}
