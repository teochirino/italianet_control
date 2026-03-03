<?php

namespace App\Console\Commands;

use App\Models\ColorChangeHistory;
use App\Services\BusinessTimeCalculator;
use Illuminate\Console\Command;

class CheckHistoryData extends Command
{
    protected $signature = 'history:check';
    protected $description = 'Verificar datos del historial de cambios de color';

    public function handle()
    {
        $this->info('Consultando historial de MAQUINARIA...');
        
        $histories = ColorChangeHistory::with('attribute')
            ->whereHas('attribute', function($query) {
                $query->where('name', 'like', '%MAQUINARIA%');
            })
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $this->table(
            ['ID', 'Anterior', 'Nuevo', 'Fecha Cambio', 'Comentario'],
            $histories->map(function($h) {
                return [
                    $h->id,
                    $h->previous_color,
                    $h->new_color,
                    $h->created_at->format('Y-m-d H:i:s'),
                    $h->comment ?? 'N/A'
                ];
            })
        );

        $this->info("\nCalculando tiempos laborales...");
        
        foreach ($histories as $index => $history) {
            $this->line("\n--- Registro ID: {$history->id} ---");
            $this->line("Cambio: {$history->previous_color} -> {$history->new_color}");
            $this->line("Fecha: {$history->created_at->format('Y-m-d H:i:s')}");
            
            if ($index < $histories->count() - 1) {
                $previousChange = $histories[$index + 1];
                $duration = BusinessTimeCalculator::calculateBusinessHours(
                    $previousChange->created_at,
                    $history->created_at
                );
                $this->line("Tiempo en color anterior ({$history->previous_color}): {$duration['formatted']}");
            }
            
            $nextChange = $index > 0 ? $histories[$index - 1] : null;
            $endTime = $nextChange ? $nextChange->created_at : now();
            
            $duration = BusinessTimeCalculator::calculateBusinessHours(
                $history->created_at,
                $endTime
            );
            $this->line("Tiempo en nuevo color ({$history->new_color}): {$duration['formatted']}");
        }

        return 0;
    }
}
