# Sistema de Tiempo Laboral Inteligente

## 📋 Descripción

Este sistema implementa un contador de tiempo "inteligente" que solo contabiliza horas laborales, excluyendo:
- **Fines de semana** (sábado y domingo)
- **Horas fuera de jornada laboral** (antes de 8:00 AM y después de 6:00 PM)
- **Días festivos** configurables en la base de datos

**Zona horaria:** América/Ciudad de México (`America/Mexico_City`)

## 🎯 Funcionamiento

### Ejemplo Práctico
Si un atributo cambia de color:
- **Lunes 5:00 PM** → Cambio de color
- **Lunes 5:00 PM - 6:00 PM** = 1 hora contada
- **Lunes 6:00 PM - Martes 8:00 AM** = 0 horas (fuera de horario)
- **Martes 8:00 AM** → Muestra **1 hora total**

### Indicador Visual
Cuando estás fuera del horario laboral, el contador muestra un ícono de pausa (⏸️) indicando que el tiempo está "detenido".

## 📁 Archivos Creados/Modificados

### Backend (PHP/Laravel)
1. **`database/migrations/2026_03_03_120000_create_holidays_table.php`**
   - Migración para tabla de días festivos

2. **`app/Models/Holiday.php`**
   - Modelo para gestionar días festivos
   - Métodos: `isHoliday()`, `getHolidaysInRange()`

3. **`app/Services/BusinessTimeCalculator.php`**
   - Servicio principal para cálculos de tiempo laboral
   - Métodos principales:
     - `calculateBusinessHours()` - Calcula tiempo laboral entre dos fechas
     - `isCurrentlyInBusinessHours()` - Verifica si estamos en horario laboral
     - `getNextBusinessTime()` - Obtiene el próximo horario laboral

4. **`app/Http/Controllers/ColorChangeHistoryController.php`**
   - Actualizado para usar `BusinessTimeCalculator`

5. **`app/Console/Commands/VerifyBusinessTimeCalculations.php`**
   - Comando para verificar cálculos: `php artisan time:verify-business-hours`

6. **`database/seeders/HolidaySeeder.php`**
   - Seeder con días festivos de México 2026

### Frontend (Vue.js)
1. **`resources/js/utils/businessTimeCalculator.js`**
   - Helper JavaScript para cálculos en el frontend
   - Funciones: `calculateBusinessHours()`, `isCurrentlyInBusinessHours()`

2. **`resources/js/Pages/Dashboard.vue`**
   - Actualizado para usar tiempo laboral inteligente
   - Muestra ícono de pausa cuando está fuera de horario

## 🚀 Comandos Útiles

### Verificar Cálculos
```bash
php artisan time:verify-business-hours
```
Muestra una tabla con los últimos 50 cambios de color y sus tiempos laborales calculados.

### Cargar Días Festivos
```bash
php artisan db:seed --class=HolidaySeeder
```

### Recompilar Assets Frontend
```bash
npm run build
# o para desarrollo
npm run dev
```

## 🗄️ Gestión de Días Festivos

### Agregar Días Festivos Manualmente
```php
use App\Models\Holiday;

Holiday::create([
    'date' => '2026-12-31',
    'name' => 'Fin de Año',
    'description' => 'Último día del año',
    'active' => true,
]);
```

### Consultar Días Festivos
```sql
SELECT * FROM holidays WHERE active = 1 ORDER BY date;
```

### Desactivar un Día Festivo
```sql
UPDATE holidays SET active = 0 WHERE date = '2026-01-01';
```

## 🔄 Cómo Revertir los Cambios

Si necesitas volver al sistema anterior (tiempo continuo sin pausas):

### Opción 1: Revertir Migración
```bash
php artisan migrate:rollback --step=1
```
Esto eliminará la tabla `holidays`.

### Opción 2: Restaurar Archivos Manualmente

1. **Restaurar `ColorChangeHistoryController.php`:**
```php
// En el método calculateTimeDifference(), reemplazar:
return BusinessTimeCalculator::calculateBusinessHours($startTime, $endTime);

// Por el código original:
$interval = $startTime->diff($endTime);
$days = $interval->days;
$hours = $interval->h;
$minutes = $interval->i;
$seconds = $interval->s;

return [
    'days' => $days,
    'hours' => $hours,
    'minutes' => $minutes,
    'seconds' => $seconds,
    'total_seconds' => ($days * 86400) + ($hours * 3600) + ($minutes * 60) + $seconds,
    'formatted' => $this->formatDuration($days, $hours, $minutes, $seconds),
];
```

2. **Restaurar `Dashboard.vue`:**
```javascript
// Eliminar la importación:
import { calculateBusinessHours, isCurrentlyInBusinessHours } from '@/utils/businessTimeCalculator';

// Restaurar calculateTimeInColor() al código original (ver backup)
```

3. **Eliminar archivos creados:**
```bash
# Backend
rm app/Models/Holiday.php
rm app/Services/BusinessTimeCalculator.php
rm app/Console/Commands/VerifyBusinessTimeCalculations.php
rm database/seeders/HolidaySeeder.php

# Frontend
rm resources/js/utils/businessTimeCalculator.js
```

4. **Recompilar assets:**
```bash
npm run build
```

## 📊 Configuración

### Cambiar Horario Laboral
Editar en `app/Services/BusinessTimeCalculator.php`:
```php
private const WORK_START_HOUR = 8;  // Hora de inicio
private const WORK_END_HOUR = 18;   // Hora de fin
```

Y en `resources/js/utils/businessTimeCalculator.js`:
```javascript
const WORK_START_HOUR = 8;
const WORK_END_HOUR = 18;
```

### Cambiar Zona Horaria
Editar en `app/Services/BusinessTimeCalculator.php`:
```php
private const TIMEZONE = 'America/Mexico_City';
```

Y en `resources/js/utils/businessTimeCalculator.js`:
```javascript
const TIMEZONE = 'America/Mexico_City';
```

## 🧪 Testing

### Probar en Local (Venezuela)
El sistema usa zona horaria de México, pero funcionará correctamente desde cualquier ubicación.

### Probar en Producción (México)
1. Hacer deploy de los cambios
2. Ejecutar migraciones: `php artisan migrate`
3. Cargar días festivos: `php artisan db:seed --class=HolidaySeeder`
4. Verificar: `php artisan time:verify-business-hours`

## ⚠️ Notas Importantes

1. **Los timestamps originales NO se modifican** - El campo `color_changed_at` mantiene la fecha/hora real del cambio
2. **Los cálculos son en tiempo real** - No se almacenan duraciones, se calculan al consultar
3. **Días festivos de 2026** - Recuerda actualizar el seeder cada año
4. **Rendimiento** - Los cálculos son eficientes, pero considera cachear si tienes miles de registros

## 📞 Soporte

Para dudas o problemas, revisar:
- Logs de Laravel: `storage/logs/laravel.log`
- Consola del navegador para errores de JavaScript
- Comando de verificación: `php artisan time:verify-business-hours`

---

**Fecha de implementación:** 3 de marzo de 2026
**Versión:** 1.0.0
