<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Division;
use App\Models\Station;
use App\Models\Attribute;
use Illuminate\Support\Facades\Hash;

class ProductionControlSeeder extends Seeder
{
    public function run(): void
    {
        $adminUser = User::create([
            'name' => 'Administrador',
            'email' => 'admin@control.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);

        $normalUser1 = User::create([
            'name' => 'Pedro Morales',
            'email' => 'pedro@control.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
        ]);

        $normalUser2 = User::create([
            'name' => 'María González',
            'email' => 'maria@control.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
        ]);

        $acero = Division::create([
            'name' => 'ACERO',
            'color' => 'verde',
            'order' => 1,
            'active' => true,
        ]);

        $madera = Division::create([
            'name' => 'MADERA',
            'color' => 'verde',
            'order' => 2,
            'active' => true,
        ]);

        $ensambles = Division::create([
            'name' => 'ENSAMBLES FINALES',
            'color' => 'verde',
            'order' => 3,
            'active' => true,
        ]);

        $troqueles = Station::create([
            'division_id' => $acero->id,
            'name' => 'TROQUELES',
            'color' => 'verde',
            'order' => 1,
            'active' => true,
        ]);

        $corteLaser = Station::create([
            'division_id' => $acero->id,
            'name' => 'CORTE LASER',
            'color' => 'verde',
            'order' => 2,
            'active' => true,
        ]);

        Attribute::create([
            'station_id' => $troqueles->id,
            'name' => 'MAQUINARIA',
            'color' => 'verde',
            'order' => 1,
            'active' => true,
        ]);

        Attribute::create([
            'station_id' => $troqueles->id,
            'name' => 'M.O.',
            'color' => 'verde',
            'order' => 2,
            'active' => true,
        ]);

        Attribute::create([
            'station_id' => $troqueles->id,
            'name' => 'PROGRAMA',
            'color' => 'verde',
            'order' => 3,
            'active' => true,
        ]);

        Attribute::create([
            'station_id' => $troqueles->id,
            'name' => 'CALIDAD',
            'color' => 'verde',
            'order' => 4,
            'active' => true,
        ]);

        Attribute::create([
            'station_id' => $corteLaser->id,
            'name' => 'MAQUINARIA',
            'color' => 'verde',
            'order' => 1,
            'active' => true,
        ]);

        Attribute::create([
            'station_id' => $corteLaser->id,
            'name' => 'M.O.',
            'color' => 'verde',
            'order' => 2,
            'active' => true,
        ]);

        Attribute::create([
            'station_id' => $corteLaser->id,
            'name' => 'PROGRAMA',
            'color' => 'verde',
            'order' => 3,
            'active' => true,
        ]);

        Attribute::create([
            'station_id' => $corteLaser->id,
            'name' => 'CALIDAD',
            'color' => 'verde',
            'order' => 4,
            'active' => true,
        ]);

        $normalUser1->assignedStations()->attach($troqueles->id);
        $normalUser2->assignedStations()->attach($corteLaser->id);
    }
}
