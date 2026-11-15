
<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">

    <a href="{{ route('admin.salons.index') }}"
       class="text-sm font-semibold text-blue-600 hover:underline">
        ← Retour aux salons
    </a>

    <div class="mt-6">
        <p class="text-sm font-semibold text-blue-600">
            Administration / Salons
        </p>
        <h1 class="mt-2 text-3xl font-bold text-slate-900">
            {{ $pageTitle }}
        </h1>
        <p class="mt-2 text-sm text-slate-500">
            Configurez les informations de cette édition.
        </p>
    </div>

    <form method="POST" action="{{ $formAction }}"
          class="mt-8 space-y-6">
        @csrf

        @if ($formMethod)
            @method($formMethod)
        @endif

        @if ($errors->any())
            <div role="alert"
                 class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                <p class="font-semibold">
                    Veuillez corriger les erreurs suivantes :
                </p>
                <ul class="mt-2 list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8">
            <h2 class="text-lg font-bold text-slate-900">
                Informations du salon
            </h2>

            <div class="mt-6 grid gap-5 sm:grid-cols-2">

                <div class="sm:col-span-2">
                    <label for="name" class="mb-2 block text-sm font-semibold">
                        Nom du salon *
                    </label>
                    <input id="name" name="name" required maxlength="255"
                           value="{{ old('name', $salon->name) }}"
                           placeholder="Ex : Refrigair Expo"
                           class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="edition" class="mb-2 block text-sm font-semibold">
                        Édition *
                    </label>
                    <input id="edition" name="edition" required maxlength="100"
                           value="{{ old('edition', $salon->edition) }}"
                           placeholder="Ex : 2027"
                           class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="city" class="mb-2 block text-sm font-semibold">
                        Ville *
                    </label>
                    <input id="city" name="city" required maxlength="150"
                           value="{{ old('city', $salon->city) }}"
                           placeholder="Ex : Casablanca"
                           class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="sm:col-span-2">
                    <label for="venue" class="mb-2 block text-sm font-semibold">
                        Lieu *
                    </label>
                    <input id="venue" name="venue" required maxlength="255"
                           value="{{ old('venue', $salon->venue) }}"
                           placeholder="Ex : OFEC Casablanca"
                           class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="start_date" class="mb-2 block text-sm font-semibold">
                        Date de début *
                    </label>
                    <input type="date" id="start_date" name="start_date" required
                           value="{{ old('start_date', $salon->start_date?->format('Y-m-d')) }}"
                           class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="end_date" class="mb-2 block text-sm font-semibold">
                        Date de fin *
                    </label>
                    <input type="date" id="end_date" name="end_date" required
                           value="{{ old('end_date', $salon->end_date?->format('Y-m-d')) }}"
                           class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="sm:col-span-2">
                    <label for="description" class="mb-2 block text-sm font-semibold">
                        Description
                    </label>
                    <textarea id="description" name="description" rows="5"
                              maxlength="5000"
                              placeholder="Présentation et objectifs du salon..."
                              class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description', $salon->description) }}</textarea>
                </div>
            </div>
        </section>

        <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8">
            <h2 class="text-lg font-bold text-slate-900">
                Configuration
            </h2>

            <div class="mt-6 grid gap-5 sm:grid-cols-2">

                <div>
                    <label for="status" class="mb-2 block text-sm font-semibold">
                        Statut du salon *
                    </label>
                    <select id="status" name="status"
                            class="w-full rounded-xl border-slate-300 text-sm">
                        <option value="planned"
                            @selected(old('status', $salon->status) === 'planned')>
                            Planifié
                        </option>
                        <option value="active"
                            @selected(old('status', $salon->status) === 'active')>
                            En cours
                        </option>
                        <option value="completed"
                            @selected(old('status', $salon->status) === 'completed')>
                            Terminé
                        </option>
                        <option value="cancelled"
                            @selected(old('status', $salon->status) === 'cancelled')>
                            Annulé
                        </option>
                    </select>
                </div>

                <div>
                    <label for="registration_open"
                           class="mb-2 block text-sm font-semibold">
                        Inscriptions *
                    </label>
                    <select id="registration_open" name="registration_open"
                            class="w-full rounded-xl border-slate-300 text-sm">
                        <option value="0"
                            @selected((string) old('registration_open', (int) $salon->registration_open) === '0')>
                            Fermées
                        </option>
                        <option value="1"
                            @selected((string) old('registration_open', (int) $salon->registration_open) === '1')>
                            Ouvertes
                        </option>
                    </select>
                </div>
            </div>

            <p class="mt-4 text-xs leading-5 text-slate-500">
                Pour les salons terminés ou annulés, les inscriptions doivent rester fermées.
                Ce paramètre sera appliqué aux formulaires d'inscription
                lorsque ceux-ci seront reliés aux salons.
            </p>
        </section>

        <div class="flex flex-wrap justify-end gap-3">
            <a href="{{ route('admin.salons.index') }}"
               class="rounded-xl border border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                Annuler
            </a>

            <button type="submit"
                    class="rounded-xl bg-[#2563EB] px-6 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                {{ $salon->exists ? 'Enregistrer les modifications' : 'Créer le salon' }}
            </button>
        </div>
    </form>
</x-admin.layout>
