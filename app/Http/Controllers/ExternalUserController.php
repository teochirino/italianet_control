<?php

namespace App\Http\Controllers;

use App\Models\ExternalUser;
use App\Models\User;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ExternalUserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        
        $query = ExternalUser::active();
        
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nomina', 'like', "%{$search}%")
                  ->orWhere('apellidopaterno', 'like', "%{$search}%")
                  ->orWhere('apellidomaterno', 'like', "%{$search}%");
            });
        }

        $externalUsers = $query->orderBy('name')
                              ->limit(500)
                              ->get(['id', 'name', 'apellidopaterno', 'apellidomaterno', 'email', 'nomina']);

        $existingUsers = User::where('is_admin', false)
                            ->with('assignedStations.division')
                            ->get()
                            ->keyBy('email');
        
        $users = $externalUsers->map(function($user) use ($existingUsers) {
            $localUser = $existingUsers->get($user->email);
            
            $fullName = trim(implode(' ', array_filter([
                $user->name,
                $user->apellidopaterno,
                $user->apellidomaterno,
            ])));
            
            return [
                'id' => $user->id,
                'name' => $fullName,
                'email' => $user->email,
                'nomina' => $user->nomina,
                'is_imported' => $localUser !== null,
                'local_user_id' => $localUser ? $localUser->id : null,
                'assigned_stations' => $localUser ? $localUser->assignedStations : [],
            ];
        });

        return response()->json([
            'users' => $users
        ]);
    }

    public function import(Request $request)
    {
        $validated = $request->validate([
            'external_user_id' => 'required|integer',
        ]);

        $externalUser = ExternalUser::find($validated['external_user_id']);
        
        if (!$externalUser) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado en la base de datos externa.'
            ], 404);
        }

        $existingUser = User::where('email', $externalUser->email)->first();
        
        if ($existingUser) {
            return response()->json([
                'success' => false,
                'message' => 'Este usuario ya existe en el sistema.'
            ], 400);
        }

        $fullName = trim(implode(' ', array_filter([
            $externalUser->name,
            $externalUser->apellidopaterno,
            $externalUser->apellidomaterno,
        ])));
        
        $user = User::create([
            'main_user_id' => $externalUser->id,
            'name' => $fullName,
            'email' => $externalUser->email,
            'password' => Hash::make('password'),
            'is_admin' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Usuario importado exitosamente.',
            'user' => $user
        ]);
    }
}
