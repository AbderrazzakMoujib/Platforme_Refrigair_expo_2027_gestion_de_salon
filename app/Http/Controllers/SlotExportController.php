<?php

namespace App\Http\Controllers;

use App\Models\Slot;
use App\Models\Space;
use App\Support\SlotCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Exports du programme : PDF (FR / EN) et CSV compatible Excel. */
class SlotExportController extends Controller
{
    private const PDF_FACADE = 'Barryvdh\\DomPDF\\Facade\\Pdf';

    public function pdf(Request $request, string $locale): Response
    {
        Gate::authorize('view', Slot::class);

        $data = [
            'locale' => $locale,
            'days' => Slot::query()
                ->with(['space:id,type,name', 'exhibitor:id,company_name'])
                ->ordered()
                ->get()
                ->groupBy(fn (Slot $slot) => $slot->date->toDateString()),
            'spaces' => Space::query()->orderBy('id')->get(),
            'rules' => SlotCatalog::rules($locale),
            'generatedAt' => now(),
            'printable' => false,
        ];

        // dompdf (barryvdh/laravel-dompdf) si installé ; sinon page HTML imprimable.
        if (class_exists(self::PDF_FACADE)) {
            $pdf = self::PDF_FACADE;

            return $pdf::loadView('exports.slots-program', $data)
                ->setPaper('a4')
                ->download("programme-conferences-lounge-refrigair-2027-{$locale}.pdf");
        }

        return response()->view('exports.slots-program', ['printable' => true] + $data);
    }

    public function csv(): StreamedResponse
    {
        Gate::authorize('view', Slot::class);

        $slots = Slot::query()
            ->with(['space:id,type,name', 'exhibitor:id,company_name'])
            ->ordered()
            ->get();

        return response()->streamDownload(function () use ($slots): void {
            $out = fopen('php://output', 'wb');

            // BOM UTF-8 + séparateur « ; » : ouverture directe dans Excel (paramètres FR).
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['Code', 'Espace', 'Date', 'Début', 'Fin', 'Prix HT (EUR)', 'Statut', 'Exposant', 'Titre', 'Intervenants', 'Support envoyé', 'Notes', 'Type de titulaire', 'Contact', 'E-mail contact', 'Téléphone contact'], ';', '"', '');

            foreach ($slots as $slot) {
                fputcsv($out, [
                    $slot->code,
                    $slot->space->type->label(),
                    $slot->date->format('d/m/Y'),
                    $slot->start_label,
                    $slot->end_label,
                    number_format((float) $slot->price_excl_tax, 2, ',', ''),
                    $slot->status->label(),
                    $this->safeCell($slot->holder_name),
                    $this->safeCell($slot->title),
                    $this->safeCell($slot->speakers),
                    $slot->support_path ? 'Oui' : 'Non',
                    $this->safeCell($slot->notes),
                    $slot->exhibitor_id ? 'Exposant' : ($slot->is_external ? 'Non exposant' : ''),
                    $this->safeCell($slot->contact_name),
                    $this->safeCell($slot->contact_email),
                    $this->safeCell($slot->contact_phone),
                ], ';', '"', '');
            }

            fclose($out);
        }, 'creneaux-conferences-lounge-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Neutralise les textes saisis par les utilisateurs qui seraient interprétés
     * comme des formules par Excel (=, +, -, @, tabulation, retour chariot).
     */
    private function safeCell(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        return in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true) ? "'".$value : $value;
    }
}
