<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'FnB Cloud') }} - Modern POS for Restaurants</title>
        <meta name="description" content="All-in-one cloud-based POS system for restaurants, cafes, and food businesses. Manage orders, kitchen, inventory, and more.">

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            .gradient-text {
                background: linear-gradient(135deg, #ec4899 0%, #f43f5e 50%, #ef4444 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }
            .hero-gradient {
                background: radial-gradient(ellipse 80% 50% at 50% -20%, rgba(236, 72, 153, 0.15), transparent);
            }
            .card-hover {
                transition: all 0.3s ease;
            }
            .card-hover:hover {
                transform: translateY(-4px);
                box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.15);
            }
            .dark .card-hover:hover {
                box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.4);
            }
        </style>
    </head>
    <body class="bg-zinc-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 antialiased">
        <!-- Navigation -->
        <header class="fixed top-0 left-0 right-0 z-50 bg-zinc-50/80 dark:bg-zinc-950/80 backdrop-blur-lg border-b border-zinc-200 dark:border-zinc-800">
            <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo -->
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-pink-500 to-rose-500 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <span class="text-xl font-bold">FnB Cloud</span>
                    </div>

                    <!-- Nav Links -->
                    <div class="hidden md:flex items-center gap-8">
                        <a href="#features" class="text-sm font-medium text-zinc-600 dark:text-zinc-400 hover:text-pink-500 dark:hover:text-pink-400 transition-colors">Features</a>
                        <a href="#how-it-works" class="text-sm font-medium text-zinc-600 dark:text-zinc-400 hover:text-pink-500 dark:hover:text-pink-400 transition-colors">How It Works</a>
                        <a href="#pricing" class="text-sm font-medium text-zinc-600 dark:text-zinc-400 hover:text-pink-500 dark:hover:text-pink-400 transition-colors">Pricing</a>
                    </div>

                    <!-- Auth Buttons -->
                    @if (Route::has('login'))
                        <div class="flex items-center gap-3">
                            @auth
                                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-pink-500 hover:bg-pink-600 rounded-lg transition-colors">
                                    Dashboard
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                    </svg>
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="text-sm font-medium text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">
                                    Log in
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-pink-500 hover:bg-pink-600 rounded-lg transition-colors">
                                        Get Started
                                    </a>
                                @endif
                            @endauth
                        </div>
                    @endif
                </div>
            </nav>
        </header>

        <!-- Hero Section -->
        <section class="relative pt-32 pb-20 lg:pt-40 lg:pb-32 hero-gradient">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-4xl mx-auto">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-pink-100 dark:bg-pink-900/30 text-pink-600 dark:text-pink-400 text-sm font-medium mb-6">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Trusted by 500+ restaurants
                    </div>
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight mb-6">
                        Modern POS for <span class="gradient-text">Restaurants</span> & Cafes
                    </h1>
                    <p class="text-lg sm:text-xl text-zinc-600 dark:text-zinc-400 mb-10 max-w-2xl mx-auto">
                        All-in-one cloud-based point of sale system. Manage orders, kitchen display, customers, loyalty programs, and reports - all from one place.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <a href="{{ route('register') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-semibold text-white bg-pink-500 hover:bg-pink-600 rounded-xl transition-all shadow-lg shadow-pink-500/25 hover:shadow-pink-500/40">
                            Start Free Trial
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>
                        <a href="#features" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-semibold text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700 rounded-xl transition-all">
                            <svg class="w-5 h-5 text-pink-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                            </svg>
                            Watch Demo
                        </a>
                    </div>
                </div>

                <!-- Hero Image / Mockup -->
                <div class="mt-16 relative">
                    <div class="absolute inset-0 bg-gradient-to-t from-zinc-50 dark:from-zinc-950 to-transparent z-10 pointer-events-none h-32 bottom-0 top-auto"></div>
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden">
                        <div class="bg-zinc-100 dark:bg-zinc-800 px-4 py-3 flex items-center gap-2">
                            <div class="flex gap-1.5">
                                <div class="w-3 h-3 rounded-full bg-red-400"></div>
                                <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                                <div class="w-3 h-3 rounded-full bg-green-400"></div>
                            </div>
                            <div class="flex-1 text-center text-xs text-zinc-500 dark:text-zinc-400">pos.fnbcloud.com</div>
                        </div>
                        <div class="p-6 lg:p-8 bg-zinc-50 dark:bg-zinc-900/50">
                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                                <!-- Mini stat cards -->
                                <div class="bg-white dark:bg-zinc-800 rounded-xl p-4 border border-zinc-200 dark:border-zinc-700">
                                    <div class="text-2xl font-bold text-pink-500">RM 12,450</div>
                                    <div class="text-sm text-zinc-500">Today&apos;s Sales</div>
                                </div>
                                <div class="bg-white dark:bg-zinc-800 rounded-xl p-4 border border-zinc-200 dark:border-zinc-700">
                                    <div class="text-2xl font-bold text-emerald-500">89</div>
                                    <div class="text-sm text-zinc-500">Orders</div>
                                </div>
                                <div class="bg-white dark:bg-zinc-800 rounded-xl p-4 border border-zinc-200 dark:border-zinc-700">
                                    <div class="text-2xl font-bold text-blue-500">12</div>
                                    <div class="text-sm text-zinc-500">In Kitchen</div>
                                </div>
                                <div class="bg-white dark:bg-zinc-800 rounded-xl p-4 border border-zinc-200 dark:border-zinc-700">
                                    <div class="text-2xl font-bold text-amber-500">4.8</div>
                                    <div class="text-sm text-zinc-500">Rating</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-20 lg:py-32 bg-white dark:bg-zinc-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl sm:text-4xl font-bold mb-4">Everything you need to run your restaurant</h2>
                    <p class="text-lg text-zinc-600 dark:text-zinc-400 max-w-2xl mx-auto">Powerful features designed specifically for food and beverage businesses.</p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Feature 1: POS -->
                    <div class="card-hover bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-700/50">
                        <div class="w-12 h-12 rounded-xl bg-pink-100 dark:bg-pink-900/30 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Point of Sale</h3>
                        <p class="text-zinc-600 dark:text-zinc-400">Fast, intuitive POS interface. Process orders quickly with touch-friendly design, split bills, apply discounts, and accept multiple payment methods.</p>
                    </div>

                    <!-- Feature 2: KDS -->
                    <div class="card-hover bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-700/50">
                        <div class="w-12 h-12 rounded-xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Kitchen Display</h3>
                        <p class="text-zinc-600 dark:text-zinc-400">Real-time kitchen display system. Orders flow instantly to the kitchen with clear item details, modifiers, and status tracking.</p>
                    </div>

                    <!-- Feature 3: Menu Management -->
                    <div class="card-hover bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-700/50">
                        <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Menu Management</h3>
                        <p class="text-zinc-600 dark:text-zinc-400">Easy menu setup with categories, variants, add-ons, and modifiers. Import menus via CSV or build from scratch.</p>
                    </div>

                    <!-- Feature 4: Customers & Loyalty -->
                    <div class="card-hover bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-700/50">
                        <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Customer Loyalty</h3>
                        <p class="text-zinc-600 dark:text-zinc-400">Build customer relationships with loyalty points, vouchers, and rewards. Track purchase history and spending patterns.</p>
                    </div>

                    <!-- Feature 5: Reports -->
                    <div class="card-hover bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-700/50">
                        <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Sales Reports</h3>
                        <p class="text-zinc-600 dark:text-zinc-400">Comprehensive analytics and reporting. Track sales, popular items, peak hours, and staff performance with visual charts.</p>
                    </div>

                    <!-- Feature 6: Multi-tenant -->
                    <div class="card-hover bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-700/50">
                        <div class="w-12 h-12 rounded-xl bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Multi-Branch Ready</h3>
                        <p class="text-zinc-600 dark:text-zinc-400">Manage multiple outlets from one dashboard. Separate data for each branch while maintaining centralized control.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works -->
        <section id="how-it-works" class="py-20 lg:py-32 bg-zinc-50 dark:bg-zinc-950">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl sm:text-4xl font-bold mb-4">Get started in minutes</h2>
                    <p class="text-lg text-zinc-600 dark:text-zinc-400 max-w-2xl mx-auto">Simple setup process to get your restaurant running on FnB Cloud.</p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <div class="text-center">
                        <div class="w-16 h-16 rounded-2xl bg-pink-500 text-white flex items-center justify-center text-2xl font-bold mx-auto mb-4">1</div>
                        <h3 class="text-xl font-semibold mb-2">Create Account</h3>
                        <p class="text-zinc-600 dark:text-zinc-400">Sign up and set up your restaurant profile in under 5 minutes.</p>
                    </div>
                    <div class="text-center">
                        <div class="w-16 h-16 rounded-2xl bg-pink-500 text-white flex items-center justify-center text-2xl font-bold mx-auto mb-4">2</div>
                        <h3 class="text-xl font-semibold mb-2">Add Your Menu</h3>
                        <p class="text-zinc-600 dark:text-zinc-400">Import your menu via CSV or add items manually with our easy editor.</p>
                    </div>
                    <div class="text-center">
                        <div class="w-16 h-16 rounded-2xl bg-pink-500 text-white flex items-center justify-center text-2xl font-bold mx-auto mb-4">3</div>
                        <h3 class="text-xl font-semibold mb-2">Start Selling</h3>
                        <p class="text-zinc-600 dark:text-zinc-400">Open your POS and start taking orders immediately. It&apos;s that simple.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section id="pricing" class="py-20 lg:py-32 bg-white dark:bg-zinc-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl sm:text-4xl font-bold mb-4">Simple, transparent pricing</h2>
                    <p class="text-lg text-zinc-600 dark:text-zinc-400 max-w-2xl mx-auto">No hidden fees. No long-term contracts. Cancel anytime.</p>
                </div>

                <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                    <!-- Starter -->
                    <div class="card-hover bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl p-8 border border-zinc-200 dark:border-zinc-700/50">
                        <h3 class="text-lg font-semibold text-zinc-500 dark:text-zinc-400 mb-2">Starter</h3>
                        <div class="mb-4">
                            <span class="text-4xl font-bold">RM 99</span>
                            <span class="text-zinc-500">/month</span>
                        </div>
                        <p class="text-zinc-600 dark:text-zinc-400 mb-6">Perfect for small cafes and food stalls.</p>
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center gap-2 text-sm">
                                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                1 outlet
                            </li>
                            <li class="flex items-center gap-2 text-sm">
                                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                Up to 3 users
                            </li>
                            <li class="flex items-center gap-2 text-sm">
                                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                POS & KDS
                            </li>
                            <li class="flex items-center gap-2 text-sm">
                                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                Basic reports
                            </li>
                        </ul>
                        <a href="{{ route('register') }}" class="block w-full text-center px-6 py-3 text-sm font-semibold text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 hover:border-zinc-300 dark:hover:border-zinc-600 rounded-xl transition-all">
                            Get Started
                        </a>
                    </div>

                    <!-- Professional -->
                    <div class="card-hover bg-pink-500 rounded-2xl p-8 border border-pink-500 relative">
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 bg-white dark:bg-zinc-900 text-pink-500 text-xs font-semibold rounded-full">Most Popular</div>
                        <h3 class="text-lg font-semibold text-pink-100 mb-2">Professional</h3>
                        <div class="mb-4 text-white">
                            <span class="text-4xl font-bold">RM 199</span>
                            <span class="text-pink-200">/month</span>
                        </div>
                        <p class="text-pink-100 mb-6">For growing restaurants with more needs.</p>
                        <ul class="space-y-3 mb-8 text-white">
                            <li class="flex items-center gap-2 text-sm">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                Up to 3 outlets
                            </li>
                            <li class="flex items-center gap-2 text-sm">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                Unlimited users
                            </li>
                            <li class="flex items-center gap-2 text-sm">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                Loyalty & vouchers
                            </li>
                            <li class="flex items-center gap-2 text-sm">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                Advanced analytics
                            </li>
                            <li class="flex items-center gap-2 text-sm">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                Priority support
                            </li>
                        </ul>
                        <a href="{{ route('register') }}" class="block w-full text-center px-6 py-3 text-sm font-semibold text-pink-500 bg-white hover:bg-pink-50 rounded-xl transition-all">
                            Get Started
                        </a>
                    </div>

                    <!-- Enterprise -->
                    <div class="card-hover bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl p-8 border border-zinc-200 dark:border-zinc-700/50">
                        <h3 class="text-lg font-semibold text-zinc-500 dark:text-zinc-400 mb-2">Enterprise</h3>
                        <div class="mb-4">
                            <span class="text-4xl font-bold">Custom</span>
                        </div>
                        <p class="text-zinc-600 dark:text-zinc-400 mb-6">For restaurant chains and franchises.</p>
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center gap-2 text-sm">
                                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                Unlimited outlets
                            </li>
                            <li class="flex items-center gap-2 text-sm">
                                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                Custom integrations
                            </li>
                            <li class="flex items-center gap-2 text-sm">
                                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                Dedicated support
                            </li>
                            <li class="flex items-center gap-2 text-sm">
                                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                SLA guarantee
                            </li>
                        </ul>
                        <a href="mailto:sales@fnbcloud.com" class="block w-full text-center px-6 py-3 text-sm font-semibold text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 hover:border-zinc-300 dark:hover:border-zinc-600 rounded-xl transition-all">
                            Contact Sales
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 lg:py-32 bg-zinc-900 dark:bg-zinc-950">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">Ready to transform your restaurant?</h2>
                <p class="text-lg text-zinc-400 mb-8">Join hundreds of restaurants already using FnB Cloud to streamline their operations.</p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('register') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-semibold text-white bg-pink-500 hover:bg-pink-600 rounded-xl transition-all">
                        Start Free Trial
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                    <a href="mailto:hello@fnbcloud.com" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-semibold text-white border border-zinc-700 hover:border-zinc-600 rounded-xl transition-all">
                        Talk to Sales
                    </a>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="py-12 bg-zinc-950 border-t border-zinc-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-pink-500 to-rose-500 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <span class="text-lg font-bold text-white">FnB Cloud</span>
                    </div>
                    <p class="text-sm text-zinc-500">&copy; {{ date('Y') }} FnB Cloud. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </body>
</html>
