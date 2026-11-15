
<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">

    <a
        href="{{ route('admin.exposants.index') }}"
        class="text-sm font-semibold text-blue-600 hover:underline"
    >
        ← Retour aux exposants
    </a>

    <div class="mt-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <p class="text-sm font-semibold text-blue-600">
                Fiche exposant
            </p>

            <h1 class="mt-2 text-3xl font-bold text-slate-900">
                {{ $exhibitor->company_name }}
            </h1>

            <p class="mt-2 text-sm text-slate-500">
                {{ $exhibitor->contact_name }} ·
                Stand {{ $exhibitor->stand_number ?: 'Non attribué' }}
            </p>
        </div>

        <span class="w-fit rounded-xl bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700">
            Consultation administrateur
        </span>
    </div>

    @php
        $quota = (int) $exhibitor->authorized_badges;
        $registered = (int) $exhibitor->badges_count;
        $remaining = max(0, $quota - $registered);
        $progress = $quota > 0
            ? min(100, round($registered / $quota * 100))
            : 0;
    @endphp

    <!-- Badges -->
    <section class="mt-7 grid gap-4 sm:grid-cols-3">
        <x-admin.stat-card
            label="Badges autorisés"
            :value="$quota"
            icon="badge"
            description="Quota attribué."
        />

        <x-admin.stat-card
            label="Badges enregistrés"
            :value="$registered"
            icon="badge"
            description="Membres inscrits."
        />

        <x-admin.stat-card
            label="Badges restants"
            :value="$remaining"
            icon="badge"
            description="Places disponibles."
        />
    </section>

    <section class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div class="flex justify-between gap-3">
            <h2 class="font-bold text-slate-900">
                Progression des badges
            </h2>
            <span class="font-bold text-blue-700">
                {{ $progress }}%
            </span>
        </div>

        <div class="mt-4 h-3 overflow-hidden rounded-full bg-slate-100">
            <div
                class="h-full rounded-full bg-blue-600"
                style="width: {{ $progress }}%">
            </div>
        </div>
    </section>

    <!-- Entreprise et Commercial -->
    <section class="mt-6 grid gap-6 lg:grid-cols-2">

        <article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <h2 class="text-lg font-bold">
                Informations de l'entreprise
            </h2>

            <dl class="mt-6 grid gap-5 sm:grid-cols-2">
                <div>
                    <dt class="text-xs text-slate-500">Entreprise</dt>
                    <dd class="mt-1 font-semibold">{{ $exhibitor->company_name }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-500">Responsable</dt>
                    <dd class="mt-1 font-semibold">{{ $exhibitor->contact_name }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-500">Email</dt>
                    <dd class="mt-1 break-all font-medium">{{ $exhibitor->email }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-500">Téléphone</dt>
                    <dd class="mt-1 font-medium">{{ $exhibitor->phone ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-500">Stand</dt>
                    <dd class="mt-1 font-semibold">{{ $exhibitor->stand_number ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-500">Surface</dt>
                    <dd class="mt-1 font-semibold">
                        {{ $exhibitor->area_sqm }} m²
                    </dd>
                </div>
            </dl>
        </article>

        <article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <h2 class="text-lg font-bold">
                Commercial & accès plateforme
            </h2>

            <dl class="mt-6 space-y-5">
                <div>
                    <dt class="text-xs text-slate-500">Commercial responsable</dt>
                    <dd class="mt-1 font-semibold">
                        {{ $exhibitor->commercial?->name ?? 'Non attribué' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs text-slate-500">Email commercial</dt>
                    <dd class="mt-1 break-all text-sm font-medium">
                        {{ $exhibitor->commercial?->email ?? '—' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs text-slate-500">Téléphone commercial</dt>
                    <dd class="mt-1 text-sm font-medium">
                        {{ $exhibitor->commercial?->phone ?? '—' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs text-slate-500">Statut du compte exposant</dt>
                    <dd class="mt-2">
                        @if (!$exhibitor->user)
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                Compte non créé
                            </span>
                        @elseif ($exhibitor->user->is_active)
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                Compte actif
                            </span>
                        @else
                            <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-700">
                                Compte désactivé
                            </span>
                        @endif
                    </dd>
                </div>
            </dl>

            @if ($exhibitor->commercial)
                <a
                    href="{{ route('admin.commerciaux.show', $exhibitor->commercial) }}"
                    class="mt-6 inline-block text-sm font-semibold text-blue-700 hover:underline"
                >
                    Voir la fiche du commercial
                </a>
            @endif
        </article>

    </section>

    <!-- Liste des Badges -->
    <section class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">

        <div class="border-b border-slate-100 px-6 py-5">
            <h2 class="text-lg font-bold">
                Badges de l'entreprise
            </h2>
            <p class="mt-1 text-sm text-slate-500">
                Liste des membres enregistrés par cet exposant.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[650px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-5 py-4">Nom complet</th>
                        <th class="px-5 py-4">Fonction</th>
                        <th class="px-5 py-4">Email</th>
                        <th class="px-5 py-4">Téléphone</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($badges as $badge)
                        <tr>
                            <td class="px-5 py-4 font-semibold">
                                {{ $badge->first_name }} {{ $badge->last_name }}
                            </td>
                            <td class="px-5 py-4">
                                {{ $badge->function }}
                            </td>
                            <td class="px-5 py-4">
                                {{ $badge->email }}
                            </td>
                            <td class="px-5 py-4">
                                {{ $badge->phone ?: '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-slate-500">
                                Aucun badge enregistré pour cette entreprise.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($badges->hasPages())
            <div class="border-t border-slate-100 px-6 py-5">
                {{ $badges->links() }}
            </div>
        @endif
    </section>

</x-admin.layout>
