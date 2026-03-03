<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    public function run(): void
    {
        $holidays = [
            [
                'date' => '2026-01-01',
                'name' => 'Año Nuevo',
                'description' => 'Celebración del Año Nuevo',
            ],
            [
                'date' => '2026-02-02',
                'name' => 'Día de la Constitución',
                'description' => 'Conmemoración de la Constitución Mexicana de 1917',
            ],
            [
                'date' => '2026-03-16',
                'name' => 'Natalicio de Benito Juárez',
                'description' => 'Conmemoración del natalicio de Benito Juárez',
            ],
            [
                'date' => '2026-04-02',
                'name' => 'Jueves Santo',
                'description' => 'Semana Santa',
            ],
            [
                'date' => '2026-04-03',
                'name' => 'Viernes Santo',
                'description' => 'Semana Santa',
            ],
            [
                'date' => '2026-05-01',
                'name' => 'Día del Trabajo',
                'description' => 'Celebración del Día Internacional del Trabajo',
            ],
            [
                'date' => '2026-09-16',
                'name' => 'Día de la Independencia',
                'description' => 'Conmemoración de la Independencia de México',
            ],
            [
                'date' => '2026-11-02',
                'name' => 'Día de Muertos',
                'description' => 'Celebración del Día de Muertos',
            ],
            [
                'date' => '2026-11-16',
                'name' => 'Día de la Revolución Mexicana',
                'description' => 'Conmemoración de la Revolución Mexicana',
            ],
            [
                'date' => '2026-12-25',
                'name' => 'Navidad',
                'description' => 'Celebración de la Navidad',
            ],
        ];

        foreach ($holidays as $holiday) {
            Holiday::updateOrCreate(
                ['date' => $holiday['date']],
                $holiday
            );
        }

        $this->command->info('Días festivos de México 2026 cargados exitosamente.');
    }
}
