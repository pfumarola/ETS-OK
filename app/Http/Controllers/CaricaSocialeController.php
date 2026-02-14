<?php

namespace App\Http\Controllers;

use App\Models\CaricaSociale;
use App\Models\Organo;
use Illuminate\Http\Request;

class CaricaSocialeController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin,segreteria');
    }

    public function store(Request $request)
    {
        $request->validate([
            'organo_id' => 'required|exists:organi,id',
            'nome' => 'required|string|max:255',
            'ordine' => 'nullable|integer|min:0',
        ]);
        CaricaSociale::create([
            'organo_id' => $request->organo_id,
            'nome' => $request->nome,
            'ordine' => $request->input('ordine', 0),
        ]);

        return back()->with('flash', ['type' => 'success', 'message' => 'Carica creata.']);
    }

    public function update(Request $request, CaricaSociale $cariche_sociali)
    {
        $request->validate([
            'organo_id' => 'required|exists:organi,id',
            'nome' => 'required|string|max:255',
            'ordine' => 'nullable|integer|min:0',
        ]);
        $cariche_sociali->update([
            'organo_id' => $request->organo_id,
            'nome' => $request->nome,
            'ordine' => $request->input('ordine', 0),
        ]);

        return back()->with('flash', ['type' => 'success', 'message' => 'Carica aggiornata.']);
    }

    public function destroy(CaricaSociale $cariche_sociali)
    {
        // Gli incarichi vengono eliminati automaticamente tramite cascadeOnDelete nella migrazione
        $cariche_sociali->delete();

        return back()->with('flash', ['type' => 'success', 'message' => 'Carica eliminata.']);
    }
}
