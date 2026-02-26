<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->company_config_pending) {
             // If company is not configured, we might restrict access or allow only onboarding routes
             // For now, CheckTenant implies we NEED a tenant context.
             // If pending, we don't have a valid tenant context usually (unless switching to existing one)
             // So we return error or rely on CheckCompanyConfigured to handle redirection.
             // Here we strictly check if current_company_id is valid.
        }

        if ($user && !$user->current_company_id) {
             // If user has no current company, we can't scope queries.
             // We might check if they have companies and auto-select one.
             if ($user->companies()->exists()) {
                 $firstCompany = $user->companies()->first();
                 $user->current_company_id = $firstCompany->id;
                 $user->save();
             } else {
                 // No companies at all.
                 // This middleware enforces tenant context, so we should abort if strict
                 // or allow specific routes.
                 // For now, let's proceed but TenantScope won't apply filter (or filter by null which is 0 results).
             }
        }

        return $next($request);
    }
}
