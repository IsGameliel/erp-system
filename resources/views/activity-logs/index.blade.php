<x-app-layout>
    @php
        $formatValue = function ($value) use (&$formatValue) {
            if (is_array($value)) {
                return collect($value)
                    ->map(function ($item, $key) use (&$formatValue) {
                        if (is_array($item)) {
                            return collect($item)
                                ->map(fn ($nested, $nestedKey) => str_replace('_', ' ', ucfirst((string) $nestedKey)).': '.$formatValue($nested))
                                ->implode(', ');
                        }

                        if (is_string($key)) {
                            return str_replace('_', ' ', ucfirst((string) $key)).': '.$formatValue($item);
                        }

                        return $formatValue($item);
                    })
                    ->implode(' | ');
            }

            if (is_bool($value)) {
                return $value ? 'Yes' : 'No';
            }

            return filled($value) ? (string) $value : '—';
        };
    @endphp
    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.25em] text-cyan-700">Monitoring</p>
                <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Staff activity history</h2>
            </div>
        </div>
    </x-slot>

    <form class="page-panel grid gap-4 lg:grid-cols-4">
        <input class="form-input" name="search" placeholder="Search description or module" value="{{ request('search') }}">
        <select class="form-select" name="action">
            <option value="">All actions</option>
            @foreach ($actions as $action)
                <option value="{{ $action }}" @selected(request('action') === $action)>{{ ucfirst($action) }}</option>
            @endforeach
        </select>
        <select class="form-select" name="module">
            <option value="">All modules</option>
            @foreach ($modules as $module)
                <option value="{{ $module }}" @selected(request('module') === $module)>{{ str_replace('_', ' ', ucfirst($module)) }}</option>
            @endforeach
        </select>
        <button class="btn-secondary justify-center" type="submit">Filter</button>
    </form>

    <x-table-wrapper>
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-6 py-3 font-medium">Staff</th>
                    <th class="px-6 py-3 font-medium">Action</th>
                    <th class="px-6 py-3 font-medium">Module</th>
                    <th class="px-6 py-3 font-medium">Description</th>
                    <th class="px-6 py-3 font-medium">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($activityLogs as $activity)
                    <tr>
                        <td class="px-6 py-4">
                            <p class="font-medium text-slate-900">{{ $activity->user?->name ?? 'Deleted user' }}</p>
                            <p class="text-xs text-slate-500">{{ str_replace('_', ' ', $activity->user_role) }}</p>
                        </td>
                        <td class="px-6 py-4 capitalize">{{ $activity->action }}</td>
                        <td class="px-6 py-4">{{ str_replace('_', ' ', ucfirst($activity->module)) }}</td>
                        <td class="px-6 py-4 text-slate-700">
                            <p>{{ $activity->description }}</p>

                            @if (($activity->old_values ?? []) !== [] || ($activity->new_values ?? []) !== [])
                                <div class="mt-3 space-y-2 rounded-2xl border border-slate-200 bg-slate-50 p-3 text-xs">
                                    @foreach (array_unique([...array_keys($activity->old_values ?? []), ...array_keys($activity->new_values ?? [])]) as $field)
                                        <div class="grid gap-2 border-b border-slate-200 pb-2 last:border-b-0 last:pb-0 lg:grid-cols-3">
                                            <p class="font-semibold uppercase tracking-wide text-slate-500">{{ str_replace('_', ' ', $field) }}</p>
                                            <div>
                                                <p class="mb-1 font-medium text-slate-500">Before</p>
                                                <p>{{ $formatValue($activity->old_values[$field] ?? null) }}</p>
                                            </div>
                                            <div>
                                                <p class="mb-1 font-medium text-slate-500">After</p>
                                                <p>{{ $formatValue($activity->new_values[$field] ?? null) }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-slate-600">{{ $activity->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-6 text-center text-slate-500">No staff activity logs found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-table-wrapper>

    {{ $activityLogs->links() }}
</x-app-layout>
