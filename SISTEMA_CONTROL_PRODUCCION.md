# Sistema de Control de Planta de Producción

## Descripción General

Sistema de control en tiempo real para planta de producción que permite monitorear el estado de divisiones, estaciones y atributos mediante un sistema de colores jerárquico.

## Características Principales

### 1. Jerarquía de Colores

El sistema implementa una jerarquía de colores automática:

- **Rojo**: Máxima prioridad - Si al menos un atributo está en rojo, la estación está en rojo
- **Amarillo**: Segunda prioridad - Si no hay rojo pero hay amarillo, la estación está en amarillo
- **Gris**: Tercera prioridad - Condición especial:
  - Si el atributo "PROGRAMA" está en gris, la estación se pone en gris
  - Si otros atributos están en gris pero no "PROGRAMA", no afecta la jerarquía
- **Verde**: Estado normal - Todos los atributos están en verde

### 2. Estructura del Sistema

```
División (Ej: ACERO, MADERA, ENSAMBLES FINALES)
  └── Estación (Ej: TROQUELES, CORTE LASER)
      └── Atributos (Ej: MAQUINARIA, M.O., PROGRAMA, CALIDAD)
```

### 3. Roles de Usuario

#### Super Usuario (is_admin = 1)
- Acceso completo al sistema
- CRUD de Divisiones, Estaciones y Atributos
- Asignación de usuarios a estaciones
- Visualización de todo el panel de control

#### Usuario Normal (is_admin = 0)
- Solo puede actualizar colores de atributos en estaciones asignadas
- Visualiza únicamente las estaciones a las que tiene acceso
- No puede crear, editar o eliminar elementos

## Estructura de Base de Datos

### Tablas Creadas

1. **users** (modificada)
   - Agregado campo: `is_admin` (boolean)

2. **divisions**
   - id, name, color, order, active, timestamps

3. **stations**
   - id, division_id, name, color, order, active, timestamps

4. **attributes**
   - id, station_id, name, color (enum: rojo, amarillo, verde, gris), order, active, timestamps

5. **user_station_assignments**
   - id, user_id, station_id, timestamps
   - Unique constraint: (user_id, station_id)

## Instalación y Configuración

### 1. Ejecutar Migraciones

```bash
php artisan migrate
```

### 2. Ejecutar Seeders (Datos de Ejemplo)

```bash
php artisan db:seed
```

Esto creará:
- 1 usuario administrador: admin@control.com / password
- 2 usuarios normales: pedro@control.com / password, maria@control.com / password
- 3 divisiones: ACERO, MADERA, ENSAMBLES FINALES
- 2 estaciones en ACERO: TROQUELES, CORTE LASER
- 4 atributos por estación: MAQUINARIA, M.O., PROGRAMA, CALIDAD

### 3. Compilar Assets Frontend

```bash
npm install
npm run build
```

Para desarrollo:
```bash
npm run dev
```

### 4. Iniciar Servidor

```bash
php artisan serve
```

## Uso del Sistema

### Panel de Control Principal (Dashboard)

**Ruta**: `/dashboard`

- Muestra todas las divisiones con sus estaciones y atributos
- Los usuarios normales solo ven las estaciones asignadas
- Actualización automática cada 5 segundos
- Actualización en tiempo real al cambiar colores

#### Cambiar Color de Atributo

1. Hacer clic en uno de los 4 botones de color (rojo, amarillo, verde, gris)
2. El color se actualiza inmediatamente
3. La estación recalcula su color automáticamente
4. La división recalcula su color automáticamente

### Panel de Administración (Solo Super Usuarios)

#### Gestionar Divisiones
**Ruta**: `/admin/divisions`

- Crear, editar, eliminar divisiones
- Definir orden de visualización
- Activar/desactivar divisiones

#### Gestionar Estaciones
**Ruta**: `/admin/stations`

- Crear, editar, eliminar estaciones
- Asignar a divisiones
- Definir orden de visualización
- Activar/desactivar estaciones

#### Gestionar Atributos
**Ruta**: `/admin/attributes`

- Crear, editar, eliminar atributos
- Asignar a estaciones
- Definir color inicial
- Definir orden de visualización
- Activar/desactivar atributos

#### Asignar Usuarios a Estaciones
**Ruta**: `/admin/user-assignments`

- Asignar usuarios normales a estaciones específicas
- Un usuario puede tener acceso a múltiples estaciones
- Eliminar asignaciones

## Lógica de Actualización de Colores

### Modelo Attribute

Cuando se guarda o elimina un atributo:
```php
protected static function booted(): void
{
    static::saved(function (Attribute $attribute) {
        $attribute->station->updateColor();
    });
}
```

### Modelo Station

Método `updateColor()`:
1. Obtiene todos los atributos activos
2. Verifica si el atributo "PROGRAMA" está en gris → Estación = gris
3. Si no, verifica si hay algún atributo en rojo → Estación = rojo
4. Si no, verifica si hay algún atributo en amarillo → Estación = amarillo
5. Si todos están en verde → Estación = verde
6. Actualiza el color de la división padre

### Modelo Division

Método `updateColor()`:
1. Obtiene todas las estaciones activas
2. Aplica la misma jerarquía de colores
3. Actualiza su propio color

## API Endpoints

### Actualizar Color de Atributo
```
POST /attributes/{attribute}/update-color
Body: { "color": "rojo|amarillo|verde|gris" }
Middleware: auth, can-update-attribute
```

### Obtener Datos del Dashboard
```
GET /api/dashboard-data
Middleware: auth, verified
```

## Middleware de Seguridad

### IsAdmin
- Verifica que el usuario tenga `is_admin = true`
- Protege todas las rutas de administración

### CanUpdateAttribute
- Verifica que el usuario sea admin O tenga acceso a la estación del atributo
- Protege la actualización de colores

## Características Técnicas

### Frontend
- **Framework**: Vue.js 3 con Composition API
- **Routing**: Inertia.js
- **Estilos**: Tailwind CSS
- **HTTP Client**: Axios

### Backend
- **Framework**: Laravel 12
- **ORM**: Eloquent
- **Autenticación**: Laravel Breeze
- **Validación**: Form Requests

### Actualización en Tiempo Real
- Polling cada 5 segundos en el dashboard
- Actualización inmediata al cambiar colores vía AJAX
- Recálculo automático de colores en cascada (Atributo → Estación → División)

## Estructura de Archivos Creados

### Migraciones
- `2024_01_20_000001_add_is_admin_to_users_table.php`
- `2024_01_20_000002_create_divisions_table.php`
- `2024_01_20_000003_create_stations_table.php`
- `2024_01_20_000004_create_attributes_table.php`
- `2024_01_20_000005_create_user_station_assignments_table.php`

### Modelos
- `app/Models/Division.php`
- `app/Models/Station.php`
- `app/Models/Attribute.php`
- `app/Models/UserStationAssignment.php`
- `app/Models/User.php` (modificado)

### Controladores
- `app/Http/Controllers/DivisionController.php`
- `app/Http/Controllers/StationController.php`
- `app/Http/Controllers/AttributeController.php`
- `app/Http/Controllers/UserStationAssignmentController.php`
- `app/Http/Controllers/DashboardController.php`

### Middleware
- `app/Http/Middleware/IsAdmin.php`
- `app/Http/Middleware/CanUpdateAttribute.php`

### Vistas (Vue.js)
- `resources/js/Pages/Dashboard.vue`
- `resources/js/Pages/Admin/Divisions/Index.vue`
- `resources/js/Pages/Admin/Divisions/Create.vue`
- `resources/js/Pages/Admin/Divisions/Edit.vue`
- `resources/js/Pages/Admin/Stations/Index.vue`
- `resources/js/Pages/Admin/Stations/Create.vue`
- `resources/js/Pages/Admin/Stations/Edit.vue`
- `resources/js/Pages/Admin/Attributes/Index.vue`
- `resources/js/Pages/Admin/Attributes/Create.vue`
- `resources/js/Pages/Admin/Attributes/Edit.vue`
- `resources/js/Pages/Admin/UserAssignments/Index.vue`

### Seeders
- `database/seeders/ProductionControlSeeder.php`

## Credenciales de Prueba

Después de ejecutar el seeder:

**Administrador:**
- Email: admin@control.com
- Password: password

**Usuario Normal 1:**
- Email: pedro@control.com
- Password: password
- Acceso: Estación TROQUELES

**Usuario Normal 2:**
- Email: maria@control.com
- Password: password
- Acceso: Estación CORTE LASER

## Notas Importantes

1. **Actualización Automática**: El dashboard se actualiza automáticamente cada 5 segundos para reflejar cambios realizados por otros usuarios.

2. **Jerarquía de Colores**: La jerarquía se aplica automáticamente. No es necesario actualizar manualmente los colores de estaciones o divisiones.

3. **Atributo PROGRAMA**: Tiene comportamiento especial. Si está en gris, fuerza a la estación a estar en gris independientemente de otros atributos.

4. **Permisos**: Los usuarios normales solo pueden actualizar colores de atributos en estaciones asignadas. No pueden crear, editar o eliminar elementos.

5. **Elementos Inactivos**: Los elementos marcados como `active = false` no se muestran en el dashboard ni afectan el cálculo de colores.

## Soporte y Mantenimiento

Para agregar nuevas divisiones, estaciones o atributos:
1. Iniciar sesión como administrador
2. Usar los paneles de administración correspondientes
3. Asignar usuarios a las nuevas estaciones si es necesario

Para modificar la lógica de colores:
- Editar `app/Models/Station.php` método `updateColor()`
- Editar `app/Models/Division.php` método `updateColor()`
