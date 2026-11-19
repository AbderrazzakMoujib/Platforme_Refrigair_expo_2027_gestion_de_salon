@props([
    'label',
    'value',
    'icon',
    'tone' => 'blue',
    'trend' => null,
    'trendTone' => 'emerald',
    'description' => null,
])

@php
    $tones = [
        'blue' => ['card' => 'from-blue-50/80', 'icon' => 'bg-blue-100 text-blue-600'],
        'amber' => ['card' => 'from-amber-50/80', 'icon' => 'bg-amber-100 text-amber-600'],
        'emerald' => ['card' => 'from-emerald-50/80', 'icon' => 'bg-emerald-100 text-emerald-600'],
        'violet' => ['card' => 'from-violet-50/80', 'icon' => 'bg-violet-100 text-violet-600'],
    ];
    $trendTones = [
        'emerald' => 'text-emerald-700',
        'red' => 'text-red-600',
        'violet' => 'text-violet-700',
        'slate' => 'text-slate-500',
    ];
    $style = $tones[$tone] ?? $tones['blue'];
@endphp

<article {{ $attributes->merge(['class' => "rounded-2xl bg-linear-to-br {$style['card']} to-white p-5 shadow-sm ring-1 ring-slate-200/70"]) }}>
    <div class="flex items-start gap-4">
        <span class="grid size-12 shrink-0 place-items-center rounded-full {{ $style['icon'] }}" aria-hidden="true">
            <x-admin.icon :name="$icon" class="size-6" />
        </span>
        <div class="min-w-0">
            <p class="text-sm font-semibold text-slate-700">{{ $label }}</p>
            <p class="mt-1 text-3xl font-bold tracking-tight text-slate-900">{{ $value }}</p>
            @if ($trend)
                <p class="mt-1.5 text-sm font-semibold {{ $trendTones[$trendTone] ?? $trendTones['emerald'] }}">{{ $trend }}</p>
            @endif
            @if ($description)
                <p class="mt-1 text-xs text-slate-500">{{ $description }}</p>
            @endif
        </div>
    </div>
</article>
