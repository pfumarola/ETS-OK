<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Document;
use App\Models\Template;
use App\Services\AttachmentService;
use App\Support\PdfLetterheadData;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

class DocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin,segreteria');
    }

    public function index(Request $request)
    {
        $documents = Document::query()
            ->orderByDesc('data')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Documents/Index', [
            'documents' => $documents,
        ]);
    }

    public function create()
    {
        return Inertia::render('Documents/Create', [
            'templates' => Template::where('categoria', 'documento')->orderBy('nome')->get(['id', 'nome', 'contenuto']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titolo' => 'required|string|max:255',
            'data' => 'required|date',
            'contenuto' => 'nullable|string',
        ]);

        $document = Document::create($validated);

        return redirect()->route('documents.show', $document)
            ->with('flash', ['type' => 'success', 'message' => 'Documento creato.']);
    }

    public function show(Document $document)
    {
        $document->load('attachments');

        return Inertia::render('Documents/Show', [
            'document' => $document,
            'uploadMaxFileSizeHuman' => self::uploadMaxFileSizeHuman(),
        ]);
    }

    public function edit(Document $document)
    {
        return Inertia::render('Documents/Edit', [
            'document' => $document,
            'templates' => Template::where('categoria', 'documento')->orderBy('nome')->get(['id', 'nome', 'contenuto']),
        ]);
    }

    public function update(Request $request, Document $document)
    {
        $validated = $request->validate([
            'titolo' => 'required|string|max:255',
            'data' => 'required|date',
            'contenuto' => 'nullable|string',
        ]);

        $document->update($validated);

        return redirect()->route('documents.show', $document)
            ->with('flash', ['type' => 'success', 'message' => 'Documento aggiornato.']);
    }

    public function destroy(Document $document)
    {
        $document->delete();

        return redirect()->route('documents.index')
            ->with('flash', ['type' => 'success', 'message' => 'Documento eliminato.']);
    }

    public function downloadPdf(Document $document)
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('documents.pdf', [
            'document' => $document,
            'letterhead' => PdfLetterheadData::data(),
        ]);

        $safeTitle = preg_replace('/[^a-zA-Z0-9_\-\s]/', '', $document->titolo ?? '') ?: 'documento';
        $slug = Str::slug(mb_substr($safeTitle, 0, 50));
        $filename = 'documento-' . $slug . '-' . $document->id . '.pdf';

        return $pdf->download($filename);
    }

    public function storeAttachment(Request $request, Document $document, AttachmentService $attachmentService)
    {
        $maxKb = (int) floor(UploadedFile::getMaxFilesize() / 1024);
        $appMaxKb = 10240;
        $limitKb = $maxKb > 0 ? min($appMaxKb, $maxKb) : $appMaxKb;

        $request->validate([
            'file' => 'required|file|max:'.$limitKb.'|mimes:pdf,jpg,jpeg,png,gif,doc,docx,xls,xlsx',
        ], [
            'file.required' => 'Seleziona un file da caricare.',
            'file.max' => 'Il file non deve superare '.self::uploadMaxFileSizeHuman().' (limite del server).',
            'file.mimes' => 'Formato non consentito. Usa PDF, immagini, Word o Excel.',
        ]);

        $file = $request->file('file');
        if ($file->getError() !== \UPLOAD_ERR_OK) {
            $message = match ($file->getError()) {
                \UPLOAD_ERR_INI_SIZE, \UPLOAD_ERR_FORM_SIZE => 'Il file è troppo grande per le impostazioni del server.',
                \UPLOAD_ERR_PARTIAL => 'Il file è stato caricato solo in parte. Riprova.',
                default => 'Errore durante l\'upload del file. Riprova.',
            };

            return redirect()->back()->with('flash', ['type' => 'error', 'message' => $message]);
        }

        try {
            $attachmentService->store($file, $document);
        } catch (\Throwable $e) {
            report($e);
            Log::error('Upload allegato documento fallito', [
                'document_id' => $document->id,
                'exception' => $e->getMessage(),
            ]);

            return redirect()->back()->with('flash', ['type' => 'error', 'message' => 'Caricamento non riuscito. Riprova o contatta l\'assistenza.']);
        }

        return redirect()->back()->with('flash', ['type' => 'success', 'message' => 'Allegato caricato.']);
    }

    public function destroyAttachment(Document $document, Attachment $attachment)
    {
        if ($attachment->attachable_type !== Document::class || (int) $attachment->attachable_id !== (int) $document->id) {
            abort(404, 'Allegato non trovato su questo documento.');
        }

        $attachment->delete();

        return redirect()->back()->with('flash', ['type' => 'success', 'message' => 'Allegato rimosso.']);
    }

    private static function uploadMaxFileSizeHuman(): string
    {
        $bytes = (int) UploadedFile::getMaxFilesize();
        if ($bytes >= 1024 * 1024) {
            return round($bytes / (1024 * 1024), 1) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }

        return $bytes . ' B';
    }
}
