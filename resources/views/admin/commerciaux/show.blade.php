
<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">

<div class="space-y-6">

    <a href="{{ route('admin.commerciaux.index') }}"
       class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:underline">
        ← Retour aux commerciaux
    </a>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="h-24 bg-gradient-to-r from-[#0F2347] to-[#2563EB]"></div>

        <div class="px-6 pb-7 sm:px-8">

            <div class="-mt-12 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">

                <div class="flex items-end gap-4">

                    @if ($commercial->profile_photo_url)
                        <img src="{{ $commercial->profile_photo_url }}"
                             alt="Photo de {{ $commercial->name }}"
                             class="size-24 shrink-0 rounded-2xl border-4 border-white bg-white object-cover shadow-md">
                    @else
                        <span class="grid size-24 shrink-0 place-items-center rounded-2xl border-4 border-white bg-blue-100 text-3xl font-bold text-blue-700 shadow-md">
                            {{ $commercial->initials }}
                        </span>
                    @endif

                    <div class="pb-1">
                        <h1 class="text-2xl font-extrabold text-[#0F2347]">
                            {{ $commercial->name }}
                        </h1>

                        <p class="mt-1 text-sm text-slate-500">
                            {{ $commercial->email }}
                        </p>
                    </div>

                </div>

                <a href="{{ route('admin.commerciaux.edit', $commercial) }}"
                   class="rounded-xl bg-[#2563EB] px-5 py-3 text-center text-sm font-semibold text-white hover:bg-blue-700">
                    Modifier le profil
                </a>

            </div>

            <div class="mt-8 grid gap-5 border-t border-slate-100 pt-7 sm:grid-cols-2 lg:grid-cols-3">

                <div>
                    <p class="text-xs font-semibold uppercase text-slate-400">
                        Téléphone
                    </p>
                    <p class="mt-2 font-semibold text-slate-800">
                        {{ $commercial->phone ?: '—' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase text-slate-400">
                        Statut
                    </p>

                    <span @class([
                        'mt-2 inline-flex rounded-full px-3 py-1.5 text-xs font-bold',
                        'bg-emerald-50 text-emerald-700' => $commercial->is_active,
                        'bg-red-50 text-red-700' => !$commercial->is_active,
                    ])>
                        {{ $commercial->is_active ? 'Actif' : 'Inactif' }}
                    </span>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase text-slate-400">
                        Exposants gérés
                    </p>
                    <p class="mt-2 text-xl font-bold text-[#0F2347]">
                        {{ $commercial->exhibitors_count }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase text-slate-400">
                        Date de création
                    </p>
                    <p class="mt-2 font-semibold text-slate-800">
                        {{ $commercial->created_at?->format('d/m/Y à H:i') }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase text-slate-400">
                        Accès plateforme
                    </p>
                    <p class="mt-2 font-semibold text-slate-800">
                        {{ $accessStatus }}
                    </p>
                </div>

            </div>

        </div>
    </section>

    @error('invitation')
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            {{ $message }}
        </div>
    @enderror

</div>

</x-admin.layout>
