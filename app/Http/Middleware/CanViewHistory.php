<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Station;
use App\Models\Attribute;

class CanViewHistory
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if ($user->is_admin) {
            return $next($request);
        }

        $stationId = $request->route('station')?->id;
        $attributeId = $request->route('attribute')?->id;
        
        if ($attributeId) {
            $attribute = Attribute::find($attributeId);
            
            if (!$attribute) {
                abort(404, 'Atributo no encontrado.');
            }
            
            $stationId = $attribute->station_id;
        }
        
        if (!$stationId) {
            abort(403, 'No se especificó la estación o atributo.');
        }

        $hasAccess = $user->visibleStations()
            ->where('stations.id', $stationId)
            ->exists();

        if (!$hasAccess) {
            abort(403, 'No tienes permisos para ver el historial de esta estación o atributo.');
        }

        return $next($request);
    }
}
