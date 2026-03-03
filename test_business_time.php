<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\BusinessTimeCalculator;
use Carbon\Carbon;

echo "=== TEST DE CÁLCULO DE TIEMPO LABORAL ===\n\n";

// Test 1: Cambio fuera de horario (7:45 PM) hasta dentro de horario (2:06 PM)
$start = Carbon::parse('2026-02-19 19:45:31', 'America/Mexico_City');
$end = Carbon::parse('2026-03-03 14:06:39', 'America/Mexico_City');

echo "Test 1: Cambio fuera de horario\n";
echo "Inicio: " . $start->format('Y-m-d H:i:s') . " (hora: {$start->hour})\n";
echo "Fin: " . $end->format('Y-m-d H:i:s') . " (hora: {$end->hour})\n";
echo "Días entre fechas: " . $start->diffInDays($end) . "\n";

$result = BusinessTimeCalculator::calculateBusinessHours($start, $end);
echo "Resultado: {$result['formatted']}\n";
echo "Total segundos: {$result['total_seconds']}\n";
echo "Días: {$result['days']}, Horas: {$result['hours']}, Minutos: {$result['minutes']}\n\n";

// Test 2: Mismo día, dentro de horario
$start2 = Carbon::parse('2026-03-03 10:00:00', 'America/Mexico_City');
$end2 = Carbon::parse('2026-03-03 14:06:39', 'America/Mexico_City');

echo "Test 2: Mismo día, dentro de horario\n";
echo "Inicio: " . $start2->format('Y-m-d H:i:s') . "\n";
echo "Fin: " . $end2->format('Y-m-d H:i:s') . "\n";

$result2 = BusinessTimeCalculator::calculateBusinessHours($start2, $end2);
echo "Resultado: {$result2['formatted']}\n";
echo "Total segundos: {$result2['total_seconds']}\n\n";

// Test 3: Verificar si las fechas son días laborales
echo "Test 3: Verificación de días\n";
$current = $start->copy();
$daysChecked = 0;
while ($current->lessThan($end) && $daysChecked < 15) {
    $isWeekend = $current->isWeekend() ? 'SÍ' : 'NO';
    echo $current->format('Y-m-d (l)') . " - ¿Fin de semana? {$isWeekend}\n";
    $current->addDay();
    $daysChecked++;
}
