<?php

namespace App\Http\Controllers;

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

        $entries = $query->orderByDesc('date')->orderByDesc('id')->paginate(30)->withQueryString();
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
        return Inertia::render('PrimaNota/Create', [
            'rendicontoVoci' => RendicontoCassaSchema::getSelectableVoices(),
            'macroAreas' => RendicontoCassaSchema::getMacroAreasForSelect(),
        ]);
    }

    public function store(Request $request)
    {
        $validCodes = RendicontoCassaSchema::getValidCodes();
        $request->validate([
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

        PrimaNotaEntry::create($request->only('rendiconto_code', 'date', 'amount', 'description', 'gestione') + [
            'competenza_cassa' => $request->boolean('competenza_cassa', true),
        ]);

        return redirect()->route('prima-nota.index')->with('flash', ['type' => 'success', 'message' => 'Movimento registrato.']);
    }

    public function edit(PrimaNotaEntry $prima_nota_entry)
    {
        return Inertia::render('PrimaNota/Edit', [
            'entry' => $prima_nota_entry,
            'rendicontoVoci' => RendicontoCassaSchema::getSelectableVoices(),
            'macroAreas' => RendicontoCassaSchema::getMacroAreasForSelect(),
        ]);
    }

    public function update(Request $request, PrimaNotaEntry $prima_nota_entry)
    {
        $validCodes = RendicontoCassaSchema::getValidCodes();
        $request->validate([
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

        $prima_nota_entry->update($request->only('rendiconto_code', 'date', 'amount', 'description', 'gestione') + [
            'competenza_cassa' => $request->boolean('competenza_cassa', true),
        ]);

        return redirect()->route('prima-nota.index')->with('flash', ['type' => 'success', 'message' => 'Movimento aggiornato.']);
    }
}
