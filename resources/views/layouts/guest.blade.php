<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />

        <title>{{ filled($title ?? null) ? $title.' - '.config('app.name', 'FnB Cloud') : config('app.name', 'FnB Cloud') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @fluxAppearance
    </head>
    <body class="min-h-screen bg-neutral-100 font-sans antialiased text-neutral-900">
        {{ $slot }}

        {{-- Lightweight toast handler for guest notify events --}}
        <div
            x-data="{ toasts: [] }"
            x-on:notify.window="
                const id = Date.now();
                toasts.push({ id, type: $event.detail.type || 'info', message: $event.detail.message });
                setTimeout(() => { toasts = toasts.filter(t => t.id !== id); }, 3500);
            "
            class="fixed inset-x-0 top-4 z-[100] flex flex-col items-center gap-2 px-4 pointer-events-none"
        >
            <template x-for="toast in toasts" :key="toast.id">
                <div
                    x-transition
                    class="w-full max-w-sm rounded-xl px-4 py-3 text-sm font-semibold shadow-lg pointer-events-auto"
                    :class="toast.type === 'error' ? 'bg-red-600 text-white' : (toast.type === 'success' ? 'bg-emerald-600 text-white' : 'bg-neutral-900 text-white')"
                    x-text="toast.message"
                ></div>
            </template>
        </div>

        @fluxScripts
    </body>
</html>
