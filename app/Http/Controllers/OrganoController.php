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

    public function show(Organo $organo)
    {
        $organo->load(['caricheSociali' => ['incarichi' => ['member:id,cognome,nome']]]);
        $organo->loadCount('caricheSociali');
        $organo->setAttribute('mandato_da', $organo->dataInizioMandato()?->toDateString());
        $organo->setAttribute('mandato_scadenza', $organo->mandatoScadenza()?->toDateString());
        $members = Member::nonCessati()->orderBy('cognome')->orderBy('nome')->get(['id', 'cognome', 'nome']);

        return Inertia::render('Organi/Show', ['organo' => $organo, 'members' => $members]);
    }
}
