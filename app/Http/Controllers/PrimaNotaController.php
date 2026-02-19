<?php

namespace App\Http\Controllers;

use App\Models\Conto;
use App\Models\PrimaNotaEntry;
use App\Services\RendicontoCassaSchema;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Prima nota: elenco movimenti e creazione manuale. Voci da schema MOD_D (hardcoded).
 */
class PrimaNotaController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin,contabile');
    }

    public function index(Request $request)
    {
        $query = PrimaNotaEntry::query();

        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->to);
        }
        if ($request->filled('rendiconto_code')) {
            $query->where('rendiconto_code', $request->rendiconto_code);
        }

        $entries = $query->with('conto')->orderByDesc('date')->orderByDesc('id')->paginate(30)->withQueryString();
        $rendicontoVoci = RendicontoCassaSchema::getSelectableVoices();
        $macroAreas = RendicontoCassaSchema::getMacroAreasForSelect();

        return Inertia::render('PrimaNota/Index', [
            'entries' => $entries,
            'rendicontoVoci' => $rendicontoVoci,
            'macroAreas' => $macroAreas,
            'filters' => $request->only('from', 'to', 'rendiconto_code'),
        ]);
    }

    public function create()
    {
        $conti = Conto::attivi()->ordered()->get(['id', 'name', 'code', 'type']);
        if ($conti->isEmpty()) {
            return redirect()->route('prima-nota.index')
                ->with('flash', ['type' => 'error', 'message' => 'Crea almeno un conto tesoreria prima di registrare movimenti.']);
        }
        return Inertia::render('PrimaNota/Create', [
            'rendicontoVoci' => RendicontoCassaSchema::getSelectableVoices(),
            'macroAreas' => RendicontoCassaSchema::getMacroAreasForSelect(),
            'conti' => $conti,
        ]);
    }

    public function store(Request $request)
    {
        $validCodes = RendicontoCassaSchema::getValidCodes();
        $request->validate([
            'conto_id' => 'required|exists:conti,id',
            'rendiconto_code' => 'required|string|in:' . implode(',', $validCodes),
            'date' => 'required|date',
            'amount' => 'required|numeric',
            'description' => 'nullable|string|max:255',
            'gestione' => 'nullable|in:istituzionale,commerciale',
            'competenza_cassa' => 'boolean',
        ]);

        $info = RendicontoCassaSchema::getInfoByCode($request->rendiconto_code);
        $amount = (float) $request->amount;
        if ($info) {
            if ($info['tipo'] === 'entrata' && $amount < 0) {
                return redirect()->back()->withInput()->withErrors([
                    'amount' => 'Per una voce di entrata l\'importo deve essere positivo.',
                ]);
            }
            if ($info['tipo'] === 'uscita' && $amount > 0) {
                return redirect()->back()->withInput()->withErrors([
                    'amount' => 'Per una voce di uscita l\'importo deve essere negativo.',
                ]);
            }
        }

        PrimaNotaEntry::create($request->only('conto_id', 'rendiconto_code', 'date', 'amount', 'description', 'gestione') + [
            'competenza_cassa' => $request->boolean('competenza_cassa', true),
        ]);

        return redirect()->route('prima-nota.index')->with('flash', ['type' => 'success', 'message' => 'Movimento registrato.']);
    }

    public function edit(PrimaNotaEntry $prima_nota_entry)
    {
        $conti = Conto::attivi()->ordered()->get(['id', 'name', 'code', 'type']);
        return Inertia::render('PrimaNota/Edit', [
            'entry' => $prima_nota_entry,
            'rendicontoVoci' => RendicontoCassaSchema::getSelectableVoices(),
            'macroAreas' => RendicontoCassaSchema::getMacroAreasForSelect(),
            'conti' => $conti,
        ]);
    }

    public function update(Request $request, PrimaNotaEntry $prima_nota_entry)
    {
        $validCodes = RendicontoCassaSchema::getValidCodes();
        $request->validate([
            'conto_id' => 'required|exists:conti,id',
            'rendiconto_code' => 'required|string|in:' . implode(',', $validCodes),
            'date' => 'required|date',
            'amount' => 'required|numeric',
            'description' => 'nullable|string|max:255',
            'gestione' => 'nullable|in:istituzionale,commerciale',
            'competenza_cassa' => 'boolean',
        ]);

        $info = RendicontoCassaSchema::getInfoByCode($request->rendiconto_code);
        $amount = (float) $request->amount;
        if ($info) {
            if ($info['tipo'] === 'entrata' && $amount < 0) {
                return redirect()->back()->withInput()->withErrors([
                    'amount' => 'Per una voce di entrata l\'importo deve essere positivo.',
                ]);
            }
            if ($info['tipo'] === 'uscita' && $amount > 0) {
                return redirect()->back()->withInput()->withErrors([
                    'amount' => 'Per una voce di uscita l\'importo deve essere negativo.',
                ]);
            }
        }

        $prima_nota_entry->update($request->only('conto_id', 'rendiconto_code', 'date', 'amount', 'description', 'gestione') + [
            'competenza_cassa' => $request->boolean('competenza_cassa', true),
        ]);

        return redirect()->route('prima-nota.index')->with('flash', ['type' => 'success', 'message' => 'Movimento aggiornato.']);
    }

    public function createGiroconto()
    {
        $conti = Conto::attivi()->ordered()->get(['id', 'name', 'code', 'type']);
        if ($conti->count() < 2) {
            return redirect()->route('prima-nota.index')
                ->with('flash', ['type' => 'error', 'message' => 'Servono almeno due conti attivi per eseguire un giroconto.']);
        }
        return Inertia::render('PrimaNota/Giroconto', ['conti' => $conti]);
    }

    public function storeGiroconto(Request $request)
    {
        $conti = Conto::attivi()->ordered()->get(['id', 'name']);
        $validIds = $conti->pluck('id')->toArray();
        $request->validate([
            'conto_da_id' => 'required|in:' . implode(',', $validIds),
            'conto_a_id' => 'required|in:' . implode(',', $validIds),
            'date' => 'required|date',
            'importo' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);
        $contoDaId = (int) $request->conto_da_id;
        $contoAId = (int) $request->conto_a_id;
        if ($contoDaId === $contoAId) {
            return redirect()->back()->withInput()->withErrors([
                'conto_a_id' => 'Il conto di destinazione deve essere diverso dal conto di partenza.',
            ]);
        }
        $contoDa = $conti->firstWhere('id', $contoDaId);
        $contoA = $conti->firstWhere('id', $contoAId);
        $importo = (float) $request->importo;
        $data = $request->date;
        $desc = $request->filled('description')
            ? $request->description
            : 'Giroconto da ' . $contoDa->name . ' verso ' . $contoA->name;

        PrimaNotaEntry::create([
            'conto_id' => $contoDaId,
            'rendiconto_code' => 'EXP_D_1',
            'date' => $data,
            'amount' => -$importo,
            'description' => $desc,
            'gestione' => 'istituzionale',
            'competenza_cassa' => true,
        ]);
        PrimaNotaEntry::create([
            'conto_id' => $contoAId,
            'rendiconto_code' => 'INC_D_1',
            'date' => $data,
            'amount' => $importo,
            'description' => $desc,
            'gestione' => 'istituzionale',
            'competenza_cassa' => true,
        ]);

        return redirect()->route('prima-nota.index')->with('flash', ['type' => 'success', 'message' => 'Giroconto registrato.']);
    }
}
