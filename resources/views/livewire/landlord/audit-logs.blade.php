<div class="flex flex-col gap-6 p-4 md:p-8">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">Audit Logs</flux:heading>
            <flux:subheading class="text-zinc-400">Track landlord actions and important system events</flux:subheading>
        </div>
        <div class="flex items-center gap-2">
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">
                Clear
            </flux:button>
        </div>
    </div>

    <flux:card class="p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div class="md:col-span-2">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search action, tenant, user, IP, subject..."
                />
            </div>
            <div>
                <flux:select wire:model.live="action" placeholder="All actions">
                    <flux:select.option value="">All actions</flux:select.option>
                    @foreach($actionOptions as $opt)
                        <flux:select.option value="{{ $opt }}">{{ $opt }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
            <div class="flex items-center gap-2">
                <flux:input type="date" wire:model.live="date_from" />
                <flux:separator vertical class="h-6" />
                <flux:input type="date" wire:model.live="date_to" />
            </div>
        </div>
    </flux:card>

    <flux:card class="p-0 overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-100 dark:border-zinc-800">
            <div>
                <flux:heading size="lg">Events</flux:heading>
                <flux:text size="sm" class="text-zinc-400">Most recent first</flux:text>
            </div>
            <flux:badge color="zinc">{{ number_format($logs->total()) }} total</flux:badge>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700">
                        <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Time</th>
                        <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Action</th>
                        <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Tenant</th>
                        <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Actor</th>
                        <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Subject</th>
                        <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest text-right">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($logs as $log)
                        @php
                            $tenantLabel = $log->tenant ? $log->tenant->name : '—';
                            $actorLabel = $log->actor ? $log->actor->name : 'System';
                            $subject = $log->subject_type
                                ? (class_basename($log->subject_type) . ($log->subject_id ? ' #' . $log->subject_id : ''))
                                : '—';
                            $actionColor = str_contains($log->action, 'created')
                                ? 'green'
                                : (str_contains($log->action, 'updated') ? 'blue' : (str_contains($log->action, 'toggled') ? 'amber' : 'zinc'));
                        @endphp
                        <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                            <td class="py-3 px-4 text-zinc-500 whitespace-nowrap">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2">
                                    <flux:badge :color="$actionColor">{{ $log->action }}</flux:badge>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="min-w-0">
                                    <div class="font-semibold text-zinc-800 dark:text-zinc-100 truncate">{{ $tenantLabel }}</div>
                                    @if($log->tenant)
                                        <div class="text-xs text-zinc-500">/{{ $log->tenant->slug }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="min-w-0">
                                    <div class="font-semibold text-zinc-800 dark:text-zinc-100 truncate">{{ $actorLabel }}</div>
                                    @if($log->actor?->email)
                                        <div class="text-xs text-zinc-500 truncate">{{ $log->actor->email }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-4 text-zinc-500 whitespace-nowrap">{{ $subject }}</td>
                            <td class="py-3 px-4 text-right text-zinc-500 whitespace-nowrap">{{ $log->ip ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 px-6 text-center text-zinc-500">No audit logs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="px-6 py-4 border-t border-zinc-100 dark:border-zinc-800">
                {{ $logs->links() }}
            </div>
        @endif
    </flux:card>
</div>

