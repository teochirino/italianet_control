<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Carbon\Carbon;
use App\Models\Holiday;

$start = Carbon::parse('2026-02-19 19:45:31', 'America/Mexico_City');
$end = Carbon::parse('2026-03-03 14:06:39', 'America/Mexico_City');

echo "=== DEBUG DETALLADO ===\n";
echo "Inicio: {$start->format('Y-m-d H:i:s')}\n";
echo "Fin: {$end->format('Y-m-d H:i:s')}\n\n";

$WORK_START_HOUR = 8;
$WORK_END_HOUR = 18;

$totalSeconds = 0;
$current = $start->copy();
$holidays = Holiday::getHolidaysInRange($start, $end);

$iteration = 0;
while ($current->lessThan($end) && $iteration < 15) {
    $iteration++;
    
    $isWeekend = $current->isWeekend();
    $dateString = $current->format('Y-m-d');
    $isHoliday = in_array($dateString, $holidays);
    $isBusinessDay = !$isWeekend && !$isHoliday;
    
    echo "\n--- Día {$iteration}: {$current->format('Y-m-d (l)')} ---\n";
    echo "¿Día laboral? " . ($isBusinessDay ? 'SÍ' : 'NO') . "\n";
    
    if ($isBusinessDay) {
        $dayStart = $current->copy()->setTime($WORK_START_HOUR, 0, 0);
        $dayEnd = $current->copy()->setTime($WORK_END_HOUR, 0, 0);
        
        $periodStart = $current->copy();
        $periodEnd = $end->copy();
        
        echo "PeriodStart inicial: {$periodStart->format('H:i:s')}\n";
        
        if (!$current->isSameDay($end)) {
            $periodEnd = $current->copy()->endOfDay();
        }
        
        echo "PeriodEnd después de ajuste: {$periodEnd->format('Y-m-d H:i:s')}\n";
        
        if ($periodStart->lessThan($dayStart)) {
            $periodStart = $dayStart->copy();
            echo "Ajustado a inicio de jornada: {$periodStart->format('H:i:s')}\n";
        }
        
        if ($periodStart->greaterThanOrEqualTo($dayEnd)) {
            echo "PeriodStart >= DayEnd, saltando al siguiente día\n";
            $current->addDay()->startOfDay();
            continue;
        }
        
        if ($periodEnd->greaterThan($dayEnd)) {
            $periodEnd = $dayEnd->copy();
            echo "Ajustado a fin de jornada: {$periodEnd->format('H:i:s')}\n";
        }
        
        if ($periodStart->lessThan($periodEnd)) {
            $secondsInPeriod = $periodEnd->diffInSeconds($periodStart);
            $totalSeconds += $secondsInPeriod;
            echo "Segundos en este período: {$secondsInPeriod}\n";
            echo "Total acumulado: {$totalSeconds}\n";
        } else {
            echo "PeriodStart >= PeriodEnd, no se cuenta tiempo\n";
        }
    }
    
    $current->addDay()->startOfDay();
}

echo "\n=== RESULTADO FINAL ===\n";
echo "Total segundos: {$totalSeconds}\n";
echo "Horas: " . floor($totalSeconds / 3600) . "\n";
echo "Días laborales: " . floor($totalSeconds / 36000) . "\n";
