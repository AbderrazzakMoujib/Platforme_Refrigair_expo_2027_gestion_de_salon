
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Badge {{ $badge->reference }}</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: #f1f5f9;
            font-family: Arial, sans-serif;
            color: #0f2347;
        }

        .toolbar {
            padding: 20px;
            text-align: center;
        }

        .toolbar button {
            border: 0;
            border-radius: 10px;
            background: #2563eb;
            color: white;
            font-weight: bold;
            cursor: pointer;
            padding: 12px 24px;
        }

        .badge-card {
            width: 90mm;
            height: 130mm;
            margin: 20px auto;
            background: white;
            border: 1px solid #ddd;
            border-radius: 12px;
            overflow: hidden;
            text-align: center;
            display: flex;
            flex-direction: column;
        }

        .badge-header {
            min-height: 30mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #0f2347;
            color: white;
            padding: 12px;
        }

        .badge-header h1 {
            font-size: 20px;
            margin: 0;
        }

        .badge-header p {
            margin: 7px 0 0;
            font-size: 12px;
            letter-spacing: 1px;
        }

        .badge-body {
            padding: 18px 10px;
            flex: 1;
        }

        .person-name {
            font-size: 23px;
            line-height: 1.2;
            overflow-wrap: anywhere;
            font-weight: 800;
            margin: 12px 0;
            text-transform: uppercase;
        }

        .function {
            color: #2563eb;
            font-weight: bold;
            font-size: 14px;
            overflow-wrap: anywhere;
        }

        .company {
            font-size: 17px;
            font-weight: bold;
            margin: 15px 0;
            overflow-wrap: anywhere;
        }

        .qr {
            width: 34mm;
            height: 34mm;
            display: block;
            margin: 10px auto;
        }

        .badge-footer {
            background: #eaf0f9;
            padding: 10px;
            font-size: 11px;
            font-weight: bold;
        }

        @page {
            size: 90mm 130mm;
            margin: 0;
        }

        @media print {
            body { background: white; }
            .toolbar { display: none; }
            .badge-card {
                margin: 0;
                border: 0;
                border-radius: 0;
                break-inside: avoid;
            }
        }
    </style>
</head>

<body>
    <div class="toolbar">
        <button type="button" onclick="window.print()">
            Imprimer / Enregistrer en PDF
        </button>
    </div>

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

            <img src="{{ $qrImage }}"
                 class="qr"
                 alt="QR Code de vérification">

            <p style="font-size:11px">
                {{ $badge->reference }}
            </p>
        </main>

        <footer class="badge-footer">
            OFEC CASABLANCA · MAROC
        </footer>
    </article>
</body>
</html>
