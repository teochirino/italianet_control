<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Division;
use App\Models\Station;
use App\Models\UserStationAssignment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserStationAssignmentController extends Controller
{
    public function index()
    {
        $users = User::where('is_admin', false)
            ->with('assignedStations.division')
            ->orderBy('name')
            ->get();

        $divisions = Division::with(['stations' => function($query) {
            $query->where('active', true)->orderBy('order');
        }])
        ->where('active', true)
        ->orderBy('order')
        ->get();

        return Inertia::render('Admin/UserAssignments/Index', [
            'users' => $users,
            'divisions' => $divisions,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'station_id' => 'required|exists:stations,id',
        ]);

        UserStationAssignment::firstOrCreate($validated);

        return redirect()->route('user-assignments.index')
            ->with('success', 'Asignación creada exitosamente.');
    }

    public function destroy(UserStationAssignment $userAssignment)
    {
        $userAssignment->delete();

        return redirect()->route('user-assignments.index')
            ->with('success', 'Asignación eliminada exitosamente.');
    }

    public function assignStation(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'station_id' => 'required|exists:stations,id',
        ]);

        $assignment = UserStationAssignment::firstOrCreate($validated);

        return response()->json([
            'success' => true,
            'assignment' => $assignment,
        ]);
    }

    public function removeStation(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'station_id' => 'required|exists:stations,id',
        ]);

        UserStationAssignment::where('user_id', $validated['user_id'])
            ->where('station_id', $validated['station_id'])
            ->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
