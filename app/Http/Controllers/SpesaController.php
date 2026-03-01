<?php

namespace App\Http\Controllers;

use App\Models\Conto;
use App\Models\PrimaNotaEntry;
use App\Models\Spesa;
use App\Services\RendicontoCassaSchema;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Spese: registrazione uscite di cassa con opzione prima nota e scelta voce rendiconto.
 */
class SpesaController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin,segreteria,contabile');
    }

    public function index(Request $request)
    {
        $query = Spesa::with(['conto', 'primaNotaEntries']);

        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->to);
        }

        $spese = $query->orderByDesc('date')->orderByDesc('id')->paginate(20)->withQueryString();

        return Inertia::render('Spese/Index', [
            'spese' => $spese,
            'filters' => $request->only('from', 'to'),
        ]);
    }

    public function create(Request $request)
    {
        $conti = Conto::attivi()->ordered()->get(['id', 'name', 'code']);
        if ($conti->isEmpty()) {
            return redirect()->route('spese.index')->with('flash', [
                'type' => 'warning',
                'message' => 'Nessun conto tesoreria attivo. Creare almeno un conto prima di registrare spese.',
            ]);
        }

        return Inertia::render('Spese/Create', [
            'conti' => $conti,
            'rendicontoVociUscita' => RendicontoCassaSchema::getSelectableVoicesUscita(),
            'macroAreasUscita' => RendicontoCassaSchema::getMacroAreasForSelectUscita(),
            'oldInput' => $request->old(),
        ]);
    }

    public function store(Request $request)
    {
        $validCodesUscita = RendicontoCassaSchema::getValidCodesUscita();
        $rules = [
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'conto_id' => 'required|exists:conti,id',
            'genera_prima_nota' => 'boolean',
            'rendiconto_code' => 'nullable|string|in:' . implode(',', $validCodesUscita),
            'gestione' => 'nullable|in:istituzionale,commerciale',
            'competenza_cassa' => 'boolean',
        ];
        if ($request->boolean('genera_prima_nota')) {
            $rules['rendiconto_code'] = 'required|string|in:' . implode(',', $validCodesUscita);
        }
        $request->validate($rules);

        $spesa = Spesa::create([
            'date' => $request->date,
            'amount' => $request->amount,
            'description' => $request->description,
            'conto_id' => $request->conto_id,
            'genera_prima_nota' => $request->boolean('genera_prima_nota', true),
            'rendiconto_code' => $request->rendiconto_code,
            'gestione' => $request->gestione,
            'competenza_cassa' => $request->boolean('competenza_cassa', true),
        ]);

        if ($spesa->genera_prima_nota && $spesa->rendiconto_code) {
            PrimaNotaEntry::create([
                'conto_id' => $spesa->conto_id,
                'rendiconto_code' => $spesa->rendiconto_code,
                'entryable_type' => Spesa::class,
                'entryable_id' => $spesa->id,
                'date' => $spesa->date->toDateString(),
                'amount' => -abs((float) $spesa->amount),
                'description' => $spesa->description ?? 'Spesa',
                'gestione' => $spesa->gestione ?? 'istituzionale',
                'competenza_cassa' => $spesa->competenza_cassa,
            ]);
        }

        return redirect()->route('spese.index')->with('flash', ['type' => 'success', 'message' => 'Spesa registrata.']);
    }

    public function show(Spesa $spesa)
    {
        $spesa->load(['conto', 'primaNotaEntries']);

        return Inertia::render('Spese/Show', [
            'spesa' => $spesa,
        ]);
    }

    public function destroy(Request $request, Spesa $spesa)
    {
        $spesa->primaNotaEntries()->each(fn (PrimaNotaEntry $entry) => $entry->delete());
        $spesa->delete();

        return redirect()->route('spese.index')->with('flash', ['type' => 'success', 'message' => 'Spesa eliminata.']);
    }
}
