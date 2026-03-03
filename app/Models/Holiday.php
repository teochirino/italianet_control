<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'name',
        'description',
        'active',
    ];

    protected $casts = [
        'date' => 'date',
        'active' => 'boolean',
    ];

    public static function isHoliday($date): bool
    {
        $dateString = $date instanceof \DateTime || $date instanceof \DateTimeInterface
            ? $date->format('Y-m-d')
            : $date;

        return static::where('date', $dateString)
            ->where('active', true)
            ->exists();
    }

    public static function getHolidaysInRange($startDate, $endDate): array
    {
        $start = $startDate instanceof \DateTime || $startDate instanceof \DateTimeInterface
            ? $startDate->format('Y-m-d')
            : $startDate;

        $end = $endDate instanceof \DateTime || $endDate instanceof \DateTimeInterface
            ? $endDate->format('Y-m-d')
            : $endDate;

        return static::whereBetween('date', [$start, $end])
            ->where('active', true)
            ->pluck('date')
            ->map(fn($date) => $date->format('Y-m-d'))
            ->toArray();
    }
}
