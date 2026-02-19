<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('attributes')->orderBy('id')->chunk(100, function ($attributes) {
            foreach ($attributes as $attribute) {
                $lastChange = DB::table('color_change_histories')
                    ->where('attribute_id', $attribute->id)
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                $colorChangedAt = $lastChange ? $lastChange->created_at : $attribute->created_at;
                
                DB::table('attributes')
                    ->where('id', $attribute->id)
                    ->update(['color_changed_at' => $colorChangedAt]);
            }
        });

        DB::table('stations')->orderBy('id')->chunk(100, function ($stations) {
            foreach ($stations as $station) {
                $lastChange = DB::table('color_change_histories')
                    ->where('station_id', $station->id)
                    ->whereNull('attribute_id')
                    ->orWhere(function($query) use ($station) {
                        $query->where('station_id', $station->id)
                              ->whereNotNull('attribute_id');
                    })
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                $colorChangedAt = $lastChange ? $lastChange->created_at : $station->created_at;
                
                DB::table('stations')
                    ->where('id', $station->id)
                    ->update(['color_changed_at' => $colorChangedAt]);
            }
        });
    }

    public function down(): void
    {
    }
};
