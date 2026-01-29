<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS & Alpine.js via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <!-- TinyMCE Editor -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.7.1/tinymce.min.js"
        integrity="sha512-RnlQJaTEHoOCt5dUTV0Oi0vOBMI9PjCU7m+VHoJ4xmhuUNcwnB5Iox1es+skLril1C3gHTLbeRepHs1RpSCLoQ=="
        crossorigin="anonymous"></script>

    <!-- Rich Text Content Styling -->
    <style>
        .rich-text-content {
            line-height: 1.7;
        }

        .rich-text-content p {
            margin-bottom: 1em;
        }

        .rich-text-content h1 {
            font-size: 2em;
            font-weight: 700;
            margin-top: 0.5em;
            margin-bottom: 0.5em;
            line-height: 1.2;
        }

        .rich-text-content h2 {
            font-size: 1.5em;
            font-weight: 700;
            margin-top: 0.5em;
            margin-bottom: 0.5em;
            line-height: 1.3;
        }

        .rich-text-content h3 {
            font-size: 1.25em;
            font-weight: 600;
            margin-top: 0.5em;
            margin-bottom: 0.5em;
            line-height: 1.4;
        }

        .rich-text-content ul,
        .rich-text-content ol {
            margin-left: 1.5em;
            margin-bottom: 1em;
        }

        .rich-text-content ul {
            list-style-type: disc;
        }

        .rich-text-content ol {
            list-style-type: decimal;
        }

        .rich-text-content li {
            margin-bottom: 0.5em;
        }

        .rich-text-content strong {
            font-weight: 700;
        }

        .rich-text-content em {
            font-style: italic;
        }

        .rich-text-content a {
            color: #2563eb;
            text-decoration: underline;
        }

        .rich-text-content a:hover {
            color: #1d4ed8;
        }

        .rich-text-content blockquote {
            border-left: 4px solid #d1d5db;
            padding-left: 1em;
            margin-left: 0;
            margin-bottom: 1em;
            color: #6b7280;
            font-style: italic;
        }

        .rich-text-content code {
            background-color: #f3f4f6;
            padding: 0.2em 0.4em;
            border-radius: 0.25em;
            font-family: monospace;
            font-size: 0.875em;
        }

        .rich-text-content pre {
            background-color: #f3f4f6;
            padding: 1em;
            border-radius: 0.5em;
            overflow-x: auto;
            margin-bottom: 1em;
        }

        .rich-text-content pre code {
            background-color: transparent;
            padding: 0;
        }

        .rich-text-content table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1em;
        }

        .rich-text-content table td,
        .rich-text-content table th {
            border: 1px solid #d1d5db;
            padding: 0.5em;
        }

        .rich-text-content table th {
            background-color: #f3f4f6;
            font-weight: 600;
        }

        .rich-text-content img {
            max-width: 100%;
            height: auto;
            margin-bottom: 1em;
        }
    </style>

    @livewireStyles
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        @include('layouts.app_navigation')

        <!-- Page Heading -->
        @isset($header)
            <div class="max-w-7xl mx-auto py-2 sm:py-4 lg:py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        @endisset

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>

    <script src="https://browser.sentry-cdn.com/8.33.1/bundle.tracing.min.js" crossorigin="anonymous"></script>
    <script>
        if (window.Sentry) {
            const dsn = @json(config('sentry.dsn'));

            if (dsn) {
                const user = @json(auth()->check() ? [
                    'id' => auth()->id(),
                    'email' => auth()->user()->email,
                ] : null);

                window.Sentry.init({
                    dsn,
                    environment: @json(config('app.env')),
                    release: @json(config('sentry.release')),
                    integrations: [window.Sentry.browserTracingIntegration()],
                    tracesSampleRate: @json(config('sentry.traces_sample_rate') ?? 0),
                    sendDefaultPii: @json((bool) config('sentry.send_default_pii')),
                });

                if (user) {
                    window.Sentry.setUser(user);
                }
            }
        }
    </script>

    @stack('scripts')

    @livewireScripts
</body>

</html>