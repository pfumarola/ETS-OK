<?php

namespace App\Http\Controllers;

use App\Models\DocumentTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DocumentTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin,segreteria');
    }

    public function index()
    {
        $templates = DocumentTemplate::orderBy('nome')->paginate(15);

        return Inertia::render('DocumentTemplates/Index', [
            'templates' => $templates,
        ]);
    }

    public function create()
    {
        return Inertia::render('DocumentTemplates/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'contenuto' => 'nullable|string',
        ]);

        DocumentTemplate::create($validated);

        return redirect()->route('document-templates.index')
            ->with('flash', ['type' => 'success', 'message' => 'Template creato.']);
    }

    public function edit(DocumentTemplate $document_template)
    {
        return Inertia::render('DocumentTemplates/Edit', [
            'template' => $document_template,
        ]);
    }

    public function update(Request $request, DocumentTemplate $document_template)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'contenuto' => 'nullable|string',
        ]);

        $document_template->update($validated);

        return redirect()->route('document-templates.index')
            ->with('flash', ['type' => 'success', 'message' => 'Template aggiornato.']);
    }

    public function destroy(DocumentTemplate $document_template)
    {
        $document_template->delete();

        return redirect()->route('document-templates.index')
            ->with('flash', ['type' => 'success', 'message' => 'Template eliminato.']);
    }
}
