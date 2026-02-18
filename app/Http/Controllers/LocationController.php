<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LocationController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin,segreteria');
    }

    public function index()
    {
        $locations = Location::withCount('warehouses')->orderBy('name')->paginate(15);
        return Inertia::render('Locations/Index', ['locations' => $locations]);
    }

    public function create()
    {
        return Inertia::render('Locations/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
        ]);
        Location::create($data);
        return redirect()->route('locations.index')->with('flash', ['type' => 'success', 'message' => 'Sede creata.']);
    }

    public function edit(Location $location)
    {
        return Inertia::render('Locations/Edit', ['location' => $location]);
    }

    public function update(Request $request, Location $location)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
        ]);
        $location->update($data);
        return redirect()->route('locations.index')->with('flash', ['type' => 'success', 'message' => 'Sede aggiornata.']);
    }

    public function destroy(Location $location)
    {
        $location->delete();
        return redirect()->route('locations.index')->with('flash', ['type' => 'success', 'message' => 'Sede eliminata.']);
    }
}
