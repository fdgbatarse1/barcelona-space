<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\Log;
use Sentry\Breadcrumb;

trait LogsToSentry
{
    /**
     * Add a Sentry breadcrumb for contextual debugging.
     */
    protected function addBreadcrumb(string $category, string $message, array $data = []): void
    {
        if (!app()->bound('sentry')) {
            return;
        }

        $hub = app('sentry');

        $hub->addBreadcrumb(new Breadcrumb(
            Breadcrumb::LEVEL_INFO,
            Breadcrumb::TYPE_DEFAULT,
            $category,
            $message,
            $data
        ));
    }

    /**
     * Log an action to both Laravel logs and Sentry.
     */
    protected function logAction(string $level, string $message, array $context = []): void
    {
        Log::$level($message, $context);
    }
}

