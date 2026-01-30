# Autenticación con ExternalUser

## Resumen de Cambios

Se ha implementado un sistema de autenticación que utiliza usuarios externos de la base de datos `italianet_users` para autenticar usuarios en la aplicación de control.

## Arquitectura

### Relación 1:1 entre User y ExternalUser

- **User** (tabla `users` en base de datos `italianet_control`): Usuario local de la aplicación
- **ExternalUser** (tabla `users` en base de datos `italianet_users`): Usuario externo centralizado
- **Campo de relación**: `main_user_id` en tabla `users` apunta al `id` de `ExternalUser`

### Flujo de Autenticación

1. El usuario ingresa email y password en el formulario de login
2. Fortify busca el `ExternalUser` por email usando el scope `active()`
3. Se valida que el `ExternalUser`:
   - Tenga email válido (not null, not empty)
   - Tenga `status = 1` (activo)
   - Tenga password correcto
4. Se verifica que el `ExternalUser` tenga relación con un `User` local
5. Si no tiene relación, se muestra error: "No tienes permisos para acceder"
6. Si todo es válido, se autentica al `User` local asociado

## Archivos Modificados

### 1. Migración
- `database/migrations/2026_01_30_151619_add_main_user_id_to_users_table.php`
  - Agrega campo `main_user_id` (unsignedBigInteger, nullable, unique)

### 2. Modelos

#### User.php
```php
protected $fillable = [
    'main_user_id',
    // ... otros campos
];

public function externalUser()
{
    return $this->setConnection('italianet_users')->belongsTo(
        ExternalUser::class,
        'main_user_id',
        'id'
    );
}
```

#### ExternalUser.php
```php
public function user()
{
    return $this->setConnection('mysql')
        ->hasOne(User::class, 'main_user_id', 'id');
}
```

### 3. Providers
- `app/Providers/FortifyServiceProvider.php`: Lógica de autenticación personalizada
- `bootstrap/providers.php`: Registro de FortifyServiceProvider

### 4. Configuración
- `config/fortify.php`: Configuración de Fortify (home path: `/dashboard`)
- `routes/web.php`: Rutas de Breeze comentadas (Fortify las maneja)

## Validaciones Implementadas

✅ ExternalUser debe tener email válido (not null, not empty)
✅ ExternalUser debe tener `status = 1`
✅ ExternalUser debe tener relación con User (`main_user_id` no nulo)
✅ Si no tiene relación, denegar acceso con mensaje apropiado
✅ Verificación de password usando Hash::check()

## Cómo Vincular un ExternalUser con un User

Para que un usuario externo pueda acceder a la aplicación, debe tener un registro en la tabla `users` local con el campo `main_user_id` apuntando a su ID en la base de datos externa.

### Ejemplo SQL:
```sql
-- Crear usuario local vinculado a usuario externo con ID 123
INSERT INTO users (main_user_id, name, email, password, is_admin, created_at, updated_at)
VALUES (123, 'Nombre Usuario', 'email@example.com', '', 0, NOW(), NOW());
```

### Ejemplo con Eloquent:
```php
use App\Models\User;
use App\Models\ExternalUser;

$externalUser = ExternalUser::find(123);

$user = User::create([
    'main_user_id' => $externalUser->id,
    'name' => $externalUser->full_name,
    'email' => $externalUser->email,
    'password' => '', // No se usa, la autenticación usa el password de ExternalUser
    'is_admin' => false,
]);
```

## Pruebas

### 1. Probar autenticación exitosa
- Usuario externo con email válido, status = 1, y relación con User local
- Debe autenticarse correctamente y redirigir a `/dashboard`

### 2. Probar usuario sin permisos
- Usuario externo con email válido, status = 1, pero SIN relación con User local
- Debe mostrar error: "No tienes permisos para acceder"

### 3. Probar usuario inactivo
- Usuario externo con status = 0
- Debe fallar la autenticación (credenciales inválidas)

### 4. Probar usuario sin email
- Usuario externo sin email válido
- Debe fallar la autenticación (credenciales inválidas)

## Notas Importantes

- El password se valida contra la tabla `users` de `italianet_users` (ExternalUser)
- El campo `password` en la tabla `users` local no se usa para autenticación
- La sesión se crea con el `User` local, no con el `ExternalUser`
- Todas las vistas de autenticación usan Inertia.js (Vue)
- Rate limiting: 5 intentos por minuto por email/IP

## Comandos Útiles

### Vincular Usuario Externo con Usuario Local

```bash
# Modo interactivo
php artisan user:link-external

# Con email como argumento
php artisan user:link-external usuario@example.com

# Crear como administrador
php artisan user:link-external usuario@example.com --is-admin
```

### Otros Comandos

```bash
# Ejecutar migraciones
php artisan migrate

# Limpiar cache de configuración
php artisan config:clear

# Limpiar cache de rutas
php artisan route:clear

# Ver rutas de Fortify
php artisan route:list --name=login
```
