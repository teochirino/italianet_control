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

        $historiesWithDuration = $this->calculateDurations($histories->items());

        return response()->json([
            'station' => $station->load('division'),
            'histories' => [
                'data' => $historiesWithDuration,
                'current_page' => $histories->currentPage(),
                'last_page' => $histories->lastPage(),
                'total' => $histories->total(),
                'per_page' => $histories->perPage(),
            ],
        ]);
    }

    public function getAttributeHistory(Request $request, Attribute $attribute)
    {
        $perPage = 25;
        
        $histories = ColorChangeHistory::with(['user'])
            ->where('attribute_id', $attribute->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $historiesWithDuration = $this->calculateDurations($histories->items());

        return response()->json([
            'attribute' => $attribute->load('station.division'),
            'histories' => [
                'data' => $historiesWithDuration,
                'current_page' => $histories->currentPage(),
                'last_page' => $histories->lastPage(),
                'total' => $histories->total(),
                'per_page' => $histories->perPage(),
            ],
        ]);
    }

    private function calculateDurations($histories)
    {
        $result = [];
        
        foreach ($histories as $index => $history) {
            $historyArray = $history->toArray();
            
            $previousChangeTime = null;
            if ($index < count($histories) - 1) {
                $previousChangeTime = $histories[$index + 1]->created_at;
            }
            
            $historyArray['previous_color_duration'] = $previousChangeTime 
                ? $this->calculateTimeDifference($previousChangeTime, $history->created_at)
                : null;
            
            $nextChangeTime = null;
            if ($index > 0) {
                $nextChangeTime = $histories[$index - 1]->created_at;
            } else {
                $nextChangeTime = now();
            }
            
            $historyArray['new_color_duration'] = $this->calculateTimeDifference($history->created_at, $nextChangeTime);
            
            $result[] = $historyArray;
        }
        
        return $result;
    }

    private function calculateTimeDifference($start, $end)
    {
        $startTime = is_string($start) ? new \DateTime($start) : $start;
        $endTime = is_string($end) ? new \DateTime($end) : $end;
        
        $interval = $startTime->diff($endTime);
        
        $days = $interval->days;
        $hours = $interval->h;
        $minutes = $interval->i;
        $seconds = $interval->s;
        
        return [
            'days' => $days,
            'hours' => $hours,
            'minutes' => $minutes,
            'seconds' => $seconds,
            'total_seconds' => ($days * 86400) + ($hours * 3600) + ($minutes * 60) + $seconds,
            'formatted' => $this->formatDuration($days, $hours, $minutes, $seconds),
        ];
    }

    private function formatDuration($days, $hours, $minutes, $seconds)
    {
        $parts = [];
        
        if ($days > 0) {
            $parts[] = $days . ($days === 1 ? ' día' : ' días');
        }
        if ($hours > 0) {
            $parts[] = $hours . ($hours === 1 ? ' hora' : ' horas');
        }
        if ($minutes > 0) {
            $parts[] = $minutes . ($minutes === 1 ? ' minuto' : ' minutos');
        }
        if ($seconds > 0 && $days === 0 && $hours === 0) {
            $parts[] = $seconds . ($seconds === 1 ? ' segundo' : ' segundos');
        }
        
        if (empty($parts)) {
            return '0 segundos';
        }
        
        return implode(', ', $parts);
    }
}
