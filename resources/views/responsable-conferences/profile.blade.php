<x-responsable-conferences.layout :user="$user">
    <div class="mx-auto max-w-3xl space-y-6"><header><p class="text-sm font-semibold text-blue-600">Compte</p><h1 class="mt-1 text-3xl font-extrabold text-[#0F2347]">Mon profil</h1></header>
        @if (session('status'))<div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>@endif
        @if ($errors->any())<div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form method="POST" enctype="multipart/form-data" action="{{ route('responsable-conferences.profile.update') }}" class="space-y-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">@csrf @method('PUT')
            <x-profile-photo-upload :user="$user" id="manager-own-profile-photo" />
            <label class="block text-sm font-semibold">Nom<input name="name" required maxlength="255" value="{{ old('name',$user->name) }}" class="mt-1 w-full rounded-xl border-slate-300"></label>
            <label class="block text-sm font-semibold">Adresse e-mail<input name="email" required type="email" value="{{ old('email',$user->email) }}" class="mt-1 w-full rounded-xl border-slate-300"></label>
            <div class="border-t border-slate-100 pt-5"><h2 class="font-bold text-[#0F2347]">Changer le mot de passe (facultatif)</h2><label class="mt-3 block text-sm font-semibold">Mot de passe actuel<input name="current_password" type="password" autocomplete="current-password" class="mt-1 w-full rounded-xl border-slate-300"></label><label class="mt-3 block text-sm font-semibold">Nouveau mot de passe<input name="password" type="password" minlength="8" autocomplete="new-password" class="mt-1 w-full rounded-xl border-slate-300"></label><label class="mt-3 block text-sm font-semibold">Confirmation<input name="password_confirmation" type="password" minlength="8" autocomplete="new-password" class="mt-1 w-full rounded-xl border-slate-300"></label></div>
            <div class="flex justify-end"><button class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white">Enregistrer</button></div>
        </form>
    </div>
</x-responsable-conferences.layout>
