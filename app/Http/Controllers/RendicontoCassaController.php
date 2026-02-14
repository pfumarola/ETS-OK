<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Settings;
use App\Services\RendicontoCassaService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rendiconto economico per cassa (Modello D). Visualizzazione e export PDF.
 */
class RendicontoCassaController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin,contabile');
    }

    public function index(Request $request, RendicontoCassaService $service)
    {
        $anno = (int) $request->get('anno', now()->year);
        $anno = $anno >= 2000 && $anno <= 2100 ? $anno : (int) now()->year;

        $data = $service->buildRendiconto($anno);

        return Inertia::render('Reports/RendicontoCassa', [
            'rendiconto' => $data,
            'anno' => $anno,
        ]);
    }

    /**
     * Export PDF del rendiconto per cassa (Modello D).
     */
    public function exportPdf(Request $request, RendicontoCassaService $service): Response
    {
        $anno = (int) $request->get('anno', now()->year);
        $anno = $anno >= 2000 && $anno <= 2100 ? $anno : (int) now()->year;

        $data = $service->buildRendiconto($anno);
        $nomeAssociazione = Settings::get('nome_associazione', 'Associazione - Ente del Terzo Settore');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('rendiconto_cassa.template', [
            'rendiconto' => $data,
            'nome_associazione' => $nomeAssociazione,
            'logo_data_uri' => Attachment::logoDataUriForPdf(),
        ]);

        $filename = 'rendiconto_cassa_' . $anno . '.pdf';

        return $pdf->download($filename);
    }
}
