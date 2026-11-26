<label class="block text-sm font-semibold text-slate-700">
    Statut
    <select name="status" data-status-select class="conference-booking-input mt-2">
        @foreach ($statuses as $status)
            @if (($allowAvailable ?? true) || $status->isTaken())
                <option value="{{ $status->value }}" @selected($selStatus === $status->value)>{{ $status->label() }}</option>
            @endif
        @endforeach
    </select>
</label>

<fieldset data-holder-block>
    <legend class="text-sm font-semibold text-slate-700">Réservation pour</legend>
    <div class="mt-2 grid gap-2 sm:grid-cols-2">
        <label class="flex min-h-12 items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-700 transition has-[:checked]:border-[#2563EB] has-[:checked]:bg-blue-50">
            <input type="radio" name="holder_type" value="exhibitor" @checked($holderType === 'exhibitor') class="size-4 border-slate-300 text-[#2563EB] focus:ring-[#2563EB]">
            Exposant inscrit
        </label>
        <label class="flex min-h-12 items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-700 transition has-[:checked]:border-[#2563EB] has-[:checked]:bg-blue-50">
            <input type="radio" name="holder_type" value="external" @checked($holderType === 'external') class="size-4 border-slate-300 text-[#2563EB] focus:ring-[#2563EB]">
            Société / contact externe
        </label>
    </div>

    <div data-holder-panel="exhibitor" class="mt-3">
        <label class="block text-sm font-semibold text-slate-700">
            Exposant
            <select name="exhibitor_id" class="conference-booking-input mt-2">
                <option value="">Choisir un exposant</option>
                @foreach ($exhibitors as $exhibitor)
                    <option value="{{ $exhibitor->id }}" @selected((int) $value('exhibitor_id') === $exhibitor->id)>{{ $exhibitor->company_name }}</option>
                @endforeach
            </select>
        </label>
    </div>

    <div data-holder-panel="external" class="mt-3 space-y-4">
        <label class="block text-sm font-semibold text-slate-700">
            Société / titulaire
            <input name="exhibitor_name" value="{{ $value('exhibitor_name') }}" maxlength="255" autocomplete="organization" class="conference-booking-input mt-2">
        </label>
        <div class="grid gap-4 sm:grid-cols-2">
            <label class="block text-sm font-semibold text-slate-700">
                Contact
                <input name="contact_name" value="{{ $value('contact_name') }}" maxlength="255" autocomplete="name" class="conference-booking-input mt-2">
            </label>
            <label class="block text-sm font-semibold text-slate-700">
                Téléphone
                <input name="contact_phone" value="{{ $value('contact_phone') }}" maxlength="40" autocomplete="tel" inputmode="tel" class="conference-booking-input mt-2">
            </label>
        </div>
        <label class="block text-sm font-semibold text-slate-700">
            E-mail
            <input name="contact_email" type="email" value="{{ $value('contact_email') }}" maxlength="255" autocomplete="email" class="conference-booking-input mt-2">
        </label>
    </div>
</fieldset>

<label class="block text-sm font-semibold text-slate-700">
    Sujet / titre
    <input name="title" value="{{ $value('title') }}" maxlength="255" class="conference-booking-input mt-2">
</label>
<label class="block text-sm font-semibold text-slate-700">
    Résumé <span class="font-normal text-slate-500">(5 lignes maximum)</span>
    <textarea name="summary" rows="3" maxlength="1000" class="conference-booking-input mt-2 min-h-24 resize-y">{{ $value('summary') }}</textarea>
</label>
<label class="block text-sm font-semibold text-slate-700">
    Intervenants
    <textarea name="speakers" rows="2" maxlength="1000" class="conference-booking-input mt-2 min-h-20 resize-y">{{ $value('speakers') }}</textarea>
</label>
<label class="block text-sm font-semibold text-slate-700">
    Notes internes
    <textarea name="notes" rows="2" maxlength="2000" class="conference-booking-input mt-2 min-h-20 resize-y">{{ $value('notes') }}</textarea>
</label>

