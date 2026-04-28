<div class="flex flex-col gap-6 p-4 md:p-8">

    {{-- Header --}}
    <div>
        <flux:heading size="xl" level="2">User Guide</flux:heading>
        <flux:subheading>Everything you need to know to operate FnB Cloud POS</flux:subheading>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">

        {{-- Sidebar Nav --}}
        <div class="lg:w-56 shrink-0">
            <flux:card class="p-2 sticky top-4">
                <nav class="flex flex-col gap-0.5">
                    @foreach($sections as $key => $section)
                        <button
                            wire:click="setSection('{{ $key }}')"
                            class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-left transition-colors
                                {{ $activeSection === $key
                                    ? 'bg-pink-500/20 text-pink-400'
                                    : 'text-zinc-400 hover:text-zinc-100 hover:bg-zinc-700/50' }}"
                        >
                            <flux:icon name="{{ $section['icon'] }}" class="size-4 shrink-0" />
                            {{ $section['title'] }}
                        </button>
                    @endforeach
                </nav>
            </flux:card>
        </div>

        {{-- Content --}}
        <div class="flex-1 min-w-0">

            {{-- Getting Started --}}
            @if($activeSection === 'getting-started')
            <flux:card class="p-6 md:p-8">
                <flux:heading size="xl" level="2" class="mb-1">Getting Started</flux:heading>
                <flux:text class="text-zinc-400 mb-8">A daily overview of how to run your business with FnB Cloud.</flux:text>

                <div class="space-y-8">
                    <div>
                        <h3 class="text-base font-semibold text-zinc-100 mb-3">Daily Workflow</h3>
                        <ol class="space-y-3">
                            @foreach([
                                ['Open a Shift', 'Go to Shifts and click "Open Shift". Enter your starting cash amount.'],
                                ['Take Orders via POS', 'Use the POS screen to add items to the cart and process payments.'],
                                ['Monitor Kitchen via KDS', 'Kitchen staff can view and update order status on the KDS screen.'],
                                ['Track Orders', 'View all orders from the Orders page. Update status as needed.'],
                                ['Close Your Shift', 'At end of day, go to Shifts, click the active shift and close it by entering the actual cash in the drawer.'],
                                ['Review Reports', 'Check the Sales Report and Cashier Report to review the day\'s performance.'],
                            ] as [$step, $desc])
                            <li class="flex gap-4">
                                <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-pink-500/20 text-pink-400 text-sm font-bold">{{ $loop->iteration }}</span>
                                <div>
                                    <div class="font-semibold text-zinc-100 text-sm">{{ $step }}</div>
                                    <div class="text-zinc-400 text-sm mt-0.5">{{ $desc }}</div>
                                </div>
                            </li>
                            @endforeach
                        </ol>
                    </div>

                    <div>
                        <h3 class="text-base font-semibold text-zinc-100 mb-3">User Roles</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-zinc-700">
                                        <th class="text-left py-2 px-3 text-zinc-400 font-semibold">Role</th>
                                        <th class="text-left py-2 px-3 text-zinc-400 font-semibold">Access</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-800">
                                    @foreach([
                                        ['Owner / Admin', 'Full access to all features, settings, reports, and user management.'],
                                        ['Cashier', 'POS, Shifts, and Orders access.'],
                                        ['Waiter', 'POS access to take orders.'],
                                        ['Kitchen Staff', 'Kitchen Display System (KDS) access only.'],
                                    ] as [$role, $access])
                                    <tr>
                                        <td class="py-2.5 px-3 font-medium text-zinc-200">{{ $role }}</td>
                                        <td class="py-2.5 px-3 text-zinc-400">{{ $access }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </flux:card>
            @endif

            {{-- Shift Management --}}
            @if($activeSection === 'shifts')
            <flux:card class="p-6 md:p-8">
                <flux:heading size="xl" level="2" class="mb-1">Shift Management</flux:heading>
                <flux:text class="text-zinc-400 mb-8">Track cash drawer, open/close shifts, and reconcile at end of day.</flux:text>

                <div class="space-y-8">
                    <div>
                        <h3 class="text-base font-semibold text-zinc-100 mb-3">Opening a Shift</h3>
                        <ol class="space-y-2 text-sm text-zinc-300 list-decimal list-inside space-y-2">
                            <li>Navigate to <span class="font-medium text-zinc-100">Shifts</span> from the sidebar.</li>
                            <li>Click the <span class="font-medium text-pink-400">Open Shift</span> button in the top right.</li>
                            <li>Enter the starting cash amount (the cash currently in your drawer).</li>
                            <li>Click <span class="font-medium text-zinc-100">Open Shift</span> to confirm. Orders can now be processed.</li>
                        </ol>
                    </div>

                    <div>
                        <h3 class="text-base font-semibold text-zinc-100 mb-3">Cash Drawer Movements</h3>
                        <p class="text-sm text-zinc-400 mb-3">You can record cash going in or out of the drawer at any time during an active shift.</p>
                        <ol class="space-y-2 text-sm text-zinc-300 list-decimal list-inside">
                            <li>Click <span class="font-medium text-zinc-100">Cash Movement</span> on the active shift card.</li>
                            <li>Select the type: <span class="font-medium text-zinc-100">Cash In</span>, <span class="font-medium text-zinc-100">Cash Out</span>, or <span class="font-medium text-zinc-100">Adjustment</span>.</li>
                            <li>Enter the amount and an optional note, then confirm.</li>
                        </ol>
                    </div>

                    <div>
                        <h3 class="text-base font-semibold text-zinc-100 mb-3">Closing a Shift & Reconciliation</h3>
                        <ol class="space-y-2 text-sm text-zinc-300 list-decimal list-inside">
                            <li>Click <span class="font-medium text-zinc-100">Close Shift</span> on the active shift card.</li>
                            <li>Count the physical cash in your drawer and enter the <span class="font-medium text-zinc-100">Actual Cash</span> amount.</li>
                            <li>The system automatically calculates the <span class="font-medium text-zinc-100">Expected Cash</span> (opening cash + cash sales + cash in - cash out).</li>
                            <li>A <span class="text-green-400 font-medium">positive difference</span> means more cash than expected. A <span class="text-red-400 font-medium">negative difference</span> means a shortage.</li>
                            <li>Confirm to close the shift.</li>
                        </ol>
                    </div>

                    <div>
                        <h3 class="text-base font-semibold text-zinc-100 mb-3">Shift History & Filtering</h3>
                        <p class="text-sm text-zinc-400">Use the date picker and cashier dropdown in the Shift History section to filter past shifts. Click the eye icon on any row to view full shift details including all cash movements.</p>
                    </div>
                </div>
            </flux:card>
            @endif

            {{-- POS --}}
            @if($activeSection === 'pos')
            <flux:card class="p-6 md:p-8">
                <flux:heading size="xl" level="2" class="mb-1">POS & Taking Orders</flux:heading>
                <flux:text class="text-zinc-400 mb-8">Process orders quickly using the point of sale screen.</flux:text>

                <div class="space-y-8">
                    <div>
                        <h3 class="text-base font-semibold text-zinc-100 mb-3">Taking an Order</h3>
                        <ol class="space-y-2 text-sm text-zinc-300 list-decimal list-inside">
                            <li>Make sure a shift is open before taking orders.</li>
                            <li>Browse categories on the left panel and click a product to add it to the cart.</li>
                            <li>If the product has <span class="font-medium text-zinc-100">variants</span> (e.g. size), a popup will appear to select one.</li>
                            <li>If the product has <span class="font-medium text-zinc-100">add-ons</span>, select any extras before adding to the cart.</li>
                            <li>Adjust quantities in the cart using the + / - buttons.</li>
                            <li>Add an optional <span class="font-medium text-zinc-100">order note</span> using the note button.</li>
                        </ol>
                    </div>

                    <div>
                        <h3 class="text-base font-semibold text-zinc-100 mb-3">Discounts & Vouchers</h3>
                        <div class="space-y-3 text-sm text-zinc-300">
                            <p><span class="font-medium text-zinc-100">Discount:</span> Click the discount button in the cart to apply a percentage or fixed discount to the entire order.</p>
                            <p><span class="font-medium text-zinc-100">Voucher:</span> Enter a voucher code at checkout. The system validates the code and applies the discount automatically.</p>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-base font-semibold text-zinc-100 mb-3">Customer & Loyalty Points</h3>
                        <ol class="space-y-2 text-sm text-zinc-300 list-decimal list-inside">
                            <li>Click <span class="font-medium text-zinc-100">Add Customer</span> to attach a loyalty customer to the order.</li>
                            <li>Search by name or phone number.</li>
                            <li>The customer can choose to <span class="font-medium text-zinc-100">redeem points</span> for a discount on this order.</li>
                            <li>Points are automatically earned after a successful payment.</li>
                        </ol>
                    </div>

                    <div>
                        <h3 class="text-base font-semibold text-zinc-100 mb-3">Payment</h3>
                        <div class="space-y-3 text-sm text-zinc-300">
                            <p><span class="font-medium text-zinc-100">Single Payment:</span> Select Cash or Card and confirm the amount to complete the order.</p>
                            <p><span class="font-medium text-zinc-100">Split Payment:</span> Click "Split Payment" to divide the bill between multiple payment methods. Enter the amount for each method.</p>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-base font-semibold text-zinc-100 mb-3">Held Orders</h3>
                        <p class="text-sm text-zinc-400">You can hold an order to serve another customer first. Click <span class="font-medium text-zinc-100">Hold Order</span> in the cart to save it. Retrieve held orders from the held orders panel at any time.</p>
                    </div>

                    <div>
                        <h3 class="text-base font-semibold text-zinc-100 mb-3">Table Numbers & Order Types</h3>
                        <p class="text-sm text-zinc-400">Select the order type (Dine In, Takeaway, Delivery) before confirming an order. For Dine In, you can assign a table number to the order.</p>
                    </div>
                </div>
            </flux:card>
            @endif

            {{-- Orders --}}
            @if($activeSection === 'orders')
            <flux:card class="p-6 md:p-8">
                <flux:heading size="xl" level="2" class="mb-1">Order Management</flux:heading>
                <flux:text class="text-zinc-400 mb-8">View and manage all orders from a central list.</flux:text>

                <div class="space-y-8">
                    <div>
                        <h3 class="text-base font-semibold text-zinc-100 mb-3">Order Statuses</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-zinc-700">
                                        <th class="text-left py-2 px-3 text-zinc-400 font-semibold">Status</th>
                                        <th class="text-left py-2 px-3 text-zinc-400 font-semibold">Meaning</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-800">
                                    @foreach([
                                        ['Pending', 'Order has been placed but not yet started in the kitchen.'],
                                        ['Preparing', 'Kitchen has started preparing the order.'],
                                        ['Ready', 'Order is ready to be served or picked up.'],
                                        ['Completed', 'Order has been served/delivered and is closed.'],
                                        ['Cancelled', 'Order was cancelled before completion.'],
                                    ] as [$status, $meaning])
                                    <tr>
                                        <td class="py-2.5 px-3 font-medium text-zinc-200">{{ $status }}</td>
                                        <td class="py-2.5 px-3 text-zinc-400">{{ $meaning }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-base font-semibold text-zinc-100 mb-3">Filtering Orders</h3>
                        <p class="text-sm text-zinc-400">Use the search bar to find orders by order number or customer name. Filter by status using the tabs at the top of the order list.</p>
                    </div>

                    <div>
                        <h3 class="text-base font-semibold text-zinc-100 mb-3">Viewing Order Details</h3>
                        <p class="text-sm text-zinc-400">Click on any order row to open the full order details including items, quantities, payment method, and timestamps. You can also print a receipt from the order detail view.</p>
                    </div>
                </div>
            </flux:card>
            @endif

            {{-- KDS --}}
            @if($activeSection === 'kds')
            <flux:card class="p-6 md:p-8">
                <flux:heading size="xl" level="2" class="mb-1">Kitchen Display System (KDS)</flux:heading>
                <flux:text class="text-zinc-400 mb-8">A dedicated screen for kitchen staff to manage incoming orders.</flux:text>

                <div class="space-y-8">
                    <div>
                        <h3 class="text-base font-semibold text-zinc-100 mb-3">How it Works</h3>
                        <ol class="space-y-2 text-sm text-zinc-300 list-decimal list-inside">
                            <li>When an order is placed via POS, it automatically appears on the KDS screen.</li>
                            <li>Each order is displayed as a card showing all items, quantities, and any notes.</li>
                            <li>Click <span class="font-medium text-zinc-100">Start Preparing</span> to move the order from Pending to Preparing status.</li>
                            <li>Click <span class="font-medium text-zinc-100">Mark Ready</span> once the food is prepared. This notifies front-of-house staff.</li>
                        </ol>
                    </div>

                    <div>
                        <h3 class="text-base font-semibold text-zinc-100 mb-3">Tips for Kitchen Use</h3>
                        <ul class="space-y-2 text-sm text-zinc-400 list-disc list-inside">
                            <li>Keep the KDS screen visible at all times in the kitchen.</li>
                            <li>Orders are displayed in the order they were placed (oldest first).</li>
                            <li>Order cards are colour-coded by age to help prioritise which to prepare first.</li>
                        </ul>
                    </div>
                </div>
            </flux:card>
            @endif

            {{-- Menu Management --}}
            @if($activeSection === 'menu')
            <flux:card class="p-6 md:p-8">
                <flux:heading size="xl" level="2" class="mb-1">Menu Management</flux:heading>
                <flux:text class="text-zinc-400 mb-8">Set up and manage your products, categories, and add-ons.</flux:text>

                <div class="space-y-8">
                    <div>
                        <h3 class="text-base font-semibold text-zinc-100 mb-3">Categories</h3>
                        <p class="text-sm text-zinc-400 mb-2">Categories group your products on the POS screen. Go to <span class="font-medium text-zinc-100">Menu &rarr; Categories</span> to:</p>
                        <ul class="space-y-1 text-sm text-zinc-400 list-disc list-inside">
                            <li>Create, rename, or delete categories.</li>
                            <li>Reorder categories by dragging them.</li>
                            <li>Toggle a category active/inactive to hide it from the POS without deleting it.</li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-base font-semibold text-zinc-100 mb-3">Products</h3>
                        <p class="text-sm text-zinc-400 mb-2">Go to <span class="font-medium text-zinc-100">Menu &rarr; Products</span> to manage your menu items:</p>
                        <ul class="space-y-1 text-sm text-zinc-400 list-disc list-inside">
                            <li>Add a product with a name, category, price, and optional image.</li>
                            <li>Add <span class="font-medium text-zinc-100">variants</span> (e.g. Small / Medium / Large) with individual prices.</li>
                            <li>Attach <span class="font-medium text-zinc-100">add-on groups</span> to allow customers to customise the item.</li>
                            <li>Toggle products active/inactive to temporarily remove them from the POS.</li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-base font-semibold text-zinc-100 mb-3">Add-ons</h3>
                        <p class="text-sm text-zinc-400 mb-2">Go to <span class="font-medium text-zinc-100">Menu &rarr; Add-ons</span> to create add-on groups:</p>
                        <ul class="space-y-1 text-sm text-zinc-400 list-disc list-inside">
                            <li>Create an add-on group (e.g. "Extras", "Sauce", "Toppings").</li>
                            <li>Add individual items to each group with a name and price (can be free).</li>
                            <li>Set whether the group is required or optional, and if multiple selections are allowed.</li>
                        </ul>
                    </div>
                </div>
            </flux:card>
            @endif

            {{-- Loyalty Program --}}
            @if($activeSection === 'loyalty')
            <flux:card class="p-6 md:p-8">
                <flux:heading size="xl" level="2" class="mb-1">Loyalty Program</flux:heading>
                <flux:text class="text-zinc-400 mb-8">Reward repeat customers with points and vouchers.</flux:text>

                <div class="space-y-8">
                    <div>
                        <h3 class="text-base font-semibold text-zinc-100 mb-3">Loyalty Points</h3>
                        <ul class="space-y-2 text-sm text-zinc-400 list-disc list-inside">
                            <li>Customers earn points on every purchase based on the points-per-dollar rate you configure in <span class="font-medium text-zinc-100">Settings &rarr; Loyalty</span>.</li>
                            <li>Points can be redeemed at the POS for a discount on future orders.</li>
                            <li>Configure the minimum redemption threshold and the cash value per point in Settings.</li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-base font-semibold text-zinc-100 mb-3">Customers</h3>
                        <p class="text-sm text-zinc-400 mb-2">Go to <span class="font-medium text-zinc-100">Loyalty &rarr; Customers</span> to:</p>
                        <ul class="space-y-1 text-sm text-zinc-400 list-disc list-inside">
                            <li>Add new customers with name, phone, and email.</li>
                            <li>View a customer&apos;s total points, transaction history, and redeemed vouchers.</li>
                            <li>Manually adjust a customer&apos;s points balance if needed.</li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-base font-semibold text-zinc-100 mb-3">Vouchers</h3>
                        <p class="text-sm text-zinc-400 mb-2">Go to <span class="font-medium text-zinc-100">Loyalty &rarr; Vouchers</span> to:</p>
                        <ul class="space-y-1 text-sm text-zinc-400 list-disc list-inside">
                            <li>Create vouchers with a fixed or percentage discount value.</li>
                            <li>Set usage limits, expiry dates, and minimum order amounts.</li>
                            <li>Configure auto-issue rules to automatically send vouchers when customers reach a spending threshold.</li>
                            <li>View all issued vouchers and their redemption status.</li>
                        </ul>
                    </div>
                </div>
            </flux:card>
            @endif

            {{-- Reports --}}
            @if($activeSection === 'reports')
            <flux:card class="p-6 md:p-8">
                <flux:heading size="xl" level="2" class="mb-1">Reports</flux:heading>
                <flux:text class="text-zinc-400 mb-8">Analyse sales performance and cashier activity.</flux:text>

                <div class="space-y-8">
                    <div>
                        <h3 class="text-base font-semibold text-zinc-100 mb-3">Sales Report</h3>
                        <p class="text-sm text-zinc-400 mb-2">Go to <span class="font-medium text-zinc-100">Sales Report</span> from the sidebar.</p>
                        <ul class="space-y-1 text-sm text-zinc-400 list-disc list-inside">
                            <li>View summary cards: Total Orders, Gross Sales, Discounts, Tax, and Net Sales.</li>
                            <li>Use the Revenue Trend chart to see daily sales visually.</li>
                            <li>Filter by date range using the date pickers or the Today / 7D / Month quick buttons.</li>
                            <li>The Daily Summary table breaks down each day&apos;s figures.</li>
                            <li>Payment Methods panel shows split between Cash and Card revenue.</li>
                            <li>Top Products panel shows your best-selling items by quantity and revenue.</li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-base font-semibold text-zinc-100 mb-3">Cashier Report</h3>
                        <p class="text-sm text-zinc-400 mb-2">Go to <span class="font-medium text-zinc-100">Cashier Report</span> from the sidebar.</p>
                        <ul class="space-y-1 text-sm text-zinc-400 list-disc list-inside">
                            <li>Filter by date range and optionally by a specific cashier.</li>
                            <li>View per-cashier stats: total shifts, total sales, total orders, average order value, average shift duration, and cash variance.</li>
                            <li>Select a specific cashier to view a full breakdown of all their individual shifts.</li>
                            <li>Cash variance highlights cashiers with consistent over/short discrepancies.</li>
                        </ul>
                    </div>
                </div>
            </flux:card>
            @endif

            {{-- Settings --}}
            @if($activeSection === 'settings')
            <flux:card class="p-6 md:p-8">
                <flux:heading size="xl" level="2" class="mb-1">Settings</flux:heading>
                <flux:text class="text-zinc-400 mb-8">Configure your business, users, and system preferences.</flux:text>

                <div class="space-y-8">
                    <div>
                        <h3 class="text-base font-semibold text-zinc-100 mb-3">Receipt Settings</h3>
                        <p class="text-sm text-zinc-400">Go to <span class="font-medium text-zinc-100">Settings &rarr; Receipt</span> to customise your printed receipts — business name, address, footer message, and whether to show tax breakdowns.</p>
                    </div>

                    <div>
                        <h3 class="text-base font-semibold text-zinc-100 mb-3">Loyalty Settings</h3>
                        <p class="text-sm text-zinc-400">Go to <span class="font-medium text-zinc-100">Settings &rarr; Loyalty</span> to configure the points earning rate (e.g. 1 point per $1 spent), the redemption value (e.g. 100 points = $1 off), and the minimum points required before redemption is allowed.</p>
                    </div>

                    <div>
                        <h3 class="text-base font-semibold text-zinc-100 mb-3">Quick Notes</h3>
                        <p class="text-sm text-zinc-400">Go to <span class="font-medium text-zinc-100">Settings &rarr; Quick Notes</span> to define preset order notes (e.g. "No onions", "Extra spicy") that staff can select quickly during order taking without typing.</p>
                    </div>

                    <div>
                        <h3 class="text-base font-semibold text-zinc-100 mb-3">Roles & Permissions</h3>
                        <p class="text-sm text-zinc-400 mb-2">Go to <span class="font-medium text-zinc-100">Settings &rarr; Roles</span> to manage what each role can access. You can create custom roles and assign granular permissions.</p>
                    </div>

                    <div>
                        <h3 class="text-base font-semibold text-zinc-100 mb-3">User Management</h3>
                        <p class="text-sm text-zinc-400">Go to <span class="font-medium text-zinc-100">Settings &rarr; Users</span> to invite new staff members, assign roles, and deactivate accounts. Each user logs in with their own credentials tied to your business account.</p>
                    </div>
                </div>
            </flux:card>
            @endif

        </div>
    </div>

</div>
