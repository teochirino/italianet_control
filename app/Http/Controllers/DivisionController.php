<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DivisionController extends Controller
{
    public function index()
    {
        $divisions = Division::with(['stations.attributes'])
            ->orderBy('order')
            ->get();

        return Inertia::render('Admin/Divisions/Index', [
            'divisions' => $divisions,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Divisions/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|string|max:255',
            'order' => 'nullable|integer',
            'active' => 'boolean',
        ]);

        Division::create($validated);

        return redirect()->route('divisions.index')
            ->with('success', 'División creada exitosamente.');
    }

    public function edit(Division $division)
    {
        return Inertia::render('Admin/Divisions/Edit', [
            'division' => $division,
        ]);
    }

    public function update(Request $request, Division $division)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|string|max:255',
            'order' => 'nullable|integer',
            'active' => 'boolean',
        ]);

        $division->update($validated);

        return redirect()->route('divisions.index')
            ->with('success', 'División actualizada exitosamente.');
    }

    public function destroy(Division $division)
    {
        $division->delete();

        return redirect()->route('divisions.index')
            ->with('success', 'División eliminada exitosamente.');
    }
}
