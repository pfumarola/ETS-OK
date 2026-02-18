<?php

namespace App\Http\Controllers;

use App\Models\VerbaleTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VerbaleTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin,segreteria');
    }

    public function index()
    {
        $templates = VerbaleTemplate::orderBy('nome')->paginate(15);

        return Inertia::render('VerbaleTemplates/Index', [
            'templates' => $templates,
        ]);
    }

    public function create()
    {
        return Inertia::render('VerbaleTemplates/Create', [
            'tipoOptions' => [
                ['value' => '', 'label' => 'Per tutti i tipi'],
                ['value' => 'consiglio_direttivo', 'label' => 'Consiglio direttivo'],
                ['value' => 'assemblea', 'label' => 'Assemblea'],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'tipo' => 'nullable|in:consiglio_direttivo,assemblea',
            'contenuto' => 'nullable|string',
        ]);
        if (isset($validated['tipo']) && $validated['tipo'] === '') {
            $validated['tipo'] = null;
        }
        VerbaleTemplate::create($validated);

        return redirect()->route('verbale-templates.index')
            ->with('flash', ['type' => 'success', 'message' => 'Template creato.']);
    }

    public function edit(VerbaleTemplate $verbale_template)
    {
        return Inertia::render('VerbaleTemplates/Edit', [
            'template' => $verbale_template,
            'tipoOptions' => [
                ['value' => '', 'label' => 'Per tutti i tipi'],
                ['value' => 'consiglio_direttivo', 'label' => 'Consiglio direttivo'],
                ['value' => 'assemblea', 'label' => 'Assemblea'],
            ],
        ]);
    }

    public function update(Request $request, VerbaleTemplate $verbale_template)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'tipo' => 'nullable|in:consiglio_direttivo,assemblea',
            'contenuto' => 'nullable|string',
        ]);
        if (isset($validated['tipo']) && $validated['tipo'] === '') {
            $validated['tipo'] = null;
        }
        $verbale_template->update($validated);

        return redirect()->route('verbale-templates.index')
            ->with('flash', ['type' => 'success', 'message' => 'Template aggiornato.']);
    }

    public function destroy(VerbaleTemplate $verbale_template)
    {
        $verbale_template->delete();

        return redirect()->route('verbale-templates.index')
            ->with('flash', ['type' => 'success', 'message' => 'Template eliminato.']);
    }
}
