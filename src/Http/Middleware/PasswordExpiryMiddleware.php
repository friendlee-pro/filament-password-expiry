<?php

namespace EightyNine\FilamentPasswordExpiry\Http\Middleware;

use Closure;
use EightyNine\FilamentPasswordExpiry\PasswordExpiry;
use Illuminate\Foundation\Console\RouteListCommand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class PasswordExpiryMiddleware
{
    public function handle(Request $request, Closure $next): Response | RedirectResponse
    {
        if (
            $this->passwordHasExpired() &&
            !$request->routeIs(PasswordExpiry::getPasswordExpiryRoute()) &&
            !$request->routeIs('*.auth.*')
        ) {
            return redirect(route(PasswordExpiry::getPasswordExpiryRoute()));
        }

        $response = $next($request);

        if ($response instanceof JsonResponse) {
            return response($response->getContent(), $response->getStatusCode(), $response->headers->all());
        }

        return $response;
    }

    protected function passwordHasExpired(): bool
    {
        if (
            blank(
                config('password-expiry.auth_class')::auth()
                    ->user()
                    ?->{config('password-expiry.column_name')}
            )
        ) {
            return true;
        }
        return now()
            ->isAfter(
                config('password-expiry.auth_class')::auth()
                    ->user()
                    ->{config('password-expiry.column_name')}
            );
    }
}
