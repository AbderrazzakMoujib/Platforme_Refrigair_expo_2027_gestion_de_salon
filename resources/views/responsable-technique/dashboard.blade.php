<x-technical.layout :user="auth()->user()" title="Tableau de bord technique">
    <div class="mb-7">
        <p class="text-sm font-semibold text-blue-700">Vue d’ensemble</p>
        <h1 class="mt-1 text-2xl font-black text-[#0F2347] sm:text-3xl">Tableau de bord</h1>
        <p class="mt-2 text-sm text-slate-600">Indicateurs issus du catalogue, des comptes et du planning actuel.</p>
    </div>

    @php($cards = [
        ['Équipements au catalogue', $stats['equipment_total'], 'Tous les équipements enregistrés'],
        ['Équipements actifs', $stats['equipment_active'], 'Disponibles à la consultation'],
        ['Quantité disponible', $stats['available_quantity'], 'Somme des quantités des équipements actifs'],
        ['Commerciaux actifs', $stats['active_commercials'], 'Comptes commerciaux activés'],
        ['Exposants inscrits', $stats['exhibitors'], 'Fiches exposants enregistrées'],
        ['Réservations confirmées', $stats['confirmed_reservations'], 'Créneaux Réservé ou Payé, comptés une fois'],
        ['Demandes en attente', $stats['pending_requests'], 'Demandes exposants à examiner'],
        ['Créneaux disponibles', $stats['available_slots'], 'Créneaux sans réservation'],
    ])
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($cards as [$label, $value, $hint])
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-slate-500">{{ $label }}</p>
                <p class="mt-3 text-3xl font-black text-[#0F2347]">{{ number_format($value) }}</p>
                <p class="mt-2 text-xs leading-5 text-slate-500">{{ $hint }}</p>
            </article>
        @endforeach
    </div>
    <div class="mt-7 grid gap-4 sm:grid-cols-3">
        <a href="{{ route('responsable-technique.equipment') }}" class="rounded-2xl bg-[#0F2347] p-5 text-white hover:bg-blue-950"><span class="font-bold">Consulter le catalogue</span><span class="mt-1 block text-sm text-blue-200">Équipements, catégories et stocks</span></a>
        <a href="{{ route('responsable-technique.commercials') }}" class="rounded-2xl bg-white p-5 font-bold text-[#0F2347] shadow-sm ring-1 ring-slate-200 hover:ring-blue-300">Suivre l’activité commerciale<span class="mt-1 block text-sm font-normal text-slate-500">Commerciaux et exposants associés</span></a>
        <a href="{{ route('responsable-technique.conferences') }}" class="rounded-2xl bg-white p-5 font-bold text-[#0F2347] shadow-sm ring-1 ring-slate-200 hover:ring-blue-300">Voir le planning conférences<span class="mt-1 block text-sm font-normal text-slate-500">Demandes et créneaux séparés</span></a>
    </div>
</x-technical.layout>
