@php
    $user = auth()->user();

    $links = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'roles' => \App\Models\User::ROLES],
        ['label' => 'Users', 'route' => 'users.index', 'roles' => [\App\Models\User::ROLE_ADMIN]],
        ['label' => 'Stores', 'route' => 'stores.index', 'roles' => [\App\Models\User::ROLE_ADMIN]],
        ['label' => 'Customers', 'route' => 'customers.index', 'roles' => [\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_SALES_OFFICER]],
        ['label' => 'Sales Orders', 'route' => 'sales-orders.index', 'roles' => [\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_SALES_OFFICER]],
        ['label' => 'Vendors', 'route' => 'vendors.index', 'roles' => [\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_PROCUREMENT_OFFICER]],
        ['label' => 'Purchase Orders', 'route' => 'purchase-orders.index', 'roles' => [\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_PROCUREMENT_OFFICER]],
        ['label' => 'Products', 'route' => 'products.index', 'roles' => \App\Models\User::ROLES],
        ['label' => 'Sales Reports', 'route' => 'reports.sales', 'roles' => [\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_SALES_OFFICER]],
        ['label' => 'Procurement Reports', 'route' => 'reports.procurement', 'roles' => [\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_PROCUREMENT_OFFICER]],
    ];
@endphp

<div x-data="{ mobileMenuOpen: false }" @keydown.escape.window="mobileMenuOpen = false" class="relative border-b border-slate-200/80 bg-white/90 backdrop-blur">
    <div class="flex items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-3">
            <button
                type="button"
                class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 text-slate-700 transition hover:border-cyan-200 hover:text-cyan-700 lg:hidden"
                @click="mobileMenuOpen = true"
                aria-label="Open dashboard menu"
            >
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M3.5 5.5h13M3.5 10h13M3.5 14.5h13" stroke-linecap="round" />
                </svg>
            </button>

            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-cyan-700">ERP System</p>
                <p class="mt-1 text-sm text-slate-500">Operational dashboard for sales and procurement.</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('profile.edit') }}" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-cyan-200 hover:text-cyan-700">
                Profile
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700">
                    Logout
                </button>
            </form>
        </div>
    </div>

    <div
        x-cloak
        x-show="mobileMenuOpen"
        x-transition.opacity
        style="display: none;"
        class="fixed inset-0 z-40 bg-slate-950/50 lg:hidden"
        @click="mobileMenuOpen = false"
    ></div>

    <aside
        x-cloak
        x-show="mobileMenuOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        style="display: none;"
        class="fixed inset-y-0 left-0 z-50 w-[280px] overflow-y-auto border-r border-slate-200/80 bg-[linear-gradient(180deg,#0f172a_0%,#111827_18%,#1e293b_100%)] text-white lg:hidden"
        @click.outside="mobileMenuOpen = false"
    >
        <div class="flex min-h-full flex-col px-6 py-8">
            <div class="mb-8 flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-cyan-300">ERP Suite</p>
                    <h1 class="mt-3 text-2xl font-semibold tracking-tight">Operations Core</h1>
                    <p class="mt-2 text-sm text-slate-300">Sales, procurement, customers, vendors, orders, reports.</p>
                </div>

                <button
                    type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/10 text-slate-200 transition hover:bg-white/10"
                    @click="mobileMenuOpen = false"
                    aria-label="Close dashboard menu"
                >
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M5 5l10 10M15 5L5 15" stroke-linecap="round" />
                    </svg>
                </button>
            </div>

            <nav class="space-y-2">
                @foreach ($links as $link)
                    @continue(! $user || ! $user->hasAnyRole($link['roles']))

                    <a
                        href="{{ route($link['route']) }}"
                        @click="mobileMenuOpen = false"
                        @class([
                            'flex items-center rounded-2xl px-4 py-3 text-sm font-medium transition',
                            'bg-cyan-400/15 text-white shadow-lg shadow-cyan-950/20 ring-1 ring-cyan-300/20' => request()->routeIs($link['route']),
                            'text-slate-300 hover:bg-white/5 hover:text-white' => ! request()->routeIs($link['route']),
                        ])
                    >
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="mt-auto rounded-3xl border border-white/10 bg-white/5 p-4 text-sm text-slate-300">
                <p class="font-semibold text-white">{{ $user?->name }}</p>
                <p class="mt-1 capitalize">{{ str_replace('_', ' ', $user?->role ?? 'guest') }}</p>
            </div>
        </div>
    </aside>
</div>
