<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Attribute;

class CanUpdateAttribute
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if ($user->is_admin) {
            return $next($request);
        }

        $attributeId = $request->route('attribute')?->id ?? $request->input('attribute_id');
        
        if (!$attributeId) {
            abort(403, 'No se especificó el atributo.');
        }

        $attribute = Attribute::find($attributeId);
        
        if (!$attribute) {
            abort(404, 'Atributo no encontrado.');
        }

        $hasAccess = $user->visibleStations()
            ->where('stations.id', $attribute->station_id)
            ->exists();

        if (!$hasAccess) {
            abort(403, 'No tienes permisos para actualizar este atributo.');
        }

        return $next($request);
    }
}
