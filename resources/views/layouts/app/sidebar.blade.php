<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky class="hidden xl:flex border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                @if(auth()->user()->tenant_id === null)
                    <flux:sidebar.group :heading="__('System Admin')" class="grid">
                        <flux:sidebar.item icon="home" :href="route('landlord.dashboard')" :current="request()->routeIs('landlord.dashboard')" wire:navigate>
                            {{ __('Global Stats') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="users" :href="route('landlord.tenants.index')" :current="request()->routeIs('landlord.tenants.index')" wire:navigate>
                            {{ __('Tenants') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @else
                    <flux:sidebar.group :heading="__('Platform')" class="grid">
                        <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                            {{ __('Dashboard') }}
                        </flux:sidebar.item>
                        @if(auth()->user()->hasPermission('pos.access'))
                            <flux:sidebar.item icon="shopping-cart" :href="route('pos.index')" :current="request()->routeIs('pos.index')" wire:navigate>
                                {{ __('POS') }}
                            </flux:sidebar.item>
                        @endif
                        @if(auth()->user()->hasPermission('orders.manage'))
                            <flux:sidebar.item icon="clipboard-list" :href="route('manage.orders.index')" :current="request()->routeIs('manage.orders.index', 'manage.orders.unshifted')" wire:navigate>
                                {{ __('Orders') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="clock" :href="route('manage.orders.unshifted')" :current="request()->routeIs('manage.orders.unshifted')" wire:navigate class="pl-8 text-sm">
                                {{ __("Unshifted") }}
                            </flux:sidebar.item>
                        @endif
                        @if(auth()->user()->hasPermission('kds.access'))
                            <flux:sidebar.item icon="fire" :href="route('kds.index')" :current="request()->routeIs('kds.index')" wire:navigate>
                                {{ __('Kitchen (KDS)') }}
                            </flux:sidebar.item>
                        @endif
                        @if(auth()->user()->hasPermission('pos.access'))
                            <flux:sidebar.item icon="banknotes" :href="route('manage.shifts.index')" :current="request()->routeIs('manage.shifts.index')" wire:navigate>
                                {{ __('Shifts') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="squares-2x2" :href="route('manage.tables.index')" :current="request()->routeIs('manage.tables.index')" wire:navigate>
                                {{ __('Tables') }}
                            </flux:sidebar.item>
                        @endif
                    </flux:sidebar.group>

                    @if(auth()->user()->hasPermission('reports.view'))
                        <flux:sidebar.group :heading="__('Reports')" class="grid">
                            <flux:sidebar.item icon="chart-bar" :href="route('reports.sales')" :current="request()->routeIs('reports.sales')" wire:navigate>
                                {{ __('Sales Report') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="users" :href="route('reports.cashier')" :current="request()->routeIs('reports.cashier')" wire:navigate>
                                {{ __('Cashier Report') }}
                            </flux:sidebar.item>
                        </flux:sidebar.group>
                    @endif

                    @if(auth()->user()->hasPermission('menu.manage'))
                        <flux:sidebar.group :heading="__('Menu Management')" class="grid">
                            <flux:sidebar.item icon="layers" :href="route('manage.categories.index')" :current="request()->routeIs('manage.categories.index')" wire:navigate>
                                {{ __('Categories') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="package" :href="route('manage.products.index')" :current="request()->routeIs('manage.products.index')" wire:navigate>
                                {{ __('Products') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="plus-circle" :href="route('manage.addons.index')" :current="request()->routeIs('manage.addons.index')" wire:navigate>
                                {{ __('Add-ons') }}
                            </flux:sidebar.item>
                        </flux:sidebar.group>
                    @endif

                    @if(auth()->user()->hasPermission('customers.manage') || auth()->user()->hasPermission('vouchers.manage') || auth()->user()->hasPermission('settings.manage'))
                        <flux:sidebar.group :heading="__('Loyalty Program')" class="grid">
                            @if(auth()->user()->hasPermission('settings.manage'))
                                <flux:sidebar.item icon="sparkles" :href="route('manage.settings.loyalty')" :current="request()->routeIs('manage.settings.loyalty')" wire:navigate>
                                    {{ __('Loyalty Points') }}
                                </flux:sidebar.item>
                            @endif
                            @if(auth()->user()->hasPermission('customers.manage'))
                                <flux:sidebar.item icon="users" :href="route('manage.customers.index')" :current="request()->routeIs('manage.customers.index')" wire:navigate>
                                    {{ __('Customers') }}
                                </flux:sidebar.item>
                            @endif
                            @if(auth()->user()->hasPermission('vouchers.manage'))
                                <flux:sidebar.item icon="tag" :href="route('manage.vouchers.index')" :current="request()->routeIs('manage.vouchers.index')" wire:navigate>
                                    {{ __('Vouchers') }}
                                </flux:sidebar.item>
                            @endif
                        </flux:sidebar.group>
                    @endif
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.item icon="book-open" :href="route('guide.index')" :current="request()->routeIs('guide.index')" wire:navigate class="text-zinc-400">
                {{ __('User Guide') }}
            </flux:sidebar.item>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <flux:sidebar collapsible="mobile" sticky class="xl:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                @if(auth()->user()->tenant_id === null)
                    <flux:sidebar.group :heading="__('System Admin')" class="grid">
                        <flux:sidebar.item icon="home" :href="route('landlord.dashboard')" :current="request()->routeIs('landlord.dashboard')" wire:navigate>
                            {{ __('Global Stats') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="users" :href="route('landlord.tenants.index')" :current="request()->routeIs('landlord.tenants.index')" wire:navigate>
                            {{ __('Tenants') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @else
                    <flux:sidebar.group :heading="__('Platform')" class="grid">
                        <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                            {{ __('Dashboard') }}
                        </flux:sidebar.item>
                        @if(auth()->user()->hasPermission('pos.access'))
                            <flux:sidebar.item icon="shopping-cart" :href="route('pos.index')" :current="request()->routeIs('pos.index')" wire:navigate>
                                {{ __('POS') }}
                            </flux:sidebar.item>
                        @endif
                        @if(auth()->user()->hasPermission('orders.manage'))
                            <flux:sidebar.item icon="clipboard-list" :href="route('manage.orders.index')" :current="request()->routeIs('manage.orders.index', 'manage.orders.unshifted')" wire:navigate>
                                {{ __('Orders') }}
                            </flux:sidebar.item>
                        @endif
                        @if(auth()->user()->hasPermission('kds.access'))
                            <flux:sidebar.item icon="fire" :href="route('kds.index')" :current="request()->routeIs('kds.index')" wire:navigate>
                                {{ __('Kitchen (KDS)') }}
                            </flux:sidebar.item>
                        @endif
                        @if(auth()->user()->hasPermission('pos.access'))
                            <flux:sidebar.item icon="banknotes" :href="route('manage.shifts.index')" :current="request()->routeIs('manage.shifts.index')" wire:navigate>
                                {{ __('Shifts') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="squares-2x2" :href="route('manage.tables.index')" :current="request()->routeIs('manage.tables.index')" wire:navigate>
                                {{ __('Tables') }}
                            </flux:sidebar.item>
                        @endif
                    </flux:sidebar.group>

                    @if(auth()->user()->hasPermission('reports.view'))
                        <flux:sidebar.group :heading="__('Reports')" class="grid">
                            <flux:sidebar.item icon="chart-bar" :href="route('reports.sales')" :current="request()->routeIs('reports.sales')" wire:navigate>
                                {{ __('Sales Report') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="users" :href="route('reports.cashier')" :current="request()->routeIs('reports.cashier')" wire:navigate>
                                {{ __('Cashier Report') }}
                            </flux:sidebar.item>
                        </flux:sidebar.group>
                    @endif

                    @if(auth()->user()->hasPermission('menu.manage'))
                        <flux:sidebar.group :heading="__('Menu Management')" class="grid">
                            <flux:sidebar.item icon="layers" :href="route('manage.categories.index')" :current="request()->routeIs('manage.categories.index')" wire:navigate>
                                {{ __('Categories') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="package" :href="route('manage.products.index')" :current="request()->routeIs('manage.products.index')" wire:navigate>
                                {{ __('Products') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="plus-circle" :href="route('manage.addons.index')" :current="request()->routeIs('manage.addons.index')" wire:navigate>
                                {{ __('Add-ons') }}
                            </flux:sidebar.item>
                        </flux:sidebar.group>
                    @endif

                    @if(auth()->user()->hasPermission('customers.manage') || auth()->user()->hasPermission('vouchers.manage') || auth()->user()->hasPermission('settings.manage'))
                        <flux:sidebar.group :heading="__('Loyalty Program')" class="grid">
                            @if(auth()->user()->hasPermission('settings.manage'))
                                <flux:sidebar.item icon="sparkles" :href="route('manage.settings.loyalty')" :current="request()->routeIs('manage.settings.loyalty')" wire:navigate>
                                    {{ __('Loyalty Points') }}
                                </flux:sidebar.item>
                            @endif
                            @if(auth()->user()->hasPermission('customers.manage'))
                                <flux:sidebar.item icon="users" :href="route('manage.customers.index')" :current="request()->routeIs('manage.customers.index')" wire:navigate>
                                    {{ __('Customers') }}
                                </flux:sidebar.item>
                            @endif
                            @if(auth()->user()->hasPermission('vouchers.manage'))
                                <flux:sidebar.item icon="tag" :href="route('manage.vouchers.index')" :current="request()->routeIs('manage.vouchers.index')" wire:navigate>
                                    {{ __('Vouchers') }}
                                </flux:sidebar.item>
                            @endif
                        </flux:sidebar.group>
                    @endif
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.item icon="book-open" :href="route('guide.index')" :current="request()->routeIs('guide.index')" wire:navigate class="text-zinc-400">
                {{ __('User Guide') }}
            </flux:sidebar.item>
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="xl:hidden">
            <flux:sidebar.toggle icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        <div id="toast-container" class="fixed top-5 right-5 z-[9999] flex flex-col gap-3 pointer-events-none"></div>
        <script>
            (() => {
                const container = document.getElementById('toast-container');
                if (!container) return;

                const createToast = ({ message, type }) => {
                    const toast = document.createElement('div');
                    toast.className = [
                        'pointer-events-auto',
                        'min-w-[260px]',
                        'max-w-[360px]',
                        'rounded-2xl',
                        'border',
                        'shadow-2xl',
                        'backdrop-blur-md',
                        'px-4',
                        'py-3',
                        'flex',
                        'items-start',
                        'gap-3',
                        'animate-in',
                        'slide-in-from-top-2',
                        'fade-in',
                        'duration-200',
                        'bg-white/90',
                        'dark:bg-neutral-900/90',
                        type === 'error'
                            ? 'border-red-200 dark:border-red-900/40'
                            : type === 'warning'
                                ? 'border-amber-200 dark:border-amber-900/40'
                                : 'border-emerald-200 dark:border-emerald-900/40',
                    ].join(' ');

                    const dot = document.createElement('div');
                    dot.className = [
                        'mt-1',
                        'h-2.5',
                        'w-2.5',
                        'rounded-full',
                        type === 'error'
                            ? 'bg-red-500'
                            : type === 'warning'
                                ? 'bg-amber-500'
                                : 'bg-emerald-500',
                    ].join(' ');

                    const body = document.createElement('div');
                    body.className = 'flex-1';

                    const text = document.createElement('div');
                    text.className = 'text-sm font-black text-neutral-800 dark:text-neutral-100 leading-tight';
                    text.textContent = message;

                    const sub = document.createElement('div');
                    sub.className = 'text-[10px] font-black text-neutral-400 uppercase tracking-widest mt-0.5';
                    sub.textContent = type === 'error' ? 'Error' : type === 'warning' ? 'Notice' : 'Saved';

                    body.appendChild(text);
                    body.appendChild(sub);

                    toast.appendChild(dot);
                    toast.appendChild(body);

                    const dismiss = document.createElement('button');
                    dismiss.type = 'button';
                    dismiss.className = 'ml-2 text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200 transition-colors';
                    dismiss.innerHTML = '&#10005;';
                    dismiss.addEventListener('click', () => toast.remove());
                    toast.appendChild(dismiss);

                    container.appendChild(toast);

                    window.setTimeout(() => {
                        toast.classList.remove('fade-in');
                        toast.classList.add('animate-out', 'fade-out', 'duration-200');
                        window.setTimeout(() => toast.remove(), 200);
                    }, 3000);
                };

                const parseNotifyEvent = (e) => {
                    const detail = e?.detail;
                    if (typeof detail === 'string') return { message: detail, type: 'success' };
                    if (detail && typeof detail === 'object') {
                        const message = detail.message ?? detail[0] ?? 'Saved';
                        const type = detail.type ?? 'success';
                        return { message, type };
                    }
                    return { message: 'Saved', type: 'success' };
                };

                window.addEventListener('notify', (e) => {
                    const { message, type } = parseNotifyEvent(e);
                    createToast({ message, type });
                });
            })();
        </script>

        <script>
            (() => {
                if (window.__fnbAudioInit) return;
                window.__fnbAudioInit = true;

                const state = {
                    ctx: null,
                    master: null,
                };

                const ensure = () => {
                    if (state.ctx) return state.ctx;
                    const Ctx = window.AudioContext || window.webkitAudioContext;
                    if (!Ctx) return null;
                    state.ctx = new Ctx();
                    state.master = state.ctx.createGain();
                    state.master.gain.value = 0.2;
                    state.master.connect(state.ctx.destination);
                    return state.ctx;
                };

                const resume = async () => {
                    const ctx = ensure();
                    if (!ctx) return false;
                    try {
                        if (ctx.state === 'suspended') await ctx.resume();
                        return ctx.state === 'running';
                    } catch (_) {
                        return false;
                    }
                };

                const toneAt = (startAt, frequency, durationMs, volume) => {
                    const ctx = ensure();
                    if (!ctx || !state.master || ctx.state !== 'running') return;

                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();

                    osc.type = 'square';
                    osc.frequency.value = frequency;

                    const dur = Math.max(0.02, durationMs / 1000);
                    gain.gain.setValueAtTime(0.0001, startAt);
                    gain.gain.exponentialRampToValueAtTime(Math.max(0.0001, 0.35 * (volume ?? 1)), startAt + 0.01);
                    gain.gain.exponentialRampToValueAtTime(0.0001, startAt + dur);

                    osc.connect(gain);
                    gain.connect(state.master);

                    osc.start(startAt);
                    osc.stop(startAt + dur + 0.02);
                };

                const play = async (name) => {
                    const ok = await resume();
                    if (!ok) return;

                    const ctx = ensure();
                    if (!ctx) return;

                    const now = ctx.currentTime + 0.01;
                    const steps =
                        name === 'tap'
                            ? [{ f: 880, d: 40, v: 0.7, gap: 0 }]
                            : name === 'success'
                                ? [{ f: 523.25, d: 80, v: 1, gap: 20 }, { f: 659.25, d: 120, v: 1, gap: 0 }]
                                : name === 'warning'
                                    ? [{ f: 440, d: 110, v: 1, gap: 40 }, { f: 330, d: 140, v: 1, gap: 0 }]
                                    : name === 'error'
                                        ? [{ f: 220, d: 140, v: 1, gap: 40 }, { f: 196, d: 180, v: 1, gap: 0 }]
                                        : name === 'order'
                                            ? [{ f: 880, d: 90, v: 1, gap: 40 }, { f: 660, d: 90, v: 1, gap: 40 }, { f: 880, d: 140, v: 1, gap: 0 }]
                                            : [{ f: 660, d: 50, v: 0.6, gap: 0 }];

                    let t = now;
                    for (const s of steps) {
                        toneAt(t, s.f, s.d, s.v);
                        t += (s.d / 1000) + ((s.gap ?? 0) / 1000);
                    }
                };

                const unlockHandler = () => {
                    resume();
                    window.removeEventListener('pointerdown', unlockHandler, true);
                    window.removeEventListener('keydown', unlockHandler, true);
                    window.removeEventListener('touchstart', unlockHandler, true);
                };

                window.addEventListener('pointerdown', unlockHandler, true);
                window.addEventListener('keydown', unlockHandler, true);
                window.addEventListener('touchstart', unlockHandler, true);

                window.addEventListener('sound', (e) => {
                    const detail = e?.detail;
                    const name = typeof detail === 'string' ? detail : detail?.name;
                    if (!name) return;
                    play(name);
                });

                window.addEventListener('notify', (e) => {
                    const detail = e?.detail;
                    const type = typeof detail === 'object' && detail ? detail.type : null;
                    if (type === 'success') play('success');
                    else if (type === 'warning') play('warning');
                    else if (type === 'error') play('error');
                });
            })();
        </script>

        @fluxScripts
    </body>
</html>
