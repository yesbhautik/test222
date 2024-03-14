<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

class CheckInstallation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $installedFilePath = storage_path('framework/installed');
            if (Schema::hasTable('users')) {
                return $next($request);
            }
            if (!File::exists($installedFilePath)) {
                return redirect('/install');
            }
            return $next($request);
        } catch (QueryException $e) {
            if (str_contains($e->getMessage(), "Access denied for user")) {
                return redirect('/install');
            }
            throw $e;
        }
    }
}