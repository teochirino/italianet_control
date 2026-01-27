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

        $visibleStationIds = $user->visibleStationIds();

        $divisions = Division::with(['stations' => function ($query) use ($user) {
            $query->where('active', true)
                ->with(['attributes' => function ($q) {
                    $q->where('active', true)->orderBy('order');
                }])
                ->orderBy('order');
        }])
        ->where('active', true)
        ->orderBy('order')
        ->when(!$user->is_admin, function ($query) use ($visibleStationIds) {
            $query->whereHas('stations', function ($stationQuery) use ($visibleStationIds) {
                $stationQuery->where('active', true)->whereIn('id', $visibleStationIds);
            });
        })
        ->get();

        if (!$user->is_admin) {
            $divisions->each(function ($division) use ($visibleStationIds) {
                $filteredStations = $division->stations->whereIn('id', $visibleStationIds)->values();
                $division->setRelation('stations', $filteredStations);
            });
        }

        return Inertia::render('Dashboard', [
            'divisions' => $divisions,
            'isAdmin' => $user->is_admin,
        ]);
    }

    public function getData(Request $request)
    {
        $user = $request->user();

        $visibleStationIds = $user->visibleStationIds();

        $divisions = Division::with(['stations' => function ($query) use ($user) {
            $query->where('active', true)
                ->with(['attributes' => function ($q) {
                    $q->where('active', true)->orderBy('order');
                }])
                ->orderBy('order');
        }])
        ->where('active', true)
        ->orderBy('order')
        ->when(!$user->is_admin, function ($query) use ($visibleStationIds) {
            $query->whereHas('stations', function ($stationQuery) use ($visibleStationIds) {
                $stationQuery->where('active', true)->whereIn('id', $visibleStationIds);
            });
        })
        ->get();

        if (!$user->is_admin) {
            $divisions->each(function ($division) use ($visibleStationIds) {
                $filteredStations = $division->stations->whereIn('id', $visibleStationIds)->values();
                $division->setRelation('stations', $filteredStations);
            });
        }

        return response()->json([
            'divisions' => $divisions,
        ]);
    }
}
