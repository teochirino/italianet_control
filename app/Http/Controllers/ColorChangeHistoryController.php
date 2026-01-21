<?php

namespace App\Http\Controllers;

use App\Models\ColorChangeHistory;
use App\Models\Station;
use App\Models\Attribute;
use Illuminate\Http\Request;

class ColorChangeHistoryController extends Controller
{
    public function getStationHistory(Request $request, Station $station)
    {
        $perPage = 25;
        
        $histories = ColorChangeHistory::with(['attribute', 'user'])
            ->where('station_id', $station->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'station' => $station->load('division'),
            'histories' => $histories,
        ]);
    }

    public function getAttributeHistory(Request $request, Attribute $attribute)
    {
        $perPage = 25;
        
        $histories = ColorChangeHistory::with(['user'])
            ->where('attribute_id', $attribute->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'attribute' => $attribute->load('station.division'),
            'histories' => $histories,
        ]);
    }
}
