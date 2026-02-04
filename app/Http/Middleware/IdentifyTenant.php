<?php

namespace App\Http\Middleware;

use App\Models\Studio;
use App\Services\TenantService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function __construct(
        protected TenantService $tenantService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $studio = $this->resolveStudio($request);

        if (!$studio) {
            abort(404, 'Studio not found');
        }

        // Store in the singleton service
        $this->tenantService->set($studio);

        // Store in request attributes for easy access
        $request->attributes->set('studio', $studio);

        return $next($request);
    }

    /**
     * Resolve the studio from the request.
     */
    protected function resolveStudio(Request $request): ?Studio
    {
        // 1. Check for custom domain first (exact match)
        $host = $request->getHost();
        $studio = Studio::where('custom_domain', $host)->first();

        if ($studio) {
            return $studio;
        }

        // 2. Try subdomain extraction
        $studio = $this->resolveFromSubdomain($host);

        if ($studio) {
            return $studio;
        }

        // 3. Local development fallback: query parameter
        if ($this->isLocalEnvironment() && $request->has('studio')) {
            $slug = $request->query('studio');
            return Studio::where('slug', $slug)->first();
        }

        return null;
    }

    /**
     * Resolve studio from subdomain.
     */
    protected function resolveFromSubdomain(string $host): ?Studio
    {
        // Handle localhost variants (e.g., demo.localhost, demo.localhost:8000)
        if (str_contains($host, 'localhost')) {
            $parts = explode('.', $host);
            if (count($parts) >= 2) {
                $slug = $parts[0];
                return Studio::where('slug', $slug)->first();
            }
            return null;
        }

        // Handle standard domains (e.g., demo.tattoostudio.com)
        $parts = explode('.', $host);

        // Need at least 3 parts for subdomain.domain.tld
        if (count($parts) >= 3) {
            $slug = $parts[0];
            return Studio::where('slug', $slug)->first();
        }

        return null;
    }

    /**
     * Check if running in local development environment.
     */
    protected function isLocalEnvironment(): bool
    {
        return app()->environment('local', 'development', 'testing');
    }
}
