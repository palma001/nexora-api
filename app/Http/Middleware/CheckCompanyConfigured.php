<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckCompanyConfigured
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
            // Return specific code to frontend to trigger onboarding redirect
             return response()->json([
                 'message' => 'Company configuration required.',
                 'requires_onboarding' => true
             ], 403);
        }

        return $next($request);
    }
}
