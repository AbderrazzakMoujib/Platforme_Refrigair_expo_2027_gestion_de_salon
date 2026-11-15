<x-commercial.layout :commercial="$commercial" :commercial-initials="$commercialInitials">
    <a href="{{ route('commercial.exhibitors.index') }}" class="text-sm font-semibold text-[#2563EB] hover:underline">← Retour à mes exposants</a>
    <h1 class="mt-3 text-2xl font-bold text-slate-900">{{ $pageTitle }}</h1>
    <form method="POST" action="{{ $formAction }}" class="mt-6 max-w-3xl rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200/70">
        @csrf @if ($formMethod) @method($formMethod) @endif
        <div class="grid gap-5 sm:grid-cols-2">
            @foreach ([['company_name','Nom de l’entreprise','text'],['contact_name','Nom du responsable','text'],['email','Email','email'],['phone','Téléphone','tel'],['stand_number','Numéro du stand','text'],['area_sqm','Surface en m²','number'],['authorized_badges','Nombre de badges autorisés','number']] as [$field,$label,$type])
                <div @class(['sm:col-span-2' => in_array($field, ['company_name','contact_name'])])>
                    <label for="{{ $field }}" class="text-sm font-medium text-slate-700">{{ $label }}</label>
                    <input id="{{ $field }}" name="{{ $field }}" type="{{ $type }}" value="{{ old($field, $exhibitor->{$field}) }}" @required(! in_array($field, ['phone','stand_number'])) @if($field === 'area_sqm') min="0.01" step="0.01" @elseif($field === 'authorized_badges') min="0" step="1" @endif class="mt-1 w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error($field) <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            @endforeach
            @if (! $formMethod)
                <div><label for="password" class="text-sm font-medium">Password initial</label><input id="password" name="password" type="password" required minlength="8" autocomplete="new-password" class="mt-1 w-full rounded-lg border-slate-300"><p class="mt-1 text-xs text-slate-500">Au moins 8 caractères.</p>@error('password')<p class="text-sm text-red-600">{{ $message }}</p>@enderror</div>
                <div><label for="password_confirmation" class="text-sm font-medium">Confirmation</label><input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password" class="mt-1 w-full rounded-lg border-slate-300"></div>
            @endif
        </div>
        <div class="mt-6 flex justify-end gap-3"><a href="{{ route('commercial.exhibitors.index') }}" class="rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700">Annuler</a><button class="rounded-lg bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Enregistrer</button></div>
    </form>
</x-commercial.layout>
