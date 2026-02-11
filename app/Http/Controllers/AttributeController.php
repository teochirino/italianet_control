<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\ColorChangeHistory;
use App\Models\Station;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AttributeController extends Controller
{
    public function index()
    {
        $attributes = Attribute::with(['station.division'])
            ->orderBy('order')
            ->get();

        return Inertia::render('Admin/Attributes/Index', [
            'attributes' => $attributes,
        ]);
    }

    public function create()
    {
        $stations = Station::with('division')->orderBy('name')->get();

        return Inertia::render('Admin/Attributes/Create', [
            'stations' => $stations,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'station_id' => 'required|exists:stations,id',
            'name' => 'required|string|max:255',
            'color' => 'required|in:rojo,amarillo,verde,gris',
            'order' => 'nullable|integer',
            'active' => 'boolean',
        ]);

        Attribute::create($validated);

        return redirect()->route('attributes.index')
            ->with('success', 'Atributo creado exitosamente.');
    }

    public function edit(Attribute $attribute)
    {
        $stations = Station::with('division')->orderBy('name')->get();

        return Inertia::render('Admin/Attributes/Edit', [
            'attribute' => $attribute->load('station.division'),
            'stations' => $stations,
        ]);
    }

    public function update(Request $request, Attribute $attribute)
    {
        $validated = $request->validate([
            'station_id' => 'required|exists:stations,id',
            'name' => 'required|string|max:255',
            'color' => 'required|in:rojo,amarillo,verde,gris',
            'order' => 'nullable|integer',
            'active' => 'boolean',
        ]);

        $attribute->update($validated);

        return redirect()->route('attributes.index')
            ->with('success', 'Atributo actualizado exitosamente.');
    }

    public function destroy(Attribute $attribute)
    {
        $attribute->delete();

        return redirect()->route('attributes.index')
            ->with('success', 'Atributo eliminado exitosamente.');
    }

    public function updateColor(Request $request, Attribute $attribute)
    {
        $validated = $request->validate([
            'color' => 'required|in:rojo,amarillo,verde,gris',
            'comment' => 'nullable|string|max:100',
        ]);

        $previousColor = $attribute->color;
        $newColor = $validated['color'];

        if ($previousColor !== $newColor) {
            ColorChangeHistory::create([
                'station_id' => $attribute->station_id,
                'attribute_id' => $attribute->id,
                'user_id' => auth()->id(),
                'previous_color' => $previousColor,
                'new_color' => $newColor,
                'comment' => $validated['comment'] ?? 'Sin comentarios',
            ]);
        }

        $attribute->update(['color' => $newColor]);

        return response()->json([
            'success' => true,
            'attribute' => $attribute->fresh(),
            'station' => $attribute->station->fresh(),
            'division' => $attribute->station->division->fresh(),
        ]);
    }
}
