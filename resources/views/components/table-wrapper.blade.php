<div {{ $attributes->class('overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm shadow-slate-200/60') }}>
    <div class="overflow-x-auto">
        {{ $slot }}
    </div>
</div>
