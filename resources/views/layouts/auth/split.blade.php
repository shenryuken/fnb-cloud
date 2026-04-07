<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen antialiased bg-zinc-50 dark:bg-zinc-950">
        <div class="relative grid h-dvh flex-col items-center justify-center lg:max-w-none lg:grid-cols-2 lg:px-0">
            {{-- Branded Left Panel --}}
            <div class="relative hidden h-full flex-col bg-zinc-900 p-10 text-white lg:flex overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-zinc-900 via-zinc-900 to-blue-950"></div>
                <div class="absolute top-0 right-0 w-96 h-96 bg-blue-600/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 bg-blue-600/5 rounded-full blur-2xl translate-y-1/2 -translate-x-1/2"></div>

                <a href="{{ route('home') }}" class="relative z-20 flex items-center gap-3 text-lg font-medium" wire:navigate>
                    <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-blue-600 shadow-lg shadow-blue-500/30">
                        <x-app-logo-icon class="h-5 w-5 fill-current text-white" />
                    </span>
                    <span class="font-black text-white">{{ config('app.name', 'FnB Cloud') }}</span>
                </a>

                <div class="relative z-20 mt-auto space-y-6">
                    <div class="flex gap-1">
                        @foreach(range(1,5) as $i)
                            <flux:icon.star class="w-5 h-5 text-amber-400 fill-amber-400" />
                        @endforeach
                    </div>
                    @php
                        [$message, $author] = str(Illuminate\Foundation\Inspiring::quotes()->random())->explode('-');
                    @endphp
                    <blockquote class="space-y-3">
                        <flux:heading size="xl" class="text-white leading-snug">&ldquo;{{ trim($message) }}&rdquo;</flux:heading>
                        <footer>
                            <flux:text class="text-zinc-400 font-semibold">— {{ trim($author) }}</flux:text>
                        </footer>
                    </blockquote>
                </div>
            </div>

            {{-- Right: Form Area --}}
            <div class="flex flex-col items-center justify-center w-full px-8 md:px-16 py-12">
                <div class="w-full max-w-sm flex flex-col gap-6">
                    <a href="{{ route('home') }}" class="flex flex-col items-center gap-3 lg:hidden" wire:navigate>
                        <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-600 shadow-lg">
                            <x-app-logo-icon class="size-6 fill-current text-white" />
                        </span>
                        <span class="font-black text-lg text-zinc-800 dark:text-zinc-100">{{ config('app.name', 'FnB Cloud') }}</span>
                    </a>
                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
