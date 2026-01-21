<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function destroy(User $user)
    {
        if ($user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar un usuario administrador.'
            ], 400);
        }

        $user->assignedStations()->detach();
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Usuario eliminado exitosamente.'
        ]);
    }

}
