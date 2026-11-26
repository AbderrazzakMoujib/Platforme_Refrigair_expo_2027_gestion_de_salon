@php
    $en = $locale === 'en';
    $allSlots = $days->flatten(1);
    $totalSlots = $allSlots->count();
    $bookedSlots = $allSlots->filter(fn ($slot) => $slot->status->isTaken())->count();
    $availableSlots = $totalSlots - $bookedSlots;
    $t = $en
        ? [
            'title' => 'Conferences & Technology Lounge',
            'subtitle' => 'Refrigair Expo 2027 · 2–4 February 2027 · OFEC Casablanca',
            'time' => 'Time',
            'space' => 'Space',
            'code' => 'Code',
            'exhibitor' => 'Exhibitor',
            'status' => 'Status',
            'price' => 'Price excl. VAT',
            'rules' => 'Booking rules',
            'generated' => 'Generated on',
            'print' => 'Print / Save as PDF',
        ]
        : [
            'title' => 'Conférences & Lounge des Technologies',
            'subtitle' => 'Refrigair Expo 2027 · du 2 au 4 février 2027 · OFEC Casablanca',
            'time' => 'Horaire',
            'space' => 'Espace',
            'code' => 'Code',
            'exhibitor' => 'Exposant',
            'status' => 'Statut',
            'price' => 'Prix HT',
            'rules' => 'Règles de réservation',
            'generated' => 'Généré le',
            'print' => 'Imprimer / Enregistrer en PDF',
        ];

    $price = fn ($value) => $en
        ? '€'.number_format((float) $value, 0, '.', ',')
        : number_format((float) $value, 0, ',', ' ').' €';
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <title>{{ $t['title'] }} – Refrigair Expo 2027</title>
    <style>
        @page { size: A4 portrait; margin: 12mm; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9.5px; color: #0F2347; margin: 0; }
        .wrap { margin: 0 auto; padding: 0 4px; }
        .brand-header { width: 100%; margin: 0 0 12px; border-collapse: collapse; border-bottom: 3px solid #2563EB; }
        .brand-header td { border: 0; padding: 0 0 10px; vertical-align: middle; }
        .brand-logo-cell { width: 175px; padding: 4px 8px 4px 0 !important; background: #fff; border-right: 1px solid #E2E8F0; }
        .brand-logo { display: block; width: 165px; height: auto; }
        h1 { font-size: 17px; margin: 0 0 3px; color: #0F2347; }
        .subtitle { color: #475569; font-size: 8px; margin: 0; }
        .document-meta { text-align: right; color: #64748B; font-size: 7px; line-height: 1.6; }
        .summary { width: 100%; margin: 0 0 12px; border-collapse: separate; border-spacing: 6px 0; }
        .summary td { border: 0; border-radius: 5px; padding: 7px 9px; background: #F1F6FD; color: #0F2347; font-size: 9px; }
        .summary strong { color: #2563EB; font-size: 13px; }
        h2 { font-size: 13px; margin: 18px 0 6px; padding: 6px 8px; background: #EFF6FF; color: #1D4ED8; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        th { text-align: left; font-size: 7px; text-transform: uppercase; color: #64748B; border-bottom: 1px solid #CBD5E1; padding: 4px 4px; }
        td { border-bottom: 1px solid #E2E8F0; padding: 4px 4px; vertical-align: top; }
        td.price { text-align: right; white-space: nowrap; }
        th.price { text-align: right; }
        .booked { color: #1D4ED8; font-weight: bold; }
        .available { color: #047857; font-weight: bold; }
        .break td { color: #94A3B8; font-style: italic; text-align: center; background: #F8FAFC; }
        .muted { color: #94A3B8; }
        ul.rules { padding-left: 16px; margin: 6px 0 0; }
        ul.rules li { margin-bottom: 4px; line-height: 1.45; }
        .footer { margin-top: 18px; font-size: 9px; color: #94A3B8; }
        .print-bar { margin: 16px 0; text-align: right; }
        .print-bar button { font: inherit; font-size: 13px; padding: 8px 14px; border: 0; border-radius: 8px; background: #2563EB; color: #fff; cursor: pointer; }
        @media print { .print-bar { display: none; } }
    </style>
</head>
<body>
<div class="wrap">
    @if ($printable)
        <div class="print-bar"><button type="button" onclick="window.print()">{{ $t['print'] }}</button></div>
    @endif

    <table class="brand-header">
        <tr>
            <td class="brand-logo-cell">
                @if ($logoDataUri)
                    <img class="brand-logo" src="{{ $logoDataUri }}" alt="Refrigair Expo 2027">
                @endif
            </td>
            <td style="padding-left: 14px">
                <h1>{{ $t['title'] }}</h1>
                <p class="subtitle">{{ $t['subtitle'] }}</p>
            </td>
            <td class="document-meta">
                REFRIGAIR EXPO 2027<br>
                {{ $t['generated'] }} {{ $generatedAt->format($en ? 'm/d/Y H:i' : 'd/m/Y H:i') }}
            </td>
        </tr>
    </table>

    <table class="summary" aria-label="Planning summary">
        <tr>
            <td><strong>{{ $totalSlots }}</strong><br>{{ $en ? 'Total time slots' : 'Créneaux au total' }}</td>
            <td><strong>{{ $bookedSlots }}</strong><br>{{ $en ? 'Booked' : 'Réservés / options' }}</td>
            <td><strong>{{ $availableSlots }}</strong><br>{{ $en ? 'Available' : 'Disponibles' }}</td>
        </tr>
    </table>

    @foreach ($days as $date => $slots)
        @php $day = \Illuminate\Support\Carbon::parse($date)->locale($locale); @endphp

        <h2>{{ ucfirst($day->isoFormat($en ? 'dddd, MMMM D, YYYY' : 'dddd D MMMM YYYY')) }}</h2>

        <table>
            <thead>
                <tr>
                    <th style="width: 15%">{{ $t['time'] }}</th>
                    <th style="width: 20%">{{ $t['space'] }}</th>
                    <th style="width: 9%">{{ $t['code'] }}</th>
                    <th>{{ $t['exhibitor'] }}</th>
                    <th style="width: 12%">{{ $t['status'] }}</th>
                    <th class="price" style="width: 12%">{{ $t['price'] }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($slots->groupBy('space_id') as $spaceSlots)
                    @foreach ($spaceSlots->values() as $index => $slot)
                        @if ($index > 0 && $spaceSlots->values()[$index - 1]->end_label !== $slot->start_label)
                            <tr class="break">
                                <td colspan="6">{{ $en ? 'Break' : 'Pause' }} {{ $spaceSlots->values()[$index - 1]->end_label }} – {{ $slot->start_label }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td>{{ $slot->start_label }} – {{ $slot->end_label }}</td>
                            <td>{{ $en ? $slot->space->type->labelEn() : $slot->space->type->label() }}</td>
                            <td>{{ $slot->code }}</td>
                            <td>
                                @if ($slot->status->isTaken() && $slot->holder_name)
                                    {{ $slot->holder_name }}
                                    @if ($slot->title)
                                        <br><span class="muted">{{ $slot->title }}</span>
                                    @endif
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                            <td class="{{ $slot->status->isTaken() ? 'booked' : 'available' }}">{{ $slot->status->publicLabel($locale) }}</td>
                            <td class="price">{{ $price($slot->price_excl_tax) }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    @endforeach

    <h2>{{ $t['rules'] }}</h2>
    <ul class="rules">
        @foreach ($rules as $rule)
            <li>{{ $rule }}</li>
        @endforeach
    </ul>

    <p class="footer">{{ $en ? 'Internal planning document · Refrigair Expo 2027' : 'Document de planning · REFRIGAIR EXPO 2027' }}</p>
</div>
</body>
</html>
