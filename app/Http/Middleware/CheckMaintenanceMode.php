<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get maintenance mode setting
        $maintenanceMode = app_setting('maintenance_mode', false);
        
        // If maintenance mode is enabled
        if ($maintenanceMode) {
            // Allow administrators to access
            if ($request->user() && $request->user()->role === 'Administrator') {
                return $next($request);
            }
            
            // Show maintenance page for other users
            return response()->view('errors.maintenance', [], 503);
        }
        
        return $next($request);
    }
}
