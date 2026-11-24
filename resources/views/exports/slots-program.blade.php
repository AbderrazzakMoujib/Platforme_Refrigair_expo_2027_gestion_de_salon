@php
    $en = $locale === 'en';
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
        @page { margin: 18mm 14mm; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10.5px; color: #0F2347; margin: 0; }
        .wrap { max-width: 900px; margin: 0 auto; padding: 0 4px; }
        h1 { font-size: 20px; margin: 0 0 2px; }
        .subtitle { color: #475569; font-size: 11px; margin: 0 0 16px; }
        h2 { font-size: 13px; margin: 18px 0 6px; padding: 6px 8px; background: #EFF6FF; color: #1D4ED8; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 9.5px; text-transform: uppercase; color: #64748B; border-bottom: 1px solid #CBD5E1; padding: 5px 6px; }
        td { border-bottom: 1px solid #E2E8F0; padding: 5px 6px; vertical-align: top; }
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

    <h1>{{ $t['title'] }}</h1>
    <p class="subtitle">{{ $t['subtitle'] }}</p>

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

    <p class="footer">{{ $t['generated'] }} {{ $generatedAt->format($en ? 'm/d/Y H:i' : 'd/m/Y H:i') }}</p>
</div>
</body>
</html>
