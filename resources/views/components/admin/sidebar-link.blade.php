@props(['label', 'icon', 'active' => false, 'available' => false, 'href' => null])

@if ($available)
    <a href="{{ $href ?? route('admin.index') }}" @class([
        'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition',
        'bg-white/15 text-white shadow-sm' => $active,
        'text-blue-100 hover:bg-white/10 hover:text-white' => ! $active,
    ])>
        <x-admin.icon :name="$icon" class="size-5 shrink-0" />
        <span>{{ $label }}</span>
    </a>
@else
    <span class="flex cursor-not-allowed items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-blue-200/55" aria-disabled="true" title="Bientôt disponible">
        <x-admin.icon :name="$icon" class="size-5 shrink-0" />
        <span>{{ $label }}</span>
        <span class="ml-auto rounded-md bg-white/10 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-blue-100/70">Bientôt</span>
    </span>
@endif
