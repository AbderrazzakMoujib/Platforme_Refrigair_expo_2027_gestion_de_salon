
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Impression multiple — Refrigair Expo</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 20px;
            background: #f1f5f9;
            font-family: Arial, sans-serif;
            color: #0f2347;
        }

        .toolbar {
            text-align: center;
            margin-bottom: 20px;
        }

        button {
            padding: 12px 24px;
            background: #2563eb;
            border: 0;
            border-radius: 10px;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }

        .badge-card {
            width: 90mm;
            height: 130mm;
            background: white;
            margin: 0 auto 20px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            page-break-after: always;
            break-after: page;
        }

        .badge-header {
            background: #0f2347;
            color: white;
            min-height: 30mm;
            padding: 20px 10px;
        }

        .badge-header h1 {
            margin: 0;
            font-size: 20px;
        }

        .badge-header p {
            font-size: 12px;
        }

        .badge-body {
            flex: 1;
            padding: 15px;
        }

        .person-name {
            font-size: 22px;
            font-weight: 800;
            overflow-wrap: anywhere;
            text-transform: uppercase;
        }

        .function {
            color: #2563eb;
            font-size: 14px;
            font-weight: bold;
            overflow-wrap: anywhere;
        }

        .company {
            margin-top: 16px;
            font-size: 17px;
            font-weight: bold;
            overflow-wrap: anywhere;
        }

        .qr {
            width: 34mm;
            height: 34mm;
            margin: 14px auto;
            display: block;
        }

        .badge-footer {
            background: #eaf0f9;
            padding: 12px;
            font-size: 11px;
            font-weight: bold;
        }

        @page {
            size: 90mm 130mm;
            margin: 0;
        }

        @media print {
            body {
                padding: 0;
                background: white;
            }

            .toolbar { display: none; }

            .badge-card {
                margin: 0;
                border: 0;
                border-radius: 0;
            }

            .badge-card:last-child {
                page-break-after: auto;
                break-after: auto;
            }
        }
    </style>
</head>

<body>

    <div class="toolbar">
        <p>{{ $items->count() }} badge(s) sélectionné(s)</p>
        <button type="button" onclick="window.print()">
            Imprimer tous les badges / PDF
        </button>
    </div>

    @foreach ($items as $item)
        @php($badge = $item['badge'])

        <article class="badge-card">
            <header class="badge-header">
                <h1>REFRIGAIR EXPO</h1>
                <p>2–4 FÉVRIER 2027</p>
            </header>

            <main class="badge-body">
                <p class="person-name">
                    {{ $badge->full_name }}
                </p>

                <p class="function">
                    {{ $badge->function }}
                </p>

                <p class="company">
                    {{ $badge->exhibitor->company_name }}
                </p>

                <img src="{{ $item['qrImage'] }}"
                     class="qr"
                     alt="QR Code de {{ $badge->full_name }}">

                <p style="font-size:11px">
                    {{ $badge->reference }}
                </p>
            </main>

            <footer class="badge-footer">
                OFEC CASABLANCA · MAROC
            </footer>
        </article>
    @endforeach

</body>
</html>
