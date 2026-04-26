<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen antialiased bg-gradient-to-br from-zinc-900 via-zinc-900 to-zinc-950">
        <div class="flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="flex w-full max-w-sm flex-col gap-6">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-3 font-medium group" wire:navigate>
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-pink-500 to-rose-600 shadow-lg shadow-pink-500/30 group-hover:shadow-pink-500/50 transition-shadow duration-300">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </span>
                    <span class="font-black text-xl text-white tracking-tight">
                        FnB Cloud
                    </span>
                </a>
                <div class="bg-zinc-900/80 backdrop-blur-sm border border-zinc-800 rounded-2xl p-8 shadow-2xl">
                    {{ $slot }}
                </div>
                <p class="text-center text-xs text-zinc-500">
                    Cloud-based POS for modern restaurants
                </p>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
