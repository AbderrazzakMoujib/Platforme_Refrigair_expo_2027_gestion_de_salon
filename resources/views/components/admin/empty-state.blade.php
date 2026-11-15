@props(['title', 'description', 'icon' => 'inbox'])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50/70 px-6 py-9 text-center']) }}>
    <span class="rounded-xl bg-white p-3 text-slate-400 shadow-sm ring-1 ring-slate-200">
        <x-admin.icon :name="$icon" class="size-5" />
    </span>
    <p class="mt-3 text-sm font-semibold text-slate-700">{{ $title }}</p>
    <p class="mt-1 max-w-sm text-xs leading-5 text-slate-500">{{ $description }}</p>
</div>
