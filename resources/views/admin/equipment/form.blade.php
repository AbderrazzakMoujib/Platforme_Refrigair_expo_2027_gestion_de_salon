
<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">

    <a href="{{ route('admin.equipements.index') }}"
       class="text-sm font-semibold text-blue-600 hover:underline">
        ← Retour aux équipements
    </a>

    <div class="mt-6">
        <p class="text-sm font-semibold text-blue-600">
            Administration / Équipements
        </p>
        <h1 class="mt-2 text-3xl font-bold text-slate-900">
            {{ $pageTitle }}
        </h1>
        <p class="mt-2 text-sm text-slate-500">
            Configurez les informations de l'équipement.
        </p>
    </div>

    <form method="POST"
          action="{{ $formAction }}"
          enctype="multipart/form-data"
          class="mt-7 max-w-4xl rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8">

        @csrf
        @if ($formMethod)
            @method($formMethod)
        @endif

        @if ($errors->any())
            <div role="alert"
                 class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                Veuillez corriger les erreurs du formulaire.
            </div>
        @endif

        <div class="grid gap-6 sm:grid-cols-2">

            <div class="sm:col-span-2">
                <label for="name" class="text-sm font-semibold text-slate-700">
                    Nom de l'équipement *
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $equipmentItem->name) }}"
                    required
                    maxlength="255"
                    placeholder="Ex : Table ronde"
                    class="mt-2 w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                >

                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="category" class="text-sm font-semibold text-slate-700">
                    Catégorie *
                </label>

                <select id="category" name="category" required
                        class="mt-2 w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Sélectionnez une catégorie</option>

                    @foreach ($categories as $category)
                        <option value="{{ $category }}"
                                @selected(old('category', $equipmentItem->category) === $category)>
                            {{ $category }}
                        </option>
                    @endforeach
                </select>

                @error('category')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="price" class="text-sm font-semibold text-slate-700">
                    Prix (DH) *
                </label>

                <input type="number"
                       id="price"
                       name="price"
                       min="0"
                       max="99999999.99"
                       step="0.01"
                       required
                       value="{{ old('price', $equipmentItem->price) }}"
                       class="mt-2 w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">

                @error('price')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="quantity_available"
                       class="text-sm font-semibold text-slate-700">
                    Quantité disponible *
                </label>

                <input
                    type="number"
                    id="quantity_available"
                    name="quantity_available"
                    value="{{ old('quantity_available', $equipmentItem->quantity_available) }}"
                    min="0"
                    max="1000000"
                    step="1"
                    required
                    class="mt-2 w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                >

                @error('quantity_available')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="is_active" class="text-sm font-semibold text-slate-700">
                    Statut *
                </label>

                <select id="is_active" name="is_active" required
                        class="mt-2 w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="1"
                            @selected((string) old('is_active', (int) $equipmentItem->is_active) === '1')>
                        Actif
                    </option>
                    <option value="0"
                            @selected((string) old('is_active', (int) $equipmentItem->is_active) === '0')>
                        Inactif
                    </option>
                </select>

                @error('is_active')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="description" class="text-sm font-semibold text-slate-700">
                    Description
                </label>

                <textarea
                    id="description"
                    name="description"
                    rows="4"
                    maxlength="5000"
                    placeholder="Description et caractéristiques de l'équipement..."
                    class="mt-2 w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                >{{ old('description', $equipmentItem->description) }}</textarea>

                @error('description')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="image" class="text-sm font-semibold text-slate-700">
                    Photo de l'équipement
                </label>

                @if ($equipmentItem->image_path)
                    <img
                        src="{{ asset('storage/'.$equipmentItem->image_path) }}"
                        alt="{{ $equipmentItem->name }}"
                        class="mt-3 size-36 rounded-xl border border-slate-200 object-cover"
                    >
                @endif

                <input
                    type="file"
                    id="image"
                    name="image"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                    class="mt-2 block w-full rounded-xl border border-slate-200 p-3 text-sm"
                >

                <p class="mt-2 text-xs text-slate-500">
                    JPG, PNG ou WebP. Taille maximale : 2 Mo.
                    @if ($equipmentItem->exists)
                        Laissez vide pour conserver la photo actuelle.
                    @endif
                </p>

                @error('image')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

        </div>

        <div class="mt-8 flex flex-wrap justify-end gap-3 border-t border-slate-100 pt-6">

            <a href="{{ route('admin.equipements.index') }}"
               class="rounded-xl border border-slate-200 px-6 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                Annuler
            </a>

            <button type="submit"
                    class="rounded-xl bg-[#2563EB] px-6 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                {{ $equipmentItem->exists ? 'Enregistrer les modifications' : 'Ajouter l’équipement' }}
            </button>

        </div>
    </form>

</x-admin.layout>
