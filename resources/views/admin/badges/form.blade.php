
<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">

    <a href="{{ route('admin.badges.index') }}"
       class="text-sm font-semibold text-blue-600 hover:underline">
        ← Retour aux badges
    </a>

    <div class="mt-6">
        <p class="text-sm font-semibold text-blue-600">
            Administration / Badges
        </p>
        <h1 class="mt-2 text-3xl font-bold">
            {{ $pageTitle }}
        </h1>
        <p class="mt-2 text-sm text-slate-500">
            Renseignez les informations du titulaire du badge.
        </p>
    </div>

    <form method="POST"
          action="{{ $formAction }}"
          class="mt-8 max-w-4xl rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8">
        @csrf

        @if ($formMethod)
            @method($formMethod)
        @endif

        @if ($errors->any())
            <div role="alert"
                 class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (! $badge->exists)
            <div>
                <label for="exhibitor_id"
                       class="mb-2 block text-sm font-semibold">
                    Exposant *
                </label>

                <select id="exhibitor_id"
                        name="exhibitor_id"
                        required
                        class="w-full rounded-xl border-slate-300 text-sm">
                    <option value="">Sélectionner un exposant</option>

                    @foreach ($exhibitors as $exhibitor)
                        <option value="{{ $exhibitor->id }}"
                            @selected((string) old('exhibitor_id', request('exhibitor_id')) === (string) $exhibitor->id)>
                            {{ $exhibitor->company_name }}
                            — {{ $exhibitor->badges_count }}/{{ $exhibitor->authorized_badges }}
                        </option>
                    @endforeach
                </select>
                @error('exhibitor_id')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        @else
            <div class="rounded-xl bg-blue-50 p-4">
                <p class="text-xs font-medium text-blue-600">Exposant</p>
                <p class="mt-1 font-bold text-blue-900">
                    {{ $badge->exhibitor->company_name }}
                </p>
                <p class="mt-1 text-xs text-blue-700">
                    La société ne peut pas être changée après création.
                </p>
            </div>
        @endif

        <div class="mt-6 grid gap-5 sm:grid-cols-2">
            <div>
                <label for="first_name"
                       class="mb-2 block text-sm font-semibold">
                    Prénom *
                </label>
                <input id="first_name" name="first_name"
                       required maxlength="255"
                       value="{{ old('first_name', $badge->first_name) }}"
                       class="w-full rounded-xl border-slate-300 text-sm">
                @error('first_name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="last_name"
                       class="mb-2 block text-sm font-semibold">
                    Nom *
                </label>
                <input id="last_name" name="last_name"
                       required maxlength="255"
                       value="{{ old('last_name', $badge->last_name) }}"
                       class="w-full rounded-xl border-slate-300 text-sm">
                @error('last_name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="function"
                       class="mb-2 block text-sm font-semibold">
                    Fonction *
                </label>
                <input id="function" name="function"
                       required maxlength="255"
                       placeholder="Ex : Responsable commercial"
                       value="{{ old('function', $badge->function) }}"
                       class="w-full rounded-xl border-slate-300 text-sm">
                @error('function')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email"
                       class="mb-2 block text-sm font-semibold">
                    Email *
                </label>
                <input id="email" name="email" type="email"
                       required maxlength="255"
                       value="{{ old('email', $badge->email) }}"
                       class="w-full rounded-xl border-slate-300 text-sm">
                @error('email')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="phone"
                       class="mb-2 block text-sm font-semibold">
                    Téléphone
                </label>
                <input id="phone" name="phone" type="tel"
                       maxlength="40"
                       value="{{ old('phone', $badge->phone) }}"
                       class="w-full rounded-xl border-slate-300 text-sm">
                @error('phone')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-8 flex flex-wrap justify-end gap-3 border-t border-slate-100 pt-6">
            <a href="{{ route('admin.badges.index') }}"
               class="rounded-xl border border-slate-200 px-6 py-3 text-sm font-semibold">
                Annuler
            </a>

            <button type="submit"
                    class="rounded-xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                {{ $badge->exists ? 'Enregistrer les modifications' : 'Créer le badge' }}
            </button>
        </div>
    </form>

</x-admin.layout>
