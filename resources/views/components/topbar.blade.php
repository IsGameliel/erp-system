<div class="border-b border-slate-200/80 bg-white/90 backdrop-blur">
    <div class="flex items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-cyan-700">ERP System</p>
            <p class="mt-1 text-sm text-slate-500">Operational dashboard for sales and procurement.</p>
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
</div>
