<?php

namespace App\Console\Commands;

use App\Models\ExternalUser;
use App\Models\User;
use Illuminate\Console\Command;

class LinkExternalUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:link-external {email?} {--is-admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vincular un usuario externo con un usuario local de la aplicación';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?? $this->ask('Email del usuario externo');

        $externalUser = ExternalUser::active()
            ->where('email', $email)
            ->first();

        if (!$externalUser) {
            $this->error('No se encontró un usuario externo activo con ese email.');
            return 1;
        }

        $existingUser = User::where('main_user_id', $externalUser->id)->first();

        if ($existingUser) {
            $this->warn('Este usuario externo ya está vinculado con un usuario local.');
            $this->info('Usuario local ID: ' . $existingUser->id);
            $this->info('Nombre: ' . $existingUser->name);
            $this->info('Email: ' . $existingUser->email);
            return 0;
        }

        $this->info('Usuario externo encontrado:');
        $this->info('ID: ' . $externalUser->id);
        $this->info('Nombre: ' . $externalUser->full_name);
        $this->info('Email: ' . $externalUser->email);
        $this->info('Nómina: ' . $externalUser->nomina);

        if (!$this->confirm('¿Deseas crear un usuario local vinculado a este usuario externo?')) {
            $this->info('Operación cancelada.');
            return 0;
        }

        $isAdmin = $this->option('is-admin') || $this->confirm('¿Es administrador?', false);

        $user = User::create([
            'main_user_id' => $externalUser->id,
            'name' => $externalUser->full_name,
            'email' => $externalUser->email,
            'password' => '',
            'is_admin' => $isAdmin,
        ]);

        $this->info('');
        $this->info('✓ Usuario local creado exitosamente!');
        $this->info('ID: ' . $user->id);
        $this->info('Nombre: ' . $user->name);
        $this->info('Email: ' . $user->email);
        $this->info('Es admin: ' . ($user->is_admin ? 'Sí' : 'No'));
        $this->info('Vinculado a ExternalUser ID: ' . $user->main_user_id);

        return 0;
    }
}
