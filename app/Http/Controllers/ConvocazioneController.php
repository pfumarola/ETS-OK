<?php

namespace App\Http\Controllers;

use App\Models\Convocazione;
use App\Models\Settings;
use App\Services\ConvocazioneRecipientService;
use App\Support\PdfLetterheadData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ConvocazioneController extends Controller
{
    public function __construct(private readonly ConvocazioneRecipientService $recipientService)
    {
        $this->middleware('role:admin,segreteria');
    }

    public function index(Request $request)
    {
        $query = Convocazione::query()->with(['createdBy:id,name', 'sentBy:id,name'])->orderByDesc('scheduled_at');

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->input('tipo'));
        }
        if ($request->filled('stato')) {
            $query->where('stato', $request->input('stato'));
        }

        return Inertia::render('Convocazioni/Index', [
            'convocazioni' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['tipo', 'stato']),
            'tipoOptions' => $this->tipoOptions(),
            'statoOptions' => $this->statoOptions(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Convocazioni/Create', [
            'tipoOptions' => $this->tipoOptions(),
            'templateConfig' => config('convocazioni.templates', []),
            'defaultValues' => [
                'scheduled_at' => now()->format('Y-m-d\TH:i'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);

        $convocazione = Convocazione::create([
            ...$validated,
            'testo_email' => $this->renderTemplate($validated),
            'stato' => Convocazione::STATO_BOZZA,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('convocazioni.show', $convocazione)
            ->with('flash', ['type' => 'success', 'message' => 'Convocazione creata in bozza.']);
    }

    public function show(Convocazione $convocazione)
    {
        $recipients = $this->recipientService->recipientsForTipo($convocazione->tipo);

        return Inertia::render('Convocazioni/Show', [
            'convocazione' => $convocazione->load(['createdBy:id,name', 'sentBy:id,name']),
            'recipientSummary' => $this->recipientSummary($recipients),
        ]);
    }

    public function edit(Convocazione $convocazione)
    {
        if (! $convocazione->isBozza()) {
            return redirect()->route('convocazioni.show', $convocazione)
                ->with('flash', ['type' => 'error', 'message' => 'Le convocazioni inviate non sono modificabili.']);
        }

        return Inertia::render('Convocazioni/Edit', [
            'convocazione' => $convocazione,
            'tipoOptions' => $this->tipoOptions(),
            'templateConfig' => config('convocazioni.templates', []),
        ]);
    }

    public function update(Request $request, Convocazione $convocazione)
    {
        if (! $convocazione->isBozza()) {
            return redirect()->route('convocazioni.show', $convocazione)
                ->with('flash', ['type' => 'error', 'message' => 'Le convocazioni inviate non sono modificabili.']);
        }

        $validated = $this->validatePayload($request);

        $convocazione->update([
            ...$validated,
            'testo_email' => $this->renderTemplate($validated),
        ]);

        return redirect()->route('convocazioni.show', $convocazione)
            ->with('flash', ['type' => 'success', 'message' => 'Convocazione aggiornata.']);
    }

    public function send(Convocazione $convocazione)
    {
        if (! $convocazione->isBozza()) {
            return redirect()->route('convocazioni.show', $convocazione)
                ->with('flash', ['type' => 'error', 'message' => 'Convocazione già inviata.']);
        }

        $recipients = $this->recipientService->recipientsForTipo($convocazione->tipo);
        $emails = $recipients['emails'];

        if ($emails === []) {
            return redirect()->route('convocazioni.show', $convocazione)
                ->with('flash', ['type' => 'error', 'message' => 'Nessun destinatario con email valida per questa convocazione.']);
        }

        try {
            foreach (array_chunk($emails, 50) as $chunk) {
                Mail::html($convocazione->testo_email ?? '', function ($message) use ($chunk, $convocazione): void {
                    $message->to($chunk)->subject($this->subjectFor($convocazione));
                });
            }
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('convocazioni.show', $convocazione)
                ->with('flash', ['type' => 'error', 'message' => 'Errore nell\'invio: ' . $e->getMessage()]);
        }

        $convocazione->update([
            'stato' => Convocazione::STATO_INVIATA,
            'sent_at' => now(),
            'sent_by' => request()->user()->id,
        ]);

        return redirect()->route('convocazioni.show', $convocazione)
            ->with('flash', ['type' => 'success', 'message' => 'Convocazione inviata a ' . count($emails) . ' destinatari.']);
    }

    public function downloadPdf(Convocazione $convocazione)
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('convocazioni.pdf', [
            'convocazione' => $convocazione,
            'letterhead' => PdfLetterheadData::data(),
        ])->setOption('enable_php', true);

        $filename = 'convocazione-' . Str::slug($convocazione->tipo) . '-' . $convocazione->scheduled_at?->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'tipo' => 'required|in:' . implode(',', [Convocazione::TIPO_ASSEMBLEA, Convocazione::TIPO_CONSIGLIO]),
            'titolo' => 'nullable|string|max:255',
            'scheduled_at' => 'required|date',
            'luogo' => 'required|string|max:255',
            'ordine_del_giorno' => 'required|string|max:20000',
        ]);
    }

    private function renderTemplate(array $data): string
    {
        $scheduled = \Carbon\Carbon::parse($data['scheduled_at']);
        $tipo = $data['tipo'];
        $template = config("convocazioni.templates.$tipo.body", '');
        $odgHtml = (string) ($data['ordine_del_giorno'] ?? '');
        $titolo = trim((string) ($data['titolo'] ?? ''));

        $replacements = [
            '{{tipo_label}}' => $tipo === Convocazione::TIPO_ASSEMBLEA ? 'Assemblea' : 'Consiglio direttivo',
            '{{titolo}}' => e($titolo !== '' ? $titolo : $this->defaultTitle($tipo, $scheduled)),
            '{{data}}' => $scheduled->format('d/m/Y'),
            '{{ora}}' => $scheduled->format('H:i'),
            '{{luogo}}' => e((string) $data['luogo']),
            '{{ordine_del_giorno}}' => $odgHtml,
            '{{nome_associazione}}' => e((string) Settings::get('nome_associazione', config('app.name'))),
        ];

        return strtr((string) $template, $replacements);
    }

    private function subjectFor(Convocazione $convocazione): string
    {
        $title = trim((string) $convocazione->titolo);
        if ($title !== '') {
            return $title;
        }

        return $this->defaultTitle($convocazione->tipo, $convocazione->scheduled_at);
    }

    private function defaultTitle(string $tipo, ?\Carbon\CarbonInterface $scheduled): string
    {
        $label = $tipo === Convocazione::TIPO_ASSEMBLEA ? 'Convocazione assemblea' : 'Convocazione consiglio direttivo';

        if (! $scheduled) {
            return $label;
        }

        return $label . ' del ' . $scheduled->format('d/m/Y');
    }

    private function recipientSummary(array $recipients): array
    {
        return [
            'total' => $recipients['total'],
            'with_email' => $recipients['with_email'],
            'without_email' => $recipients['without_email'],
        ];
    }

    private function tipoOptions(): array
    {
        return [
            ['value' => Convocazione::TIPO_ASSEMBLEA, 'label' => 'Assemblea'],
            ['value' => Convocazione::TIPO_CONSIGLIO, 'label' => 'Consiglio direttivo'],
        ];
    }

    private function statoOptions(): array
    {
        return [
            ['value' => Convocazione::STATO_BOZZA, 'label' => 'Bozza'],
            ['value' => Convocazione::STATO_INVIATA, 'label' => 'Inviata'],
        ];
    }
}
