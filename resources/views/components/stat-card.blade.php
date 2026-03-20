@props(['title', 'value', 'hint' => null])

<div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-200/60">
    <p class="text-sm font-medium text-slate-500">{{ $title }}</p>
    <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">{{ $value }}</p>
    @if ($hint)
        <p class="mt-2 text-sm text-slate-500">{{ $hint }}</p>
    @endif
</div>
