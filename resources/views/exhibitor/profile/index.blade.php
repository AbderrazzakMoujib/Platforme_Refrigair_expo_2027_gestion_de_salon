<x-exhibitor.layout :user="$user" :exhibitor="$exhibitor">
    @php
        $logoUrl = $exhibitor->logo_url;
        $initials = $exhibitor->initials;
    @endphp

    <div class="mx-auto max-w-5xl space-y-6">
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-blue-600">Espace Exposant / Compte</p>
                <h1 class="mt-1 text-2xl font-black tracking-tight text-[#0F2347] sm:text-3xl">Mon Profil</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Gérez le logo et les coordonnées de votre entreprise. Les informations de votre stand restent consultables séparément.</p>
            </div>
            <a href="{{ route('exhibitor.company.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-[#0F2347] transition hover:border-blue-300 hover:text-blue-700">Voir mon stand</a>
        </header>

        @if (session('status'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
        @endif
        @if ($errors->profile->any())
            <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                <p class="font-bold">Veuillez corriger les informations suivantes :</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->profile->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-5 sm:px-7">
                <h2 class="text-lg font-extrabold text-[#0F2347]">Identité de l’entreprise</h2>
                <p class="mt-1 text-sm text-slate-500">Le nom de l’entreprise et le statut sont gérés par l’organisation.</p>
            </div>
            <div class="grid gap-6 p-5 sm:grid-cols-[auto_1fr] sm:items-center sm:p-7">
                <div class="flex items-center gap-4">
                    <img id="exhibitor-logo-preview" src="{{ $logoUrl ?? '' }}" alt="Aperçu du logo {{ $exhibitor->company_name }}" class="{{ $logoUrl ? '' : 'hidden' }} size-24 shrink-0 rounded-2xl border border-slate-200 bg-white object-contain p-2 sm:size-28">
                    <span id="exhibitor-logo-fallback" class="{{ $logoUrl ? 'hidden' : '' }} grid size-24 shrink-0 place-items-center rounded-2xl bg-blue-50 text-2xl font-black text-blue-700 sm:size-28">{{ $initials }}</span>
                    <div class="sm:hidden">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Logo actuel</p>
                        <p class="mt-1 text-sm font-bold text-[#0F2347]">{{ $exhibitor->company_name }}</p>
                    </div>
                </div>
                <dl class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Raison sociale</dt>
                        <dd class="mt-1 break-words font-bold text-[#0F2347]">{{ $exhibitor->company_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Responsable / Contact principal</dt>
                        <dd class="mt-1 break-words font-bold text-[#0F2347]">{{ $exhibitor->contact_name ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Statut du compte</dt>
                        <dd class="mt-1"><span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $user->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $user->is_active ? 'Actif' : 'Inactif' }}</span></dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">E-mail de connexion</dt>
                        <dd class="mt-1 break-all font-medium text-slate-700">{{ $user->email }}</dd>
                        <p class="mt-1 text-xs text-slate-500">Cet identifiant de connexion n’est pas modifié ici.</p>
                    </div>
                </dl>
            </div>
        </section>

        <form method="POST" action="{{ route('exhibitor.profile.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
                <div>
                    <h2 class="text-lg font-extrabold text-[#0F2347]">Logo de l’entreprise</h2>
                    <p class="mt-1 text-sm text-slate-500">Choisissez un fichier JPG, PNG ou WebP de 2 Mo maximum. L’aperçu est immédiat.</p>
                </div>
                <label for="exhibitor-logo" class="mt-5 flex min-h-14 cursor-pointer flex-col items-center justify-center rounded-xl border border-dashed border-blue-300 bg-blue-50/50 px-4 py-4 text-center text-sm font-semibold text-blue-800 transition hover:bg-blue-50 sm:flex-row sm:gap-2">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 16V4m0 0L7 9m5-5 5 5M4 16v4h16v-4"/></svg>
                    Sélectionner un nouveau logo
                    <input id="exhibitor-logo" name="logo" type="file" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp" class="sr-only">
                </label>
                <p id="exhibitor-logo-filename" class="mt-2 min-h-5 text-xs text-slate-500" aria-live="polite"></p>
                @foreach ($errors->profile->get('logo') as $error)<p class="mt-1 text-sm font-medium text-red-700">{{ $error }}</p>@endforeach
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
                <div class="mb-5">
                    <h2 class="text-lg font-extrabold text-[#0F2347]">Coordonnées</h2>
                    <p class="mt-1 text-sm text-slate-500">Ces coordonnées concernent votre entreprise et restent distinctes de votre e-mail de connexion.</p>
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="company_name" class="block text-sm font-semibold text-slate-700">Raison sociale</label>
                        <input id="company_name" type="text" value="{{ $exhibitor->company_name }}" readonly aria-readonly="true" class="mt-2 block min-h-12 w-full rounded-xl border-slate-200 bg-slate-50 text-sm text-slate-600">
                        <p class="mt-1 text-xs text-slate-500">Modification réservée à l’équipe organisatrice.</p>
                    </div>
                    <div>
                        <label for="contact_name" class="block text-sm font-semibold text-slate-700">Nom du responsable <span class="text-red-600">*</span></label>
                        <input id="contact_name" name="contact_name" type="text" required maxlength="255" value="{{ old('contact_name', $exhibitor->contact_name) }}" autocomplete="name" class="mt-2 block min-h-12 w-full rounded-xl border-slate-300 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                        @foreach ($errors->profile->get('contact_name') as $error)<p class="mt-1 text-sm text-red-700">{{ $error }}</p>@endforeach
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700">E-mail de contact <span class="text-red-600">*</span></label>
                        <input id="email" name="email" type="email" required maxlength="255" value="{{ old('email', $exhibitor->email) }}" autocomplete="email" class="mt-2 block min-h-12 w-full rounded-xl border-slate-300 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                        @foreach ($errors->profile->get('email') as $error)<p class="mt-1 text-sm text-red-700">{{ $error }}</p>@endforeach
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-semibold text-slate-700">Téléphone</label>
                        <input id="phone" name="phone" type="tel" maxlength="40" value="{{ old('phone', $exhibitor->phone) }}" autocomplete="tel" class="mt-2 block min-h-12 w-full rounded-xl border-slate-300 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                        @foreach ($errors->profile->get('phone') as $error)<p class="mt-1 text-sm text-red-700">{{ $error }}</p>@endforeach
                    </div>
                </div>
                <div class="mt-7 flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <a href="{{ route('exhibitor.company.index') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Annuler</a>
                    <button type="submit" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-[#2563EB] px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">Enregistrer mon profil</button>
                </div>
            </section>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const input = document.getElementById('exhibitor-logo');
            const preview = document.getElementById('exhibitor-logo-preview');
            const fallback = document.getElementById('exhibitor-logo-fallback');
            const filename = document.getElementById('exhibitor-logo-filename');
            let temporaryUrl = null;

            input?.addEventListener('change', () => {
                if (temporaryUrl) URL.revokeObjectURL(temporaryUrl);
                const file = input.files?.[0];
                if (!file) {
                    filename.textContent = '';
                    return;
                }

                filename.textContent = file.name;
                temporaryUrl = URL.createObjectURL(file);
                preview.src = temporaryUrl;
                preview.classList.remove('hidden');
                fallback.classList.add('hidden');
            });
        });
    </script>
</x-exhibitor.layout>
