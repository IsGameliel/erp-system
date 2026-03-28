<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.25em] text-cyan-700">Dashboard</p>
                <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Operations overview</h2>
            </div>
            <p class="text-sm text-slate-500">Monitor sales, procurement, customers, vendors, and recent activity.</p>
        </div>
    </x-slot>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <x-stat-card title="Total Customers" :value="number_format($totalCustomers)" />
        <x-stat-card title="Total Vendors" :value="number_format($totalVendors)" />
        <x-stat-card title="Sales Orders" :value="number_format($totalSalesOrders)" />
        <x-stat-card title="Purchase Orders" :value="number_format($totalPurchaseOrders)" />
        <x-stat-card title="Sales Revenue" :value="'₦'.number_format($totalSalesRevenue, 2)" />
        <x-stat-card title="Procurement Cost" :value="'₦'.number_format($totalProcurementCost, 2)" />
        @if (auth()->user()->isSuperAdmin())
            <x-stat-card title="Active User Access" :value="number_format($activeUsersCount)" />
            <x-stat-card title="Inactive User Access" :value="number_format($inactiveUsersCount)" />
            <x-stat-card title="Expired Users" :value="number_format($expiredUsersCount)" />
            <x-stat-card title="Pending Payments" :value="number_format($pendingPaymentCount)" />
            <x-stat-card title="Approved Payments" :value="number_format($approvedPaymentCount)" />
            <x-stat-card title="Subscription Revenue" :value="'$'.number_format($subscriptionRevenue, 2)" />
            <x-stat-card title="Available Plans" :value="number_format($subscriptionPlansCount)" />
        @endif
    </div>

    @if (auth()->user()->isSuperAdmin())
        <div class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
            <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm shadow-slate-200/60">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-950">Subscription payments</h3>
                        <p class="mt-1 text-sm text-slate-500">Track recent payment submissions, approvals, and active access windows.</p>
                    </div>
                    <a href="{{ route('subscriptions.index') }}" class="text-sm font-medium text-cyan-700 hover:text-cyan-900">Manage payments</a>
                </div>

                <div class="mt-4 space-y-3">
                    @forelse ($recentSubscriptionPayments as $payment)
                        <div class="flex items-start justify-between gap-4 rounded-2xl bg-slate-50 px-4 py-4">
                            <div>
                                <p class="font-medium text-slate-900">{{ $payment->user?->name ?? 'Deleted user' }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $payment->plan?->name ?? 'Custom plan' }} • {{ ucfirst($payment->status) }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $payment->payment_reference ?: 'No reference' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-slate-900">${{ number_format((float) $payment->amount, 2) }}</p>
                                @if ($payment->ends_at)
                                    <p class="mt-1 text-xs text-slate-500">Until {{ $payment->ends_at->format('M d, Y') }}</p>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No subscription payments yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm shadow-slate-200/60">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-950">Owner controls</h3>
                        <p class="mt-1 text-sm text-slate-500">Manage plans, subscriber access, and payment approvals.</p>
                    </div>
                </div>

                <div class="mt-4 space-y-3">
                    <a href="{{ route('users.index') }}" class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-4 text-sm text-slate-700 transition hover:bg-slate-100">
                        <span>User accounts and access</span>
                        <span class="font-semibold text-cyan-700">Open</span>
                    </a>
                    <a href="{{ route('subscription-plans.index') }}" class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-4 text-sm text-slate-700 transition hover:bg-slate-100">
                        <span>Subscription plans</span>
                        <span class="font-semibold text-cyan-700">Open</span>
                    </a>
                    <a href="{{ route('subscriptions.index') }}" class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-4 text-sm text-slate-700 transition hover:bg-slate-100">
                        <span>Payment records</span>
                        <span class="font-semibold text-cyan-700">Open</span>
                    </a>
                </div>
            </div>
        </div>
    @endif

    @if (auth()->user()->hasRole(\App\Models\User::ROLE_SALES_OFFICER))
        <div class="page-panel flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-xl font-semibold text-slate-950">Sales checkout</h3>
                <p class="mt-2 text-sm text-slate-500">Create a customer if needed, add items, checkout, and print the receipt immediately.</p>
                <p class="mt-2 text-sm text-slate-600">Current store: {{ $userStore?->name ?? 'No store assigned' }}</p>
            </div>
            <a href="{{ route('sales-orders.create') }}" class="btn-primary">Start checkout</a>
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm shadow-slate-200/60">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-slate-950">Monthly sales</h3>
                <span class="text-sm text-slate-500">Chart.js</span>
            </div>
            <canvas id="salesChart" class="mt-6 h-72"></canvas>
        </div>

        <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm shadow-slate-200/60">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-slate-950">Monthly procurement</h3>
                <span class="text-sm text-slate-500">Chart.js</span>
            </div>
            <canvas id="procurementChart" class="mt-6 h-72"></canvas>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <x-table-wrapper>
            <div class="border-b border-slate-200 px-6 py-4">
                <h3 class="text-lg font-semibold text-slate-950">Recent sales orders</h3>
            </div>
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-6 py-3 font-medium">Order</th>
                        <th class="px-6 py-3 font-medium">Customer</th>
                        <th class="px-6 py-3 font-medium">Status</th>
                        <th class="px-6 py-3 font-medium">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($recentSalesOrders as $order)
                        <tr>
                            <td class="px-6 py-4 font-medium text-slate-900">{{ $order->order_number }}</td>
                            <td class="px-6 py-4">{{ $order->customer?->full_name }}</td>
                            <td class="px-6 py-4"><x-status-badge :status="$order->status" /></td>
                            <td class="px-6 py-4">₦{{ number_format($order->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-4 text-slate-500">No sales orders yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-table-wrapper>

        <x-table-wrapper>
            <div class="border-b border-slate-200 px-6 py-4">
                <h3 class="text-lg font-semibold text-slate-950">Recent purchase orders</h3>
            </div>
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-6 py-3 font-medium">PO</th>
                        <th class="px-6 py-3 font-medium">Vendor</th>
                        <th class="px-6 py-3 font-medium">Status</th>
                        <th class="px-6 py-3 font-medium">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($recentPurchaseOrders as $order)
                        <tr>
                            <td class="px-6 py-4 font-medium text-slate-900">{{ $order->po_number }}</td>
                            <td class="px-6 py-4">{{ $order->vendor?->company_name }}</td>
                            <td class="px-6 py-4"><x-status-badge :status="$order->status" /></td>
                            <td class="px-6 py-4">₦{{ number_format($order->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-4 text-slate-500">No purchase orders yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-table-wrapper>
    </div>

    @if (auth()->user()->hasAnyRole([\App\Models\User::ROLE_SUPER_ADMIN, \App\Models\User::ROLE_ADMIN]))
        <div class="grid gap-6 xl:grid-cols-2">
            @if (auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN))
                <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm shadow-slate-200/60">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-950">Catalog controls</h3>
                            <p class="mt-1 text-sm text-slate-500">Create product categories and organize products under them.</p>
                        </div>
                        <span class="rounded-full bg-cyan-50 px-3 py-1 text-sm font-semibold text-cyan-700">{{ number_format($totalProductCategories) }} categories</span>
                    </div>

                    <div class="mt-4 space-y-3">
                        <a href="{{ route('product-categories.create') }}" class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-4 text-sm text-slate-700 transition hover:bg-slate-100">
                            <span>Add product category</span>
                            <span class="font-semibold text-cyan-700">Open</span>
                        </a>
                        @if (! auth()->user()->hasRole(\App\Models\User::ROLE_SALES_OFFICER))
                            <a href="{{ route('products.create') }}" class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-4 text-sm text-slate-700 transition hover:bg-slate-100">
                                <span>Add product to category</span>
                                <span class="font-semibold text-cyan-700">Open</span>
                            </a>
                        @endif
                    </div>

                    <div class="mt-6 space-y-3">
                        @if (! $productCategoriesAvailable)
                            <p class="text-sm text-amber-700">Product categories will appear here after the latest tenant database migration is applied.</p>
                        @else
                            @forelse ($recentProductCategories as $category)
                                <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-3">
                                    <div>
                                        <p class="font-medium text-slate-900">{{ $category->name }}</p>
                                        <p class="text-sm text-slate-500">{{ $category->products_count }} products</p>
                                    </div>
                                    <a href="{{ route('product-categories.edit', $category) }}" class="text-sm font-semibold text-cyan-700 hover:text-cyan-900">Edit</a>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500">No product categories yet.</p>
                            @endforelse
                        @endif
                    </div>
                </div>
            @endif

            <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm shadow-slate-200/60">
                <h3 class="text-lg font-semibold text-slate-950">Store sales overview</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($storeSalesSummary as $store)
                        <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                            <div>
                                <p class="font-medium text-slate-900">{{ $store->name }}</p>
                                <p class="text-sm text-slate-500">{{ $store->sales_orders_count }} orders</p>
                            </div>
                            <p class="font-semibold text-slate-900">₦{{ number_format($store->sales_total ?? 0, 2) }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No store sales recorded yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm shadow-slate-200/60">
                <h3 class="text-lg font-semibold text-slate-950">Inventory snapshot</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($inventoryProducts as $product)
                        <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                            <div>
                                <p class="font-medium text-slate-900">{{ $product->name }}</p>
                                <p class="text-sm text-slate-500">{{ $product->sku }}{{ $product->category ? ' • '.$product->category->name : '' }}</p>
                            </div>
                            <p class="font-semibold text-slate-900">{{ number_format($product->stock_quantity) }} in stock</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No products available.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm shadow-slate-200/60">
            <h3 class="text-lg font-semibold text-slate-950">Top customers</h3>
            <div class="mt-4 space-y-3">
                @forelse ($topCustomers as $customer)
                    <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                        <div>
                            <p class="font-medium text-slate-900">{{ $customer->full_name }}</p>
                            <p class="text-sm text-slate-500">{{ $customer->sales_orders_count }} orders</p>
                        </div>
                        <p class="font-semibold text-slate-900">₦{{ number_format($customer->sales_total ?? 0, 2) }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No customer data available.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm shadow-slate-200/60">
            <h3 class="text-lg font-semibold text-slate-950">Top vendors</h3>
            <div class="mt-4 space-y-3">
                @forelse ($topVendors as $vendor)
                    <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                        <div>
                            <p class="font-medium text-slate-900">{{ $vendor->company_name }}</p>
                            <p class="text-sm text-slate-500">{{ $vendor->purchase_orders_count }} orders</p>
                        </div>
                        <p class="font-semibold text-slate-900">₦{{ number_format($vendor->purchase_total ?? 0, 2) }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No vendor data available.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm shadow-slate-200/60">
            <div class="flex items-center justify-between gap-4">
                <h3 class="text-lg font-semibold text-slate-950">Recent activity</h3>
                @if (auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN))
                    <a href="{{ route('activity-logs.index') }}" class="text-sm font-medium text-cyan-700 hover:text-cyan-900">View full history</a>
                @endif
            </div>
            <div class="mt-4 space-y-4">
                @forelse ($recentActivities as $activity)
                    <div class="border-l-2 border-cyan-500 pl-4">
                        <p class="text-sm font-medium text-slate-900">{{ $activity->description }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $activity->user?->name ?? 'System' }} • {{ $activity->created_at->diffForHumans() }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No activity logged yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <script>
        new Chart(document.getElementById('salesChart'), {
            type: 'line',
            data: {
                labels: @json($salesChartLabels),
                datasets: [{
                    label: 'Sales revenue',
                    data: @json($salesChartValues),
                    borderColor: '#0891b2',
                    backgroundColor: 'rgba(8, 145, 178, 0.12)',
                    fill: true,
                    tension: 0.35,
                }]
            },
        });

        new Chart(document.getElementById('procurementChart'), {
            type: 'bar',
            data: {
                labels: @json($procurementChartLabels),
                datasets: [{
                    label: 'Procurement cost',
                    data: @json($procurementChartValues),
                    backgroundColor: '#0f766e',
                    borderRadius: 12,
                }]
            },
        });
    </script>
</x-app-layout>
