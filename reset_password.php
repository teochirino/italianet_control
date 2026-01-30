<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ExternalUser;
use Illuminate\Support\Facades\Hash;

// Configuración
$email = 'arojas@lineaitalia.com.mx';
$newPassword = 'temporal123'; // Cambiar este password

echo "=== Resetear Password ===\n\n";

$externalUser = ExternalUser::where('email', $email)->first();

if (!$externalUser) {
    echo "ERROR: Usuario no encontrado\n";
    exit(1);
}

echo "Usuario encontrado:\n";
echo "  ID: {$externalUser->id}\n";
echo "  Email: {$externalUser->email}\n";
echo "  Nombre: {$externalUser->full_name}\n\n";

echo "Actualizando password a: $newPassword\n";

$externalUser->password = Hash::make($newPassword);
$externalUser->save();

echo "\n✓ Password actualizado exitosamente!\n";
echo "\nAhora puedes hacer login con:\n";
echo "  Email: {$externalUser->email}\n";
echo "  Password: $newPassword\n";
