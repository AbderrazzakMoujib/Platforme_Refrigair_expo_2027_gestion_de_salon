<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">
    <div class="mx-auto max-w-3xl space-y-6">
        <nav class="text-sm text-slate-500"><a href="{{ route('admin.responsables-techniques.index') }}" class="hover:text-blue-700">Responsables Techniques</a> / {{ $editing ? 'Modifier' : 'Créer' }}</nav>
        <header><h1 class="text-3xl font-extrabold text-[#0F2347]">{{ $editing ? 'Modifier le compte' : 'Créer un Responsable Technique' }}</h1><p class="mt-2 text-sm text-slate-500">Ce rôle est attribué uniquement par l’administration.</p></header>
        @if ($errors->any())<div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form method="POST" action="{{ $editing ? route('admin.responsables-techniques.update', $technician) : route('admin.responsables-techniques.store') }}" class="space-y-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">@csrf @if ($editing) @method('PUT') @endif
            <label class="block text-sm font-semibold text-[#0F2347]">Nom<input name="name" required maxlength="255" value="{{ old('name', $technician->name) }}" class="mt-1 w-full rounded-xl border-slate-300 focus:border-[#2563EB] focus:ring-[#2563EB]"></label>
            <label class="block text-sm font-semibold text-[#0F2347]">Adresse e-mail<input name="email" required type="email" maxlength="255" value="{{ old('email', $technician->email) }}" class="mt-1 w-full rounded-xl border-slate-300 focus:border-[#2563EB] focus:ring-[#2563EB]"></label>
            @unless ($editing)<p class="rounded-xl bg-blue-50 p-4 text-sm text-blue-900">Un courriel sécurisé invitera le responsable à choisir son mot de passe. Aucun mot de passe initial ne sera envoyé.</p>@endunless
            <div class="flex justify-end gap-3"><a href="{{ route('admin.responsables-techniques.index') }}" class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-semibold">Annuler</a><button class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white hover:bg-blue-700">{{ $editing ? 'Enregistrer' : 'Créer le compte' }}</button></div>
        </form>
    </div>
</x-admin.layout>
