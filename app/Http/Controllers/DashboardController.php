<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $divisions = Division::with(['stations' => function ($query) use ($user) {
            $query->where('active', true)
                ->with(['attributes' => function ($q) {
                    $q->where('active', true)->orderBy('order');
                }])
                ->orderBy('order');
        }])
        ->where('active', true)
        ->orderBy('order')
        ->get();

        if (!$user->is_admin) {
            $assignedStationIds = $user->assignedStations()->pluck('stations.id');
            
            $divisions = $divisions->map(function ($division) use ($assignedStationIds) {
                $division->stations = $division->stations->filter(function ($station) use ($assignedStationIds) {
                    return $assignedStationIds->contains($station->id);
                });
                return $division;
            })->filter(function ($division) {
                return $division->stations->isNotEmpty();
            })->values();
        }

        return Inertia::render('Dashboard', [
            'divisions' => $divisions,
            'isAdmin' => $user->is_admin,
        ]);
    }

    public function getData(Request $request)
    {
        $user = $request->user();

        $divisions = Division::with(['stations' => function ($query) use ($user) {
            $query->where('active', true)
                ->with(['attributes' => function ($q) {
                    $q->where('active', true)->orderBy('order');
                }])
                ->orderBy('order');
        }])
        ->where('active', true)
        ->orderBy('order')
        ->get();

        if (!$user->is_admin) {
            $assignedStationIds = $user->assignedStations()->pluck('stations.id');
            
            $divisions = $divisions->map(function ($division) use ($assignedStationIds) {
                $division->stations = $division->stations->filter(function ($station) use ($assignedStationIds) {
                    return $assignedStationIds->contains($station->id);
                });
                return $division;
            })->filter(function ($division) {
                return $division->stations->isNotEmpty();
            })->values();
        }

        return response()->json([
            'divisions' => $divisions,
        ]);
    }
}
