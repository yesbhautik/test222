<?php

namespace App\Http\Middleware;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {

            if ($request->routeIs('dashboard.user.openai.chat.*'));
        }

        return $request->expectsJson() ? null : route('login');
    }

    protected function unauthenticated($request, array $guards)
    {

        $text = $request->routeIs('dashboard.user.openai.chat.*') ? 'Please log in to your account to start using Live Chat.' : 'Unauthenticated.';
        throw new AuthenticationException(
            $text, $guards, $this->redirectTo($request)
        );
    }
}
