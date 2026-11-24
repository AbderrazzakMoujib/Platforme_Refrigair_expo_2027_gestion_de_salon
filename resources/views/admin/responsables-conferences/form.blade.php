<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">
    <div class="mx-auto max-w-3xl space-y-6">
        <nav class="text-sm text-slate-500"><a href="{{ route('admin.responsables-conferences.index') }}" class="hover:text-blue-700">Responsables Conférences</a> / {{ $editing ? 'Modifier' : 'Créer' }}</nav>
        <header><h1 class="text-3xl font-extrabold text-[#0F2347]">{{ $editing ? 'Modifier le compte' : 'Créer un responsable' }}</h1><p class="mt-2 text-sm text-slate-500">Ce rôle est attribué uniquement par l’administration.</p></header>
        @if ($errors->any())<div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form method="POST" enctype="multipart/form-data" action="{{ $editing ? route('admin.responsables-conferences.update', $manager) : route('admin.responsables-conferences.store') }}" class="space-y-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
            @csrf @if ($editing) @method('PUT') @endif
            <x-profile-photo-upload :user="$manager" id="manager-profile-photo" />
            <label class="block text-sm font-semibold text-[#0F2347]">Nom<input name="name" required maxlength="255" value="{{ old('name', $manager->name) }}" class="mt-1 w-full rounded-xl border-slate-300"></label>
            <label class="block text-sm font-semibold text-[#0F2347]">Adresse e-mail<input name="email" required type="email" maxlength="255" value="{{ old('email', $manager->email) }}" class="mt-1 w-full rounded-xl border-slate-300"></label>
            @unless ($editing)
                <label class="block text-sm font-semibold text-[#0F2347]">Mot de passe initial<input name="password" required type="password" minlength="8" autocomplete="new-password" class="mt-1 w-full rounded-xl border-slate-300"></label>
                <label class="block text-sm font-semibold text-[#0F2347]">Confirmation<input name="password_confirmation" required type="password" minlength="8" autocomplete="new-password" class="mt-1 w-full rounded-xl border-slate-300"></label>
            @endunless
            <div class="flex justify-end gap-3"><a href="{{ route('admin.responsables-conferences.index') }}" class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-semibold">Annuler</a><button class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white">{{ $editing ? 'Enregistrer' : 'Créer le compte' }}</button></div>
        </form>
    </div>
</x-admin.layout>
