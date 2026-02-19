<?php

namespace App\Http\Controllers;

use App\Models\Conto;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContoController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin,contabile');
    }

    public function index()
    {
        $conti = Conto::query()
            ->withCount(['movimenti', 'incassi'])
            ->ordered()
            ->get();

        return Inertia::render('Conti/Index', ['conti' => $conti]);
    }

    public function create()
    {
        return Inertia::render('Conti/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'type' => 'required|in:cassa,banca,altro',
            'ordine' => 'nullable|integer',
            'attivo' => 'boolean',
        ]);
        $data['attivo'] = $request->boolean('attivo', true);
        $data['ordine'] = $data['ordine'] ?? 0;
        Conto::create($data);
        return redirect()->route('conti.index')->with('flash', ['type' => 'success', 'message' => 'Conto creato.']);
    }

    public function edit(Conto $conti)
    {
        return Inertia::render('Conti/Edit', ['conto' => $conti]);
    }

    public function update(Request $request, Conto $conti)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'type' => 'required|in:cassa,banca,altro',
            'ordine' => 'nullable|integer',
            'attivo' => 'boolean',
        ]);
        $data['attivo'] = $request->boolean('attivo', true);
        $data['ordine'] = $data['ordine'] ?? 0;
        $conti->update($data);
        return redirect()->route('conti.index')->with('flash', ['type' => 'success', 'message' => 'Conto aggiornato.']);
    }

    public function destroy(Conto $conti)
    {
        if ($conti->movimenti()->exists()) {
            return redirect()->route('conti.index')
                ->with('flash', ['type' => 'error', 'message' => 'Impossibile eliminare il conto: ha movimenti collegati.']);
        }
        if ($conti->incassi()->exists()) {
            return redirect()->route('conti.index')
                ->with('flash', ['type' => 'error', 'message' => 'Impossibile eliminare il conto: è usato da incassi.']);
        }
        $conti->delete();
        return redirect()->route('conti.index')->with('flash', ['type' => 'success', 'message' => 'Conto eliminato.']);
    }
}
