<div class="flex flex-col gap-6 p-4 md:p-8">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="2">Table Management</flux:heading>
            <flux:subheading>Manage floor layout, table status, and reservations</flux:subheading>
        </div>
        <div class="flex items-center gap-2">
            <flux:button wire:click="create" icon="plus" variant="primary">Add Table</flux:button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <flux:card class="p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon.squares-2x2 class="w-5 h-5 text-zinc-500" />
                </div>
                <div>
                    <flux:text size="sm" class="text-zinc-500">Total</flux:text>
                    <flux:heading size="lg">{{ $this->tableStats['total'] }}</flux:heading>
                </div>
            </div>
        </flux:card>
        <flux:card class="p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-green-100 dark:bg-green-900/30">
                    <flux:icon.check-circle class="w-5 h-5 text-green-600" />
                </div>
                <div>
                    <flux:text size="sm" class="text-zinc-500">Available</flux:text>
                    <flux:heading size="lg" class="text-green-600">{{ $this->tableStats['available'] }}</flux:heading>
                </div>
            </div>
        </flux:card>
        <flux:card class="p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-red-100 dark:bg-red-900/30">
                    <flux:icon.user-group class="w-5 h-5 text-red-600" />
                </div>
                <div>
                    <flux:text size="sm" class="text-zinc-500">Occupied</flux:text>
                    <flux:heading size="lg" class="text-red-600">{{ $this->tableStats['occupied'] }}</flux:heading>
                </div>
            </div>
        </flux:card>
        <flux:card class="p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-amber-100 dark:bg-amber-900/30">
                    <flux:icon.clock class="w-5 h-5 text-amber-600" />
                </div>
                <div>
                    <flux:text size="sm" class="text-zinc-500">Reserved</flux:text>
                    <flux:heading size="lg" class="text-amber-600">{{ $this->tableStats['reserved'] }}</flux:heading>
                </div>
            </div>
        </flux:card>
        <flux:card class="p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon.sparkles class="w-5 h-5 text-zinc-500" />
                </div>
                <div>
                    <flux:text size="sm" class="text-zinc-500">Dirty</flux:text>
                    <flux:heading size="lg">{{ $this->tableStats['dirty'] }}</flux:heading>
                </div>
            </div>
        </flux:card>
    </div>

    {{-- Filters and View Toggle --}}
    <flux:card class="p-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                @if($this->floors->count() > 0)
                    <flux:select wire:model.live="filterFloor" placeholder="All Floors" size="sm">
                        <flux:select.option value="">All Floors</flux:select.option>
                        @foreach($this->floors as $floor)
                            <flux:select.option value="{{ $floor }}">{{ $floor }}</flux:select.option>
                        @endforeach
                    </flux:select>
                @endif
                <flux:select wire:model.live="filterStatus" placeholder="All Status" size="sm">
                    <flux:select.option value="">All Status</flux:select.option>
                    <flux:select.option value="available">Available</flux:select.option>
                    <flux:select.option value="occupied">Occupied</flux:select.option>
                    <flux:select.option value="reserved">Reserved</flux:select.option>
                    <flux:select.option value="dirty">Needs Cleaning</flux:select.option>
                </flux:select>
            </div>
            <div class="flex items-center gap-2">
                <flux:button size="sm" :variant="$viewMode === 'floor' ? 'primary' : 'ghost'" wire:click="$set('viewMode', 'floor')" icon="squares-2x2">
                    Floor View
                </flux:button>
                <flux:button size="sm" :variant="$viewMode === 'list' ? 'primary' : 'ghost'" wire:click="$set('viewMode', 'list')" icon="list-bullet">
                    List View
                </flux:button>
            </div>
        </div>
    </flux:card>

    {{-- Floor Plan View --}}
    @if($viewMode === 'floor')
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @forelse($this->tables as $table)
                <div 
                    wire:click="viewDetails({{ $table->id }})"
                    class="relative cursor-pointer group"
                >
                    {{-- Table Card --}}
                    <div class="
                        aspect-square rounded-xl border-2 flex flex-col items-center justify-center transition-all overflow-hidden relative
                        @if($table->status === 'available')
                            border-green-500 bg-green-500/10 hover:bg-green-500/20
                        @elseif($table->status === 'occupied')
                            border-red-500 bg-red-500/10 hover:bg-red-500/20
                        @elseif($table->status === 'reserved')
                            border-amber-500 bg-amber-500/10 hover:bg-amber-500/20
                        @else
                            border-zinc-500 bg-zinc-500/10 hover:bg-zinc-500/20
                        @endif
                        {{ $table->shape === 'circle' || $table->shape === 'oval' ? 'rounded-full' : '' }}
                    ">
                        {{-- Main content --}}
                        <div class="flex flex-col items-center justify-center p-4 w-full transition-transform duration-200 group-hover:-translate-y-1">
                            {{-- Table Name --}}
                            <flux:heading size="lg" class="font-bold">{{ $table->name }}</flux:heading>

                            {{-- Capacity --}}
                            <div class="flex items-center gap-1 mt-1">
                                <flux:icon.users class="w-3 h-3 text-zinc-400" />
                                <flux:text size="xs" class="text-zinc-400">{{ $table->total_capacity }}</flux:text>
                            </div>

                            {{-- Status badge --}}
                            <flux:badge size="sm" :color="$table->getStatusColor()" class="mt-2">
                                {{ $table->getStatusLabel() }}
                            </flux:badge>

                            {{-- Order KDS Status --}}
                            @if($table->status === 'occupied' && $table->currentOrder)
                                @php $kds = $table->currentOrder->kds_status; @endphp
                                <flux:badge size="sm" class="mt-1.5" :color="match($kds) {
                                    'pending' => 'zinc',
                                    'preparing' => 'amber',
                                    'ready' => 'green',
                                    'served' => 'blue',
                                    default => 'zinc'
                                }">
                                    {{ match($kds) {
                                        'pending' => 'Queued',
                                        'preparing' => 'Preparing',
                                        'ready' => 'Ready',
                                        'served' => 'Served',
                                        default => ucfirst($kds)
                                    } }}
                                </flux:badge>
                                @if($table->currentOrder->payment_status === 'unpaid')
                                    <flux:badge size="sm" class="mt-1" color="rose">Unpaid</flux:badge>
                                @endif
                            @endif

                            {{-- Turn time --}}
                            @if($table->status === 'occupied' && $table->turn_time_formatted)
                                <flux:text size="xs" class="text-zinc-400 mt-1">
                                    {{ $table->turn_time_formatted }}
                                </flux:text>
                            @endif

                            {{-- Reservation name --}}
                            @if($table->status === 'reserved' && $table->reservation_name)
                                <flux:text size="xs" class="text-amber-500 mt-1 truncate max-w-full">
                                    {{ $table->reservation_name }}
                                </flux:text>
                            @endif
                        </div>

                        {{-- Merged indicator --}}
                        @if(!empty($table->merged_table_ids))
                            <div class="absolute top-2 right-2">
                                <flux:badge size="sm" color="blue">
                                    +{{ count($table->merged_table_ids) }}
                                </flux:badge>
                            </div>
                        @endif

                        {{-- Quick Actions — slide up from bottom, inside the card --}}
                        <div class="
                            absolute inset-x-0 bottom-0 
                            flex items-center justify-center gap-1 px-2 py-2
                            translate-y-full group-hover:translate-y-0
                            transition-transform duration-200
                            @if($table->status === 'available')
                                bg-green-500/20
                            @elseif($table->status === 'occupied')
                                bg-red-500/20
                            @elseif($table->status === 'reserved')
                                bg-amber-500/20
                            @else
                                bg-zinc-500/20
                            @endif
                        ">
                            @if($table->status === 'available')
                                <flux:button size="xs" variant="primary" wire:click.stop="quickSeatTable({{ $table->id }})">Seat</flux:button>
                                <flux:button size="xs" variant="ghost" wire:click.stop="openReservationModal({{ $table->id }})">Reserve</flux:button>
                            @elseif($table->status === 'occupied')
                                @if($table->currentOrder)
                                    @if($table->currentOrder->payment_status === 'unpaid')
                                        <flux:button size="xs" variant="filled" class="!bg-amber-500 hover:!bg-amber-600" wire:click.stop="collectTablePayment({{ $table->id }})">Pay</flux:button>
                                    @endif
                                    @if(in_array($table->currentOrder->kds_status, ['preparing', 'ready', 'served']))
                                        <flux:button size="xs" variant="primary" wire:click.stop="addToExistingOrder({{ $table->id }})">+ Add</flux:button>
                                    @else
                                        <flux:button size="xs" variant="primary" wire:click.stop="goToPOS({{ $table->id }})">Order</flux:button>
                                    @endif
                                @else
                                    <flux:button size="xs" variant="primary" wire:click.stop="goToPOS({{ $table->id }})">Order</flux:button>
                                @endif
                                <flux:button size="xs" variant="ghost" wire:click.stop="setTableStatus({{ $table->id }}, 'dirty')">Clear</flux:button>
                            @elseif($table->status === 'reserved')
                                <flux:button size="xs" variant="primary" wire:click.stop="quickSeatTable({{ $table->id }})">Seat</flux:button>
                                <flux:button size="xs" variant="ghost" wire:click.stop="cancelReservation({{ $table->id }})">Cancel</flux:button>
                            @elseif($table->status === 'dirty')
                                <flux:button size="xs" variant="primary" wire:click.stop="setTableStatus({{ $table->id }}, 'available')">Clean</flux:button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <flux:card class="p-12 text-center">
                        <flux:icon.squares-2x2 class="w-12 h-12 mx-auto text-zinc-300 dark:text-zinc-700" />
                        <flux:heading class="mt-4">No tables yet</flux:heading>
                        <flux:subheading>Create your first table to get started.</flux:subheading>
                        <flux:button wire:click="create" variant="primary" class="mt-4">Add Table</flux:button>
                    </flux:card>
                </div>
            @endforelse
        </div>
    @else
        {{-- List View --}}
        <flux:card class="p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700">
                            <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Table</th>
                            <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Floor</th>
                            <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest text-center">Capacity</th>
                            <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest text-center">Status</th>
                            <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest text-center">Order</th>
                            <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Turn Time</th>
                            <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest">Reservation</th>
                            <th class="py-3 px-4 text-xs font-semibold text-zinc-500 uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse($this->tables as $table)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg flex items-center justify-center border-2 
                                            @if($table->status === 'available') border-green-500 bg-green-500/10
                                            @elseif($table->status === 'occupied') border-red-500 bg-red-500/10
                                            @elseif($table->status === 'reserved') border-amber-500 bg-amber-500/10
                                            @else border-zinc-500 bg-zinc-500/10
                                            @endif
                                        ">
                                            <flux:text class="font-bold text-sm">{{ $table->code ?? Str::limit($table->name, 3, '') }}</flux:text>
                                        </div>
                                        <div>
                                            <flux:text class="font-semibold">{{ $table->name }}</flux:text>
                                            @if(!empty($table->merged_table_ids))
                                                <flux:badge size="sm" color="blue">Merged ({{ count($table->merged_table_ids) + 1 }})</flux:badge>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <flux:text class="text-zinc-500">{{ $table->floor ?? '-' }}</flux:text>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <flux:badge color="zinc" size="sm">{{ $table->total_capacity }}</flux:badge>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <flux:badge :color="$table->getStatusColor()" size="sm">
                                        {{ $table->getStatusLabel() }}
                                    </flux:badge>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($table->status === 'occupied' && $table->currentOrder)
                                        @php $kds = $table->currentOrder->kds_status; @endphp
                                        <div class="flex flex-col items-center gap-1">
                                            <flux:badge size="sm" :color="match($kds) {
                                                'pending' => 'zinc',
                                                'preparing' => 'amber',
                                                'ready' => 'green',
                                                'served' => 'blue',
                                                default => 'zinc'
                                            }">
                                                {{ match($kds) {
                                                    'pending' => 'Queued',
                                                    'preparing' => 'Preparing',
                                                    'ready' => 'Ready',
                                                    'served' => 'Served',
                                                    default => ucfirst($kds)
                                                } }}
                                            </flux:badge>
                                            @if($table->currentOrder->payment_status === 'unpaid')
                                                <flux:badge size="sm" color="rose">Unpaid</flux:badge>
                                            @else
                                                <flux:badge size="sm" color="green">Paid</flux:badge>
                                            @endif
                                        </div>
                                    @else
                                        <flux:text class="text-zinc-400">-</flux:text>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    @if($table->status === 'occupied' && $table->turn_time_formatted)
                                        <flux:text>{{ $table->turn_time_formatted }}</flux:text>
                                    @else
                                        <flux:text class="text-zinc-400">-</flux:text>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    @if($table->reservation_name)
                                        <div>
                                            <flux:text class="font-medium">{{ $table->reservation_name }}</flux:text>
                                            @if($table->reservation_phone)
                                                <flux:text size="sm" class="text-zinc-400">{{ $table->reservation_phone }}</flux:text>
                                            @endif
                                        </div>
                                    @else
                                        <flux:text class="text-zinc-400">-</flux:text>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($table->status === 'available')
                                            <flux:button size="sm" variant="primary" wire:click="quickSeatTable({{ $table->id }})">Seat</flux:button>
                                        @elseif($table->status === 'occupied')
                                            @if($table->currentOrder)
                                                @if($table->currentOrder->payment_status === 'unpaid')
                                                    <flux:button size="sm" variant="filled" class="!bg-amber-500 hover:!bg-amber-600 !text-white" wire:click="collectTablePayment({{ $table->id }})">Pay</flux:button>
                                                @endif
                                                @if(in_array($table->currentOrder->kds_status, ['preparing', 'ready', 'served']))
                                                    <flux:button size="sm" variant="primary" wire:click="addToExistingOrder({{ $table->id }})">+ Add</flux:button>
                                                @else
                                                    <flux:button size="sm" variant="primary" wire:click="goToPOS({{ $table->id }})">Order</flux:button>
                                                @endif
                                            @else
                                                <flux:button size="sm" variant="primary" wire:click="goToPOS({{ $table->id }})">Order</flux:button>
                                            @endif
                                        @endif
                                        <flux:dropdown position="bottom" align="end">
                                            <flux:button size="sm" variant="ghost" icon="ellipsis-horizontal" square />
                                            <flux:menu>
                                                <flux:menu.item icon="eye" wire:click="viewDetails({{ $table->id }})">View Details</flux:menu.item>
                                                <flux:menu.item icon="pencil-square" wire:click="edit({{ $table->id }})">Edit Table</flux:menu.item>
                                                @if($table->status === 'available')
                                                    <flux:menu.item icon="clock" wire:click="openReservationModal({{ $table->id }})">Make Reservation</flux:menu.item>
                                                    <flux:menu.item icon="squares-plus" wire:click="openMergeModal({{ $table->id }})">Merge Tables</flux:menu.item>
                                                @endif
                                                @if(!empty($table->merged_table_ids))
                                                    <flux:menu.item icon="scissors" wire:click="splitTables({{ $table->id }})">Split Tables</flux:menu.item>
                                                @endif
                                                <flux:menu.separator />
                                                <flux:menu.submenu heading="Set Status">
                                                    <flux:menu.item wire:click="setTableStatus({{ $table->id }}, 'available')">Available</flux:menu.item>
                                                    <flux:menu.item wire:click="setTableStatus({{ $table->id }}, 'occupied')">Occupied</flux:menu.item>
                                                    <flux:menu.item wire:click="setTableStatus({{ $table->id }}, 'dirty')">Needs Cleaning</flux:menu.item>
                                                </flux:menu.submenu>
                                                <flux:menu.separator />
                                                <flux:menu.item icon="trash" variant="danger" wire:click="deleteTable({{ $table->id }})" wire:confirm="Are you sure you want to delete this table?">
                                                    Delete
                                                </flux:menu.item>
                                            </flux:menu>
                                        </flux:dropdown>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-24 text-center">
                                    <flux:icon.squares-2x2 class="w-12 h-12 mx-auto text-zinc-300 dark:text-zinc-700" />
                                    <flux:heading class="mt-4">No tables yet</flux:heading>
                                    <flux:subheading>Create your first table to get started.</flux:subheading>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </flux:card>
    @endif

    {{-- Table Modal --}}
    <flux:modal wire:model="showTableModal" class="max-w-lg">
        <div class="space-y-5">
            <flux:heading size="lg">{{ $editingTableId ? 'Edit Table' : 'Add New Table' }}</flux:heading>

            {{-- Single / Bulk toggle (only on create) --}}
            @if(!$editingTableId)
                <div class="flex items-center gap-1 p-1 bg-zinc-100 dark:bg-zinc-800 rounded-lg w-fit">
                    <button
                        wire:click="$set('createMode', 'single')"
                        class="px-4 py-1.5 text-sm font-medium rounded-md transition-colors
                            {{ $createMode === 'single'
                                ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-sm'
                                : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200' }}">
                        Single
                    </button>
                    <button
                        wire:click="$set('createMode', 'bulk')"
                        class="px-4 py-1.5 text-sm font-medium rounded-md transition-colors
                            {{ $createMode === 'bulk'
                                ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-sm'
                                : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200' }}">
                        Bulk
                    </button>
                </div>
            @endif

            {{-- Single mode --}}
            @if($createMode === 'single' || $editingTableId)
                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="tableName" label="Table Name" placeholder="e.g., Table 1" required />
                    <flux:input wire:model="tableCode" label="Short Code" placeholder="e.g., T1" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="tableCapacity" type="number" label="Capacity" min="1" max="50" />
                    <flux:select wire:model="tableShape" label="Shape">
                        <flux:select.option value="square">Square</flux:select.option>
                        <flux:select.option value="rectangle">Rectangle</flux:select.option>
                        <flux:select.option value="circle">Circle</flux:select.option>
                        <flux:select.option value="oval">Oval</flux:select.option>
                    </flux:select>
                </div>

                <flux:input wire:model="tableFloor" label="Floor / Area" placeholder="e.g., Main Floor, Patio" />

                <flux:switch wire:model="tableIsActive" label="Active" description="Inactive tables won't appear in floor plan" />
            @endif

            {{-- Bulk mode --}}
            @if($createMode === 'bulk' && !$editingTableId)
                <div>
                    <flux:input
                        wire:model.live="bulkRange"
                        label="Table Range"
                        placeholder="e.g., T1-T10, ML1-ML5, 1-20"
                        description="Use a prefix + number range. Examples: T1-T10, ML1-ML5, A01-A12, 1-30"
                    />
                    @error('bulkRange')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror

                    {{-- Live preview --}}
                    @if(!empty($bulkPreview))
                        <div class="mt-3 p-3 bg-zinc-50 dark:bg-zinc-800/60 rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-2">
                                Preview &mdash; {{ count($bulkPreview) }} table(s) will be created
                            </p>
                            <div class="flex flex-wrap gap-1.5 max-h-28 overflow-y-auto">
                                @foreach($bulkPreview as $name)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-zinc-200 dark:bg-zinc-700 text-xs font-mono text-zinc-700 dark:text-zinc-300">
                                        {{ $name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @elseif($bulkRange)
                        <p class="mt-2 text-xs text-amber-500">No tables matched. Check your range format.</p>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="bulkCapacity" type="number" label="Capacity (per table)" min="1" max="50" />
                    <flux:select wire:model="bulkShape" label="Shape">
                        <flux:select.option value="square">Square</flux:select.option>
                        <flux:select.option value="rectangle">Rectangle</flux:select.option>
                        <flux:select.option value="circle">Circle</flux:select.option>
                        <flux:select.option value="oval">Oval</flux:select.option>
                    </flux:select>
                </div>

                <flux:input wire:model="bulkFloor" label="Floor / Area" placeholder="e.g., Main Floor, Patio" />

                <flux:switch wire:model="bulkIsActive" label="Active" description="Inactive tables won't appear in floor plan" />
            @endif

            <div class="flex justify-end gap-3 pt-2">
                <flux:button wire:click="$set('showTableModal', false)" variant="ghost">Cancel</flux:button>
                <flux:button wire:click="saveTable" variant="primary">
                    @if($editingTableId)
                        Update Table
                    @elseif($createMode === 'bulk')
                        Create {{ count($bulkPreview) > 0 ? count($bulkPreview) : '' }} Tables
                    @else
                        Create Table
                    @endif
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Reservation Modal --}}
    <flux:modal wire:model="showReservationModal" class="max-w-md">
        <div class="space-y-6">
            <flux:heading size="lg">Make Reservation</flux:heading>
            
            <flux:input wire:model="reservationName" label="Guest Name" placeholder="Enter guest name" required />
            <flux:input wire:model="reservationPhone" label="Phone Number" placeholder="Enter phone number" />
            <flux:textarea wire:model="reservationNotes" label="Notes" placeholder="Special requests, party size, etc." rows="3" />
            
            <div class="flex justify-end gap-3 pt-4">
                <flux:button wire:click="$set('showReservationModal', false)" variant="ghost">Cancel</flux:button>
                <flux:button wire:click="saveReservation" variant="primary">Save Reservation</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Merge Tables Modal --}}
    <flux:modal wire:model="showMergeModal" class="max-w-md">
        <div class="space-y-6">
            <flux:heading size="lg">Merge Tables</flux:heading>
            <flux:subheading>Select tables to merge with the primary table.</flux:subheading>
            
            <div class="space-y-2 max-h-64 overflow-y-auto">
                @foreach($this->allTables->where('id', '!=', $primaryTableId)->where('status', 'available') as $table)
                    <label class="flex items-center gap-3 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 cursor-pointer">
                        <input type="checkbox" wire:model="selectedMergeTables" value="{{ $table->id }}" class="rounded" />
                        <div>
                            <flux:text class="font-medium">{{ $table->name }}</flux:text>
                            <flux:text size="sm" class="text-zinc-400">Capacity: {{ $table->capacity }}</flux:text>
                        </div>
                    </label>
                @endforeach
            </div>
            
            <div class="flex justify-end gap-3 pt-4">
                <flux:button wire:click="$set('showMergeModal', false)" variant="ghost">Cancel</flux:button>
                <flux:button wire:click="mergeTables" variant="primary">Merge Tables</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Table Details Modal --}}
    <flux:modal wire:model="showDetailsModal" class="max-w-lg">
        @if($this->detailsTable)
            <div class="space-y-6">
                <div class="flex items-start justify-between">
                    <div>
                        <flux:heading size="lg">{{ $this->detailsTable->name }}</flux:heading>
                        <flux:badge :color="$this->detailsTable->getStatusColor()" class="mt-2">
                            {{ $this->detailsTable->getStatusLabel() }}
                        </flux:badge>
                    </div>
                    <flux:button wire:click="edit({{ $this->detailsTable->id }})" size="sm" variant="ghost" icon="pencil-square" />
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 rounded-lg bg-zinc-50 dark:bg-zinc-800">
                        <flux:text size="sm" class="text-zinc-500">Capacity</flux:text>
                        <flux:heading>{{ $this->detailsTable->total_capacity }} seats</flux:heading>
                    </div>
                    <div class="p-4 rounded-lg bg-zinc-50 dark:bg-zinc-800">
                        <flux:text size="sm" class="text-zinc-500">Floor</flux:text>
                        <flux:heading>{{ $this->detailsTable->floor ?? 'Main' }}</flux:heading>
                    </div>
                </div>
                
                @if($this->detailsTable->status === 'occupied')
                    <div class="p-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                        <div class="flex items-center justify-between mb-2">
                            <flux:text size="sm" class="text-red-600 font-medium">Turn Time</flux:text>
                            <flux:heading class="text-red-600">{{ $this->detailsTable->turn_time_formatted ?? '0m' }}</flux:heading>
                        </div>
                        @if($this->detailsTable->currentOrder)
                            <flux:text size="sm" class="text-zinc-500">
                                Order #{{ $this->detailsTable->currentOrder->id }} - 
                                ${{ number_format($this->detailsTable->currentOrder->total_amount, 2) }}
                            </flux:text>
                        @endif
                    </div>
                @endif
                
                @if($this->detailsTable->status === 'reserved')
                    <div class="p-4 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
                        <flux:text size="sm" class="text-amber-600 font-medium">Reservation</flux:text>
                        <flux:heading>{{ $this->detailsTable->reservation_name }}</flux:heading>
                        @if($this->detailsTable->reservation_phone)
                            <flux:text size="sm" class="text-zinc-500">{{ $this->detailsTable->reservation_phone }}</flux:text>
                        @endif
                        @if($this->detailsTable->reservation_notes)
                            <flux:text size="sm" class="text-zinc-500 mt-2">{{ $this->detailsTable->reservation_notes }}</flux:text>
                        @endif
                    </div>
                @endif
                
                @if(!empty($this->detailsTable->merged_table_ids))
                    <div class="p-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
                        <flux:text size="sm" class="text-blue-600 font-medium mb-2">Merged Tables</flux:text>
                        <div class="flex flex-wrap gap-2">
                            @foreach($this->detailsTable->mergedTables as $merged)
                                <flux:badge color="blue">{{ $merged->name }}</flux:badge>
                            @endforeach
                        </div>
                    </div>
                @endif
                
                <div class="flex justify-between gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <div class="flex gap-2">
                        @if($this->detailsTable->status === 'occupied')
                            <flux:button wire:click="goToPOS({{ $this->detailsTable->id }})" variant="primary" icon="shopping-cart">View Order</flux:button>
                            <flux:button wire:click="setTableStatus({{ $this->detailsTable->id }}, 'dirty')" variant="ghost">Clear Table</flux:button>
                        @elseif($this->detailsTable->status === 'available')
                            <flux:button wire:click="quickSeatTable({{ $this->detailsTable->id }})" variant="primary">Seat Guests</flux:button>
                            <flux:button wire:click="openReservationModal({{ $this->detailsTable->id }})" variant="ghost">Reserve</flux:button>
                        @elseif($this->detailsTable->status === 'reserved')
                            <flux:button wire:click="quickSeatTable({{ $this->detailsTable->id }})" variant="primary">Seat Guests</flux:button>
                            <flux:button wire:click="cancelReservation({{ $this->detailsTable->id }})" variant="ghost">Cancel</flux:button>
                        @elseif($this->detailsTable->status === 'dirty')
                            <flux:button wire:click="setTableStatus({{ $this->detailsTable->id }}, 'available')" variant="primary">Mark Clean</flux:button>
                        @endif
                    </div>
                    <flux:button wire:click="$set('showDetailsModal', false)" variant="ghost">Close</flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
