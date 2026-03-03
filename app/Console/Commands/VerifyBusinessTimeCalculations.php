<?php

namespace App\Console\Commands;

use App\Models\ColorChangeHistory;
use App\Services\BusinessTimeCalculator;
use Illuminate\Console\Command;

class VerifyBusinessTimeCalculations extends Command
{
    protected $signature = 'time:verify-business-hours';
    protected $description = 'Verifica los cálculos de tiempo laboral en el historial de cambios de color';

    public function handle()
    {
        $this->info('Verificando cálculos de tiempo laboral...');
        $this->newLine();

        $histories = ColorChangeHistory::orderBy('created_at', 'desc')->take(50)->get();

        if ($histories->isEmpty()) {
            $this->warn('No hay registros de historial para verificar.');
            return 0;
        }

        $this->info("Analizando los últimos {$histories->count()} registros...");
        $this->newLine();

        $table = [];

        foreach ($histories as $history) {
            $nextChange = ColorChangeHistory::where('attribute_id', $history->attribute_id)
                ->where('created_at', '>', $history->created_at)
                ->orderBy('created_at', 'asc')
                ->first();

            $endTime = $nextChange ? $nextChange->created_at : now();
            
            $businessTime = BusinessTimeCalculator::calculateBusinessHours(
                $history->created_at,
                $endTime
            );

            $table[] = [
                'ID' => $history->id,
                'Atributo' => $history->attribute->name ?? 'N/A',
                'Color' => $history->new_color,
                'Inicio' => $history->created_at->format('Y-m-d H:i:s'),
                'Fin' => $endTime->format('Y-m-d H:i:s'),
                'Tiempo Laboral' => $businessTime['formatted'],
            ];
        }

        $this->table(
            ['ID', 'Atributo', 'Color', 'Inicio', 'Fin', 'Tiempo Laboral'],
            $table
        );

        $this->newLine();
        $this->info('✓ Verificación completada exitosamente.');
        $this->info('Los tiempos mostrados excluyen fines de semana y horas fuera de 8:00-18:00.');

        return 0;
    }
}
