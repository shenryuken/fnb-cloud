<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen antialiased bg-zinc-50 dark:bg-zinc-950">
        <div class="flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="flex w-full max-w-sm flex-col gap-6">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-3 font-medium" wire:navigate>
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-600 shadow-lg shadow-blue-500/20">
                        <x-app-logo-icon class="size-6 fill-current text-white" />
                    </span>
                    <span class="font-black text-lg text-zinc-800 dark:text-zinc-100 tracking-tight">
                        {{ config('app.name', 'FnB Cloud') }}
                    </span>
                </a>
                <flux:card class="p-8">
                    {{ $slot }}
                </flux:card>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
