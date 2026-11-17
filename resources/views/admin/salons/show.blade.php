
<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">

    <a href="{{ route('admin.salons.index') }}"
       class="text-sm font-semibold text-blue-600 hover:underline">
        ← Retour aux salons
    </a>

    <div class="mt-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-semibold text-blue-600">
                Administration / Salon
            </p>
            <h1 class="mt-2 text-3xl font-bold text-slate-900">
                {{ $salon->name }}
            </h1>
            <p class="mt-2 text-sm text-slate-500">
                Édition {{ $salon->edition }}
            </p>
        </div>

        <a href="{{ route('admin.salons.edit', $salon) }}"
           class="rounded-xl bg-[#2563EB] px-5 py-3 text-center text-sm font-semibold text-white hover:bg-blue-700">
            Modifier le salon
        </a>
    </div>

    @if (session('status'))
        <div role="status"
             class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div role="alert"
             class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif




@if ($salon->cover_image)
    <div class="mt-6 flex items-center justify-center rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <img
            src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($salon->cover_image) }}"
            alt="Couverture du salon {{ $salon->name }}"
            class="block h-auto max-h-[650px] max-w-full rounded-xl object-contain"
        >
    </div>
@endif




    <section class="mt-8 grid gap-6 lg:grid-cols-3">
        <article class="rounded-2xl bg-white p-7 shadow-sm ring-1 ring-slate-200 lg:col-span-2">
            <h2 class="text-lg font-bold text-slate-900">
                Informations générales
            </h2>

            <dl class="mt-7 grid gap-6 sm:grid-cols-2">
                <div>
                    <dt class="text-sm text-slate-500">Nom du salon</dt>
                    <dd class="mt-2 font-bold text-slate-800">
                        {{ $salon->name }}
                    </dd>
                </div>

                <div>
                    <dt class="text-sm text-slate-500">Édition</dt>
                    <dd class="mt-2 font-bold text-slate-800">
                        {{ $salon->edition }}
                    </dd>
                </div>

                <div>
                    <dt class="text-sm text-slate-500">Ville</dt>
                    <dd class="mt-2 font-bold text-slate-800">
                        {{ $salon->city }}
                    </dd>
                </div>

                <div>
                    <dt class="text-sm text-slate-500">Lieu</dt>
                    <dd class="mt-2 font-bold text-slate-800">
                        {{ $salon->venue }}
                    </dd>
                </div>

                <div>
                    <dt class="text-sm text-slate-500">Date de début</dt>
                    <dd class="mt-2 font-bold text-slate-800">
                        {{ $salon->start_date->format('d/m/Y') }}
                    </dd>
                </div>

                <div>
                    <dt class="text-sm text-slate-500">Date de fin</dt>
                    <dd class="mt-2 font-bold text-slate-800">
                        {{ $salon->end_date->format('d/m/Y') }}
                    </dd>
                </div>
            </dl>

            
<div class="mt-7 border-t border-slate-100 pt-6">
    <h3 class="font-semibold text-slate-800">
        Description
    </h3>

    <div class="mt-3 whitespace-normal text-sm leading-7 text-slate-600">
        {!! \Illuminate\Support\Str::markdown(
            $salon->description ?: 'Aucune description renseignée.',
            [
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]
        ) !!}
    </div>
</div>

        </article>

        <aside class="space-y-6">
            <article class="rounded-2xl bg-white p-7 shadow-sm ring-1 ring-slate-200">
                <h2 class="font-bold text-slate-800">
                    Statut du salon
                </h2>
                <span @class([
                    'mt-4 inline-flex rounded-full px-4 py-2 text-sm font-semibold',
                    'bg-slate-100 text-slate-700' => $salon->status === 'planned',
                    'bg-blue-50 text-blue-700' => $salon->status === 'active',
                    'bg-emerald-50 text-emerald-700' => $salon->status === 'completed',
                    'bg-red-50 text-red-700' => $salon->status === 'cancelled',
                ])>
                    {{ $salon->status_label }}
                </span>
            </article>

            <article class="rounded-2xl bg-white p-7 shadow-sm ring-1 ring-slate-200">
                <h2 class="font-bold text-slate-800">
                    Gestion des inscriptions
                </h2>

                <p class="mt-3 text-sm">
                    État :
                    <span @class([
                        'font-bold',
                        'text-emerald-700' => $salon->registration_open,
                        'text-slate-600' => !$salon->registration_open,
                    ])>
                        {{ $salon->registration_open ? 'Ouvertes' : 'Fermées' }}
                    </span>
                </p>

                @if (in_array($salon->status, ['planned', 'active'], true))
                    <form method="POST"
                          action="{{ route('admin.salons.registration', $salon) }}"
                          class="mt-5"
                          onsubmit="return confirm('Confirmer ce changement ?')">
                        @csrf
                        @method('PATCH')

                        <button type="submit"
                                class="w-full rounded-xl bg-[#0F2347] px-5 py-3 text-sm font-semibold text-white hover:bg-[#173866]">
                            {{ $salon->registration_open
                                ? 'Fermer les inscriptions'
                                : 'Ouvrir les inscriptions' }}
                        </button>
                    </form>
                @else
                    <p class="mt-4 text-xs text-slate-500">
                        Les inscriptions sont verrouillées pour ce statut.
                    </p>
                @endif

                <p class="mt-4 text-xs leading-5 text-slate-500">
                    Cette commande modifie la configuration du salon.
                    Le contrôle des inscriptions des exposants sera
                    relié à cette configuration dans une prochaine étape.
                </p>
            </article>
        </aside>
    </section>

</x-admin.layout>
