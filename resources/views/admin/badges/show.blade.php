
<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">

    <a href="{{ route('admin.badges.index') }}"
       class="text-sm font-semibold text-blue-600 hover:underline">
        ← Retour aux badges
    </a>

    <div class="mt-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-semibold text-blue-600">
                Administration / Détail du badge
            </p>

            <h1 class="mt-2 text-3xl font-bold text-slate-900">
                {{ $badge->full_name }}
            </h1>

            <p class="mt-2 text-sm text-slate-500">
                {{ $badge->reference }}
            </p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.badges.print', $badge) }}"
               target="_blank"
               rel="noopener"
               class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold">
                Imprimer
            </a>

            <a href="{{ route('admin.badges.edit', $badge) }}"
               class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white">
                Modifier
            </a>
        </div>
    </div>

    @if (session('status'))
        <div role="status"
             class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="mt-8 grid gap-6 lg:grid-cols-3">

        <div class="space-y-6 lg:col-span-2">

            <section class="rounded-2xl bg-white p-7 shadow-sm ring-1 ring-slate-200">
                <h2 class="text-lg font-bold">Informations du badge</h2>

                <dl class="mt-6 grid gap-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm text-slate-500">Nom complet</dt>
                        <dd class="mt-1 font-bold">{{ $badge->full_name }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm text-slate-500">Fonction</dt>
                        <dd class="mt-1 font-bold">{{ $badge->function }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm text-slate-500">Email</dt>
                        <dd class="mt-1 break-all font-semibold">{{ $badge->email }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm text-slate-500">Téléphone</dt>
                        <dd class="mt-1 font-semibold">
                            {{ $badge->phone ?: 'Non renseigné' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-slate-500">Société</dt>
                        <dd class="mt-1 font-bold">
                            {{ $badge->exhibitor->company_name }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-slate-500">Commercial</dt>
                        <dd class="mt-1 font-semibold">
                            {{ $badge->exhibitor->commercial?->name ?: '—' }}
                        </dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-2xl bg-white p-7 shadow-sm ring-1 ring-slate-200">
                <h2 class="text-lg font-bold">Historique des opérations Admin</h2>

                <div class="mt-5 divide-y divide-slate-100">
                    @forelse ($activities as $activity)
                        <div class="py-4">
                            <div class="flex flex-wrap justify-between gap-2">
                                <p class="font-semibold text-slate-800">
                                    {{ match($activity->action) {
                                        'created' => 'Badge créé',
                                        'updated' => 'Badge modifié',
                                        'activated' => 'Badge activé',
                                        'deactivated' => 'Badge désactivé',
                                        'archived' => 'Badge archivé',
                                        default => $activity->action,
                                    } }}
                                </p>

                                <span class="text-xs text-slate-500">
                                    {{ $activity->created_at->format('d/m/Y H:i') }}
                                </span>
                            </div>

                            <p class="mt-1 text-sm text-slate-500">
                                Par {{ $activity->user?->name ?: 'Utilisateur inconnu' }}
                            </p>
                        </div>
                    @empty
                        <p class="py-5 text-sm text-slate-500">
                            Aucune opération enregistrée.
                        </p>
                    @endforelse
                </div>

                {{ $activities->links() }}
            </section>
        </div>

        <aside class="space-y-6">

            <section class="rounded-2xl bg-white p-6 text-center shadow-sm ring-1 ring-slate-200">
                <h2 class="font-bold">QR Code du badge</h2>

                <img src="{{ $qrImage }}"
                     alt="QR Code de vérification"
                     class="mx-auto mt-5 size-56 max-w-full">

                <p class="mt-3 text-xs text-slate-500">
                    {{ $badge->reference }}
                </p>

                <a href="{{ route('admin.badges.verify', $badge->qr_token) }}"
                   class="mt-5 inline-flex rounded-xl bg-blue-50 px-5 py-3 text-sm font-semibold text-blue-700">
                    Vérifier le QR Code
                </a>
            </section>

            <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <h2 class="font-bold">Statut</h2>

                <p @class([
                    'mt-4 text-lg font-bold',
                    'text-emerald-700' => $badge->is_active,
                    'text-red-700' => ! $badge->is_active,
                ])>
                    {{ $badge->is_active ? 'Actif' : 'Désactivé' }}
                </p>

                <form method="POST"
                      action="{{ route('admin.badges.toggle', $badge) }}"
                      class="mt-5">
                    @csrf
                    @method('PATCH')

                    <button type="submit"
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold hover:bg-slate-50">
                        {{ $badge->is_active ? 'Désactiver' : 'Réactiver' }}
                    </button>
                </form>
            </section>

            <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <h2 class="font-bold">Quota de la société</h2>

                <p class="mt-3 text-2xl font-bold text-blue-700">
                    {{ $usedQuota }} / {{ $badge->exhibitor->authorized_badges }}
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    Badges enregistrés / autorisés
                </p>
            </section>

            <section class="rounded-2xl border border-red-100 bg-white p-6">
                <h2 class="font-bold text-red-700">
                    Archiver le badge
                </h2>

                <p class="mt-2 text-xs text-slate-500">
                    Le badge sera retiré des listes actives.
                    Cette action libère une place dans le quota.
                </p>

                <form method="POST"
                      action="{{ route('admin.badges.destroy', $badge) }}"
                      onsubmit="return confirm('Confirmer l’archivage de ce badge ?')"
                      class="mt-4">
                    @csrf
                    @method('DELETE')

                    <button type="submit"
                            class="w-full rounded-xl bg-red-600 px-4 py-3 text-sm font-semibold text-white hover:bg-red-700">
                        Archiver le badge
                    </button>
                </form>
            </section>

        </aside>
    </div>

</x-admin.layout>
