<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Organo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrganoController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin,segreteria');
    }

    public function index(Request $request)
    {
        $organi = Organo::query()->orderBy('nome')->paginate(15)->withQueryString();

        return Inertia::render('Organi/Index', [
            'organi' => $organi,
        ]);
    }

    public function create()
    {
        return Inertia::render('Organi/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'durata_mesi' => 'nullable|integer|min:1|max:120',
            'richiedi_elezioni_fine_mandato' => 'boolean',
            'mandato_da' => 'nullable|date',
        ]);
        Organo::create($request->only([
            'nome', 'durata_mesi', 'richiedi_elezioni_fine_mandato', 'mandato_da',
        ]));

        return redirect()->route('organi.index')->with('flash', ['type' => 'success', 'message' => 'Organo creato.']);
    }

    public function show()
    {
        $id = request()->route()->parameter('organo') ?? request()->route()->parameter('organi');
        $organo = Organo::findOrFail($id);
        $organo->load(['caricheSociali' => ['incarichi' => ['member:id,cognome,nome']]]);
        $organo->loadCount('caricheSociali');
        $members = Member::nonCessati()->orderBy('cognome')->orderBy('nome')->get(['id', 'cognome', 'nome']);

        return Inertia::render('Organi/Show', ['organo' => $organo, 'members' => $members]);
    }

    public function edit()
    {
        $id = request()->route()->parameter('organo') ?? request()->route()->parameter('organi');
        $organo = Organo::findOrFail($id);
        return Inertia::render('Organi/Edit', ['organo' => $organo]);
    }

    public function update(Request $request)
    {
        $id = request()->route()->parameter('organo') ?? request()->route()->parameter('organi');
        $organo = Organo::findOrFail($id);
        
        $request->validate([
            'nome' => 'required|string|max:255',
            'durata_mesi' => 'nullable|integer|min:1|max:120',
            'richiedi_elezioni_fine_mandato' => 'boolean',
            'mandato_da' => 'nullable|date',
        ]);
        $organo->update($request->only([
            'nome', 'durata_mesi', 'richiedi_elezioni_fine_mandato', 'mandato_da',
        ]));

        return redirect()->route('organi.show', $organo)->with('flash', ['type' => 'success', 'message' => 'Organo aggiornato.']);
    }

    public function destroy()
    {
        $id = request()->route()->parameter('organo') ?? request()->route()->parameter('organi');
        $organo = Organo::findOrFail($id);
        $organo->delete();

        return redirect()->route('organi.index')->with('flash', ['type' => 'success', 'message' => 'Organo eliminato.']);
    }
}
