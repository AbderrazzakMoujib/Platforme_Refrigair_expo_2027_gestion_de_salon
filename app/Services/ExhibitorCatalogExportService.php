<?php

namespace App\Services;

use App\Models\Exhibitor;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ExhibitorCatalogExportService
{
    public function xlsx(Collection $exhibitors): string
    {
        $rows = [['Raison sociale', 'Description de l’entreprise', 'Site web', 'E-mail professionnel', 'Téléphone professionnel', 'Nom du fichier logo', 'Commercial responsable', 'État de la fiche', 'Dernière mise à jour']];
        foreach ($exhibitors as $exhibitor) {
            $profile = $exhibitor->catalogProfile;
            $rows[] = [$exhibitor->company_name, $profile?->company_description, $profile?->website,
                $profile?->email, $profile?->phone,
                $profile?->logo_path && Storage::disk('local')->exists($profile->logo_path) ? $this->logoFilename($exhibitor) : '',
                $exhibitor->commercial?->name, $profile?->status === 'completed' ? 'Complétée' : 'Brouillon',
                $profile?->updated_at?->format('d/m/Y H:i')];
        }

        $sheetRows = '';
        foreach ($rows as $r => $cells) {
            $sheetRows .= '<row r="'.($r + 1).'">';
            foreach ($cells as $c => $value) {
                $ref = $this->column($c + 1).($r + 1);
                $style = $r === 0 ? ' s="1"' : (($c === 1) ? ' s="2"' : '');
                $sheetRows .= '<c r="'.$ref.'" t="inlineStr"'.$style.'><is><t xml:space="preserve">'.$this->xml((string) ($value ?? '')).'</t></is></c>';
            }
            $sheetRows .= '</row>';
        }
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews><cols><col min="1" max="1" width="30" customWidth="1"/><col min="2" max="2" width="55" customWidth="1"/><col min="3" max="5" width="26" customWidth="1"/><col min="6" max="6" width="38" customWidth="1"/><col min="7" max="9" width="24" customWidth="1"/></cols><sheetData>'.$sheetRows.'</sheetData><autoFilter ref="A1:I'.count($rows).'"/></worksheet>';

        $path = tempnam(sys_get_temp_dir(), 'catalog-xlsx-');
        $zip = new ZipArchive();
        if ($path === false || $zip->open($path, ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Impossible de créer le fichier Excel.');
        }
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Fiches catalogue" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>');
        $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Calibri"/></font></fonts><fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF0F2347"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="3"><xf xfId="0"/><xf xfId="0" fontId="1" fillId="2" applyFont="1" applyFill="1"/><xf xfId="0" applyAlignment="1"><alignment wrapText="1" vertical="top"/></xf></cellXfs></styleSheet>');
        $zip->addFromString('xl/worksheets/sheet1.xml', $xml);
        $zip->close();
        return $path;
    }

    public function logoFilename(Exhibitor $exhibitor): string
    {
        return Str::slug($exhibitor->company_name ?: 'exposant').'-'.$exhibitor->id.'.'.strtolower(pathinfo($exhibitor->catalogProfile->logo_path, PATHINFO_EXTENSION));
    }

    private function column(int $number): string
    {
        $name = '';
        while ($number > 0) { $number--; $name = chr(65 + $number % 26).$name; $number = intdiv($number, 26); }
        return $name;
    }

    private function xml(string $value): string
    {
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $value) ?? '';
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
