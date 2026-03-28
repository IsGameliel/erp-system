@php
    $user = auth()->user();
    $canUseApplication = $user?->canUseApplication() ?? false;
    $isOwner = $user?->isSuperAdmin() ?? false;

    $links = $isOwner
        ? [
            ['label' => 'Dashboard', 'route' => 'owner.dashboard', 'roles' => [\App\Models\User::ROLE_SUPER_ADMIN], 'requires_access' => false],
            ['label' => 'Organizations', 'route' => 'owner.organizations.index', 'roles' => [\App\Models\User::ROLE_SUPER_ADMIN], 'requires_access' => false],
            ['label' => 'Users', 'route' => 'owner.users.index', 'roles' => [\App\Models\User::ROLE_SUPER_ADMIN], 'requires_access' => false],
            ['label' => 'Plans', 'route' => 'owner.subscription-plans.index', 'roles' => [\App\Models\User::ROLE_SUPER_ADMIN], 'requires_access' => false],
            ['label' => 'Payments', 'route' => 'owner.subscriptions.index', 'roles' => [\App\Models\User::ROLE_SUPER_ADMIN], 'requires_access' => false],
        ]
        : [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'roles' => \App\Models\User::ROLES, 'requires_access' => true],
            ['label' => 'Subscription', 'route' => 'subscriptions.status', 'roles' => \App\Models\User::ROLES, 'requires_access' => false],
            ['label' => 'Users', 'route' => 'users.index', 'roles' => [\App\Models\User::ROLE_ADMIN]],
            ['label' => 'Stores', 'route' => 'stores.index', 'roles' => [\App\Models\User::ROLE_ADMIN]],
            ['label' => 'Product Categories', 'route' => 'product-categories.index', 'roles' => [\App\Models\User::ROLE_ADMIN]],
            ['label' => 'Admin History', 'route' => 'activity-logs.index', 'roles' => [\App\Models\User::ROLE_ADMIN]],
            ['label' => 'Customers', 'route' => 'customers.index', 'roles' => [\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_SALES_OFFICER]],
            ['label' => 'Sales Orders', 'route' => 'sales-orders.index', 'roles' => [\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_SALES_OFFICER]],
            ['label' => 'Vendors', 'route' => 'vendors.index', 'roles' => [\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_PROCUREMENT_OFFICER]],
            ['label' => 'Purchase Orders', 'route' => 'purchase-orders.index', 'roles' => [\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_PROCUREMENT_OFFICER]],
            ['label' => 'Products', 'route' => 'products.index', 'roles' => [\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_SALES_OFFICER, \App\Models\User::ROLE_PROCUREMENT_OFFICER]],
            ['label' => 'Sales Reports', 'route' => 'reports.sales', 'roles' => [\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_SALES_OFFICER]],
            ['label' => 'Procurement Reports', 'route' => 'reports.procurement', 'roles' => [\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_PROCUREMENT_OFFICER]],
        ];
@endphp

<aside class="hidden border-r border-slate-200/80 bg-[linear-gradient(180deg,#0f172a_0%,#111827_18%,#1e293b_100%)] text-white lg:block">
    <div class="sticky top-0 flex h-screen flex-col px-6 py-8">
        <div class="mb-8">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-cyan-300">{{ $applicationBrandName }}</p>
            <h1 class="mt-3 text-2xl font-semibold tracking-tight">{{ $isOwner ? 'Platform Owner' : 'Operations Core' }}</h1>
            <p class="mt-2 text-sm text-slate-300">{{ $isOwner ? 'Organizations, plans, subscriptions, payments, and user access.' : 'Sales, procurement, customers, vendors, orders, reports.' }}</p>
        </div>

        <nav class="min-h-0 flex-1 space-y-2 overflow-y-auto pr-2">
            @foreach ($links as $link)
                @continue(! $user || ! $user->hasAnyRole($link['roles']))
                @continue(($link['requires_access'] ?? true) && ! $canUseApplication)

                <a
                    href="{{ route($link['route']) }}"
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

        <div class="mt-6 rounded-3xl border border-white/10 bg-white/5 p-4 text-sm text-slate-300">
            <p class="font-semibold text-white">{{ $user?->name }}</p>
            <p class="mt-1 capitalize">{{ str_replace('_', ' ', $user?->role ?? 'guest') }}</p>
        </div>
    </div>
</aside>
