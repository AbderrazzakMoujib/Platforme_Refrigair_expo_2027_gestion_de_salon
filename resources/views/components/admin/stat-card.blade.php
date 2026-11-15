@props(['label', 'value', 'icon', 'description'])

<article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
            <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $value }}</p>
        </div>
        <span class="rounded-xl bg-blue-50 p-3 text-[#2563EB]">
            <x-admin.icon :name="$icon" class="size-5" />
        </span>
    </div>
    <p class="mt-4 text-xs text-slate-500">{{ $description }}</p>
</article>
