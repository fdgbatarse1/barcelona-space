<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestSentryLogs extends Command
{
    protected $signature = 'sentry:test-logs';

    protected $description = 'Send test log messages to Sentry to verify configuration';

    public function handle(): int
    {
        $this->info('Sending test logs to Sentry...');

        // Log to all channels in the stack (including Sentry)
        Log::info('Sentry test: info message', ['test' => true, 'timestamp' => now()->toIso8601String()]);
        Log::warning('Sentry test: warning message', ['test' => true]);
        Log::error('Sentry test: error message', ['test' => true]);

        // Log directly to the Sentry channel
        Log::channel('sentry_logs')->info('Sentry test: direct channel message');

        $this->info('Test logs sent! Check your Sentry dashboard for:');
        $this->line('  - 1 info message (stack)');
        $this->line('  - 1 warning message (stack)');
        $this->line('  - 1 error message (stack)');
        $this->line('  - 1 info message (direct sentry_logs channel)');

        return Command::SUCCESS;
    }
}

