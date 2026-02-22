<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\IntegrationSetting;
use Symfony\Component\HttpFoundation\Response;

class ApplyUserLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $settings = IntegrationSetting::getSettings('user_' . $user->id);
            
            if (isset($settings['locale']) && $settings['locale']) {
                app()->setLocale($settings['locale']);
                session(['locale' => $settings['locale']]);
            }
        }
        
        return $next($request);
    }
}
