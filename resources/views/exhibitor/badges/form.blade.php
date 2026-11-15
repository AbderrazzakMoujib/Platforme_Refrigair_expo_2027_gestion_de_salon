
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        {{ $method ? 'Modifier un badge' : 'Ajouter un badge' }}
        | Refrigair Expo 2027
    </title>

    <link rel="icon" type="image/png"
          href="{{ asset('assets/images/icons/favicon_io.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#F5F7FB] font-sans text-slate-900">

    <!-- Header -->
    <header class="bg-[#0F2347] px-5 py-4">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4">

            <a href="{{ route('exposant.index') }}">
                <img
                    src="{{ asset('assets/images/Refrigairexpo-February-2-4-2027-OFEC-Casablanca-Morocco-Sans-titre-4-removebg-preview.png') }}"
                    alt="Refrigair Expo 2027"
                    class="h-14 w-48 object-contain sm:w-56"
                >
            </a>

            <a href="{{ route('exhibitor.badges.index') }}"
               class="text-sm font-medium text-white hover:text-blue-200">
                Retour aux badges
            </a>
        </div>
    </header>

    <!-- Content -->
    <main class="mx-auto max-w-4xl px-4 py-8 sm:py-12">

        <div class="mb-7">

            <p class="text-sm font-semibold text-[#2563EB]">
                Espace Exposant / Gestion des badges
            </p>

            <h1 class="mt-2 text-2xl font-bold sm:text-3xl">
                {{ $method ? 'Modifier un badge' : 'Ajouter un badge' }}
            </h1>

            <p class="mt-2 text-sm text-slate-500">
                Renseignez les informations du membre de votre équipe.
            </p>

        </div>

        <!-- Form Card -->
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">

            <div class="mb-7 border-b border-slate-100 pb-5">
                <h2 class="text-lg font-bold">
                    Informations du titulaire
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    Les champs marqués * sont obligatoires.
                </p>
            </div>

            @if ($errors->any())
                <div role="alert"
                     class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    Veuillez corriger les erreurs du formulaire.
                </div>
            @endif

            <form method="POST"
                  action="{{ $action }}"
                  class="space-y-6">

                @csrf

                @if ($method)
                    @method('PUT')
                @endif

                <div class="grid gap-5 sm:grid-cols-2">

                    <!-- Prénom -->
                    <div>
                        <label for="first_name"
                               class="block text-sm font-semibold text-slate-700">
                            Prénom *
                        </label>

                        <input
                            type="text"
                            id="first_name"
                            name="first_name"
                            value="{{ old('first_name', $badge->first_name) }}"
                            required
                            maxlength="255"
                            autocomplete="given-name"
                            placeholder="Ex : Mohamed"
                            class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500"
                        >

                        @error('first_name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nom -->
                    <div>
                        <label for="last_name"
                               class="block text-sm font-semibold text-slate-700">
                            Nom *
                        </label>

                        <input
                            type="text"
                            id="last_name"
                            name="last_name"
                            value="{{ old('last_name', $badge->last_name) }}"
                            required
                            maxlength="255"
                            autocomplete="family-name"
                            placeholder="Ex : Alami"
                            class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500"
                        >

                        @error('last_name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Fonction -->
                    <div>
                        <label for="function"
                               class="block text-sm font-semibold text-slate-700">
                            Fonction / Poste *
                        </label>

                        <input
                            type="text"
                            id="function"
                            name="function"
                            value="{{ old('function', $badge->function) }}"
                            required
                            maxlength="255"
                            placeholder="Ex : Directeur Commercial"
                            class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500"
                        >

                        @error('function')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email"
                               class="block text-sm font-semibold text-slate-700">
                            Adresse email *
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email', $badge->email) }}"
                            required
                            maxlength="255"
                            autocomplete="email"
                            placeholder="exemple@entreprise.com"
                            class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500"
                        >

                        @error('email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Téléphone -->
                    <div class="sm:col-span-2">
                        <label for="phone"
                               class="block text-sm font-semibold text-slate-700">
                            Téléphone
                            <span class="font-normal text-slate-400">
                                (optionnel)
                            </span>
                        </label>

                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            value="{{ old('phone', $badge->phone) }}"
                            maxlength="40"
                            autocomplete="tel"
                            placeholder="Ex : +212 6 00 00 00 00"
                            class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500"
                        >

                        @error('phone')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                <!-- Actions -->
                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end">

                    <a href="{{ route('exhibitor.badges.index') }}"
                       class="rounded-xl border border-slate-200 px-6 py-3 text-center text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Annuler
                    </a>

                    <button type="submit"
                            class="rounded-xl bg-[#2563EB] px-7 py-3 text-sm font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">

                        {{ $method ? 'Enregistrer les modifications' : 'Ajouter le badge' }}

                    </button>

                </div>

            </form>

        </section>

        <p class="mt-6 text-center text-xs text-slate-400">
            Refrigair Expo 2027 — Gestion des badges exposants
        </p>

    </main>

</body>
</html>
