<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Sentry\Breadcrumb;
use Sentry\Laravel\Facades\Sentry;
use Sentry\State\Scope;

class SentryContext
{
    /**
     * Attach user context and a request breadcrumb to Sentry for each HTTP call.
     */
    public function handle(Request $request, Closure $next)
    {
        if (app()->bound('sentry')) {
            $user = $request->user();
            $route = $request->route();

            Sentry::configureScope(function (Scope $scope) use ($user): void {
                if ($user) {
                    $scope->setUser([
                        'id' => $user->id,
                        'email' => $user->email,
                    ]);
                } else {
                    $scope->setUser(null);
                }
            });

            Sentry::addBreadcrumb(new Breadcrumb(
                Breadcrumb::LEVEL_INFO,
                Breadcrumb::TYPE_NAVIGATION,
                'request',
                sprintf('%s %s', $request->method(), $request->path()),
                [
                    'route' => $route?->getName() ?? $request->path(),
                    'method' => $request->method(),
                    'user_id' => $user?->id,
                ]
            ));
        }

        return $next($request);
    }
}

