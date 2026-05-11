<div class="flex flex-col gap-6 p-4 md:p-8">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">System Health</flux:heading>
            <flux:subheading class="text-zinc-400">Quick production readiness checks</flux:subheading>
        </div>
        <flux:button wire:click="refreshChecks" variant="primary" icon="arrow-path">
            Refresh
        </flux:button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @foreach($groups as $groupName => $checks)
            <flux:card class="p-0 overflow-hidden">
                <div class="px-6 py-4 border-b border-zinc-100 dark:border-zinc-800">
                    <flux:heading size="lg">{{ $groupName }}</flux:heading>
                </div>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach($checks as $check)
                        @php
                            $ok = (bool) ($check['ok'] ?? false);
                            $badgeColor = $ok ? 'green' : 'red';
                        @endphp
                        <div class="px-6 py-4">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $check['label'] }}</div>
                                    @if(!empty($check['help']))
                                        <div class="text-sm text-zinc-500">{{ $check['help'] }}</div>
                                    @endif
                                </div>
                                <div class="shrink-0 text-right">
                                    <flux:badge :color="$badgeColor">{{ $ok ? 'OK' : 'Needs Fix' }}</flux:badge>
                                    <div class="text-sm text-zinc-500 mt-1">{{ $check['value'] ?? '—' }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </flux:card>
        @endforeach
    </div>
</div>

