<?php

namespace App\Http\Controllers;

use App\Models\Station;
use App\Models\Division;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StationController extends Controller
{
    public function index()
    {
        $stations = Station::with(['division', 'attributes'])
            ->orderBy('order')
            ->get();

        return Inertia::render('Admin/Stations/Index', [
            'stations' => $stations,
        ]);
    }

    public function create()
    {
        $divisions = Division::orderBy('name')->get();

        return Inertia::render('Admin/Stations/Create', [
            'divisions' => $divisions,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'division_id' => 'required|exists:divisions,id',
            'name' => 'required|string|max:255',
            'order' => 'nullable|integer',
            'active' => 'boolean',
        ]);

        Station::create($validated);

        return redirect()->route('stations.index')
            ->with('success', 'Estación creada exitosamente.');
    }

    public function edit(Station $station)
    {
        $divisions = Division::orderBy('name')->get();

        return Inertia::render('Admin/Stations/Edit', [
            'station' => $station->load('division'),
            'divisions' => $divisions,
        ]);
    }

    public function update(Request $request, Station $station)
    {
        $validated = $request->validate([
            'division_id' => 'required|exists:divisions,id',
            'name' => 'required|string|max:255',
            'order' => 'nullable|integer',
            'active' => 'boolean',
        ]);

        $station->update($validated);

        return redirect()->route('stations.index')
            ->with('success', 'Estación actualizada exitosamente.');
    }

    public function destroy(Station $station)
    {
        $station->delete();

        return redirect()->route('stations.index')
            ->with('success', 'Estación eliminada exitosamente.');
    }
}
