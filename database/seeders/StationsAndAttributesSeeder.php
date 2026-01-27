<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Division;
use App\Models\Station;
use App\Models\Attribute;

class StationsAndAttributesSeeder extends Seeder
{
    public function run(): void
    {
        $acero = Division::where('name', 'ACERO')->first();
        $madera = Division::where('name', 'MADERA')->first();
        $ensambles = Division::where('name', 'ENSAMBLES FINALES')->first();

        if (!$acero || !$madera || !$ensambles) {
            $this->command->error('Las divisiones no existen. Por favor ejecuta primero ProductionControlSeeder.');
            return;
        }

        $estructuraMuebles = Station::create([
            'division_id' => $acero->id,
            'name' => 'ESTRUCTURA DE MUEBLES',
            'color' => 'verde',
            'order' => 10,
            'active' => true,
        ]);

        $this->createAttributes($estructuraMuebles, [
            'MAQUINARIA',
            'MATERIA PRIMA',
            'MOD',
            'PROGRAMA',
            'PLANTILLAS',
            'CALIDAD',
        ]);

        $estructuraMamparas = Station::create([
            'division_id' => $acero->id,
            'name' => 'ESTRUCTURA DE MAMPARAS',
            'color' => 'verde',
            'order' => 11,
            'active' => true,
        ]);

        $this->createAttributes($estructuraMamparas, [
            'MAQUINARIA',
            'MATERIA PRIMA',
            'MOD',
            'PROGRAMA',
            'PLANTILLAS',
            'CALIDAD',
        ]);

        $pintura = Station::create([
            'division_id' => $acero->id,
            'name' => 'PINTURA',
            'color' => 'verde',
            'order' => 12,
            'active' => true,
        ]);

        $this->createAttributes($pintura, [
            'MAQUINARIA (CABINA-LÍNEA DE PROD)',
            'HERRAMIENTAS Y E.TRABAJO',
            'MATERIA PRIMA',
            'MOD',
            'PROGRAMA',
            'CALIDAD',
        ]);

        $telas = Station::create([
            'division_id' => $acero->id,
            'name' => 'TELAS',
            'color' => 'verde',
            'order' => 13,
            'active' => true,
        ]);

        $this->createAttributes($telas, [
            'MAQUINARIA',
            'HERRAMIENTAS Y E.TRABAJO',
            'MATERIA PRIMA',
            'MOD',
            'PROGRAMA',
            'CALIDAD',
        ]);

        $corteCarpinteria = Station::create([
            'division_id' => $madera->id,
            'name' => 'CORTE CARPINTERÍA',
            'color' => 'verde',
            'order' => 20,
            'active' => true,
        ]);

        $this->createAttributes($corteCarpinteria, [
            'MAQUINARIA',
            'MATERIA PRIMA',
            'MOD',
            'PROGRAMA',
            'CALIDAD',
        ]);

        $enchapado = Station::create([
            'division_id' => $madera->id,
            'name' => 'ENCHAPADO',
            'color' => 'verde',
            'order' => 21,
            'active' => true,
        ]);

        $this->createAttributes($enchapado, [
            'MAQUINARIA',
            'MATERIA PRIMA',
            'MOD',
            'PROGRAMA',
            'CALIDAD',
        ]);

        $habilitadoMuebles = Station::create([
            'division_id' => $madera->id,
            'name' => 'HABILITADO MUEBLES',
            'color' => 'verde',
            'order' => 22,
            'active' => true,
        ]);

        $this->createAttributes($habilitadoMuebles, [
            'HERRAMIENTAS Y E.TRABAJO',
            'MATERIA PRIMA',
            'MOD',
            'PROGRAMA',
            'CALIDAD',
        ]);

        $cajas = Station::create([
            'division_id' => $madera->id,
            'name' => 'CAJAS',
            'color' => 'verde',
            'order' => 23,
            'active' => true,
        ]);

        $this->createAttributes($cajas, [
            'MAQUINARIA',
            'MATERIA PRIMA',
            'MOD',
            'PROGRAMA',
            'CALIDAD',
        ]);

        $ensambleMuebles = Station::create([
            'division_id' => $ensambles->id,
            'name' => 'ENSAMBLE MUEBLES',
            'color' => 'verde',
            'order' => 30,
            'active' => true,
        ]);

        $this->createAttributes($ensambleMuebles, [
            'MAQUINARIA',
            'HERRAMIENTAS Y E.TRABAJO',
            'MATERIA PRIMA',
            'MOD',
            'PROGRAMA',
            'CALIDAD',
        ]);

        $ensambleLibreros = Station::create([
            'division_id' => $ensambles->id,
            'name' => 'ENSAMBLE LIBREROS',
            'color' => 'verde',
            'order' => 31,
            'active' => true,
        ]);

        $this->createAttributes($ensambleLibreros, [
            'MAQUINARIA',
            'HERRAMIENTAS Y E.TRABAJO',
            'MATERIA PRIMA',
            'MOD',
            'PROGRAMA',
            'CALIDAD',
        ]);

        $ensambleArchiveros = Station::create([
            'division_id' => $ensambles->id,
            'name' => 'ENSAMBLE ARCHIVEROS',
            'color' => 'verde',
            'order' => 32,
            'active' => true,
        ]);

        $this->createAttributes($ensambleArchiveros, [
            'MAQUINARIA',
            'HERRAMIENTAS Y E.TRABAJO',
            'MATERIA PRIMA',
            'MOD',
            'PROGRAMA',
            'CALIDAD',
        ]);

        $ensambleMamparas = Station::create([
            'division_id' => $ensambles->id,
            'name' => 'ENSAMBLE MAMPARAS',
            'color' => 'verde',
            'order' => 33,
            'active' => true,
        ]);

        $this->createAttributes($ensambleMamparas, [
            'HERRAMIENTAS Y E.TRABAJO',
            'MATERIA PRIMA',
            'MOD',
            'PROGRAMA',
            'CALIDAD',
        ]);

        $ensambleSillas = Station::create([
            'division_id' => $ensambles->id,
            'name' => 'ENSAMBLE SILLAS Y SILLONES',
            'color' => 'verde',
            'order' => 34,
            'active' => true,
        ]);

        $this->createAttributes($ensambleSillas, [
            'HERRAMIENTAS Y E.TRABAJO',
            'MATERIA PRIMA',
            'MOD',
            'PROGRAMA',
            'CALIDAD',
        ]);

        $this->command->info('Estaciones y atributos creados exitosamente.');
    }

    private function createAttributes(Station $station, array $attributeNames): void
    {
        foreach ($attributeNames as $index => $name) {
            Attribute::create([
                'station_id' => $station->id,
                'name' => $name,
                'color' => 'verde',
                'order' => $index + 1,
                'active' => true,
            ]);
        }
    }
}
