@props(['status'])

@php
    $palette = match ($status) {
        'active', 'delivered', 'received', 'confirmed', 'approved' => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
        'pending', 'draft', 'ordered', 'shipped' => 'bg-amber-100 text-amber-800 ring-amber-200',
        'inactive', 'cancelled' => 'bg-rose-100 text-rose-800 ring-rose-200',
        default => 'bg-slate-100 text-slate-700 ring-slate-200',
    };
@endphp

<span {{ $attributes->class("inline-flex rounded-full px-3 py-1 text-xs font-semibold capitalize ring-1 ring-inset {$palette}") }}>
    {{ str_replace('_', ' ', $status) }}
</span>
