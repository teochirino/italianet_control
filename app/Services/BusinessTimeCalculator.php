<?php

namespace App\Services;

use App\Models\Holiday;
use Carbon\Carbon;

class BusinessTimeCalculator
{
    private const WORK_START_HOUR = 8;
    private const WORK_END_HOUR = 18;
    private const WORK_HOURS_PER_DAY = 10;
    private const TIMEZONE = 'America/Mexico_City';

    public static function calculateBusinessHours(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        $startCarbon = Carbon::parse($start)->setTimezone(self::TIMEZONE);
        $endCarbon = Carbon::parse($end)->setTimezone(self::TIMEZONE);

        if ($startCarbon->greaterThanOrEqualTo($endCarbon)) {
            return [
                'days' => 0,
                'hours' => 0,
                'minutes' => 0,
                'seconds' => 0,
                'total_seconds' => 0,
                'formatted' => '0 segundos',
            ];
        }

        $totalSeconds = 0;
        $current = $startCarbon->copy();

        $holidays = Holiday::getHolidaysInRange($startCarbon, $endCarbon);

        while ($current->lessThan($endCarbon)) {
            if (self::isBusinessDay($current, $holidays)) {
                $dayStart = $current->copy()->setTime(self::WORK_START_HOUR, 0, 0);
                $dayEnd = $current->copy()->setTime(self::WORK_END_HOUR, 0, 0);

                $periodStart = $current->copy();
                $periodEnd = $endCarbon->copy();

                if (!$current->isSameDay($endCarbon)) {
                    $periodEnd = $current->copy()->endOfDay();
                }

                if ($periodStart->lessThan($dayStart)) {
                    $periodStart = $dayStart->copy();
                }

                if ($periodEnd->greaterThan($dayEnd)) {
                    $periodEnd = $dayEnd->copy();
                }

                if ($periodStart->lessThan($periodEnd)) {
                    $secondsInPeriod = $periodEnd->diffInSeconds($periodStart);
                    $totalSeconds += $secondsInPeriod;
                }
            }

            $current->addDay()->startOfDay();
        }

        return self::formatDuration($totalSeconds);
    }

    public static function isCurrentlyInBusinessHours(): bool
    {
        $now = Carbon::now(self::TIMEZONE);
        
        if ($now->isWeekend()) {
            return false;
        }

        if (Holiday::isHoliday($now)) {
            return false;
        }

        $hour = $now->hour;
        return $hour >= self::WORK_START_HOUR && $hour < self::WORK_END_HOUR;
    }

    public static function getNextBusinessTime(): ?Carbon
    {
        $now = Carbon::now(self::TIMEZONE);
        $next = $now->copy();

        for ($i = 0; $i < 30; $i++) {
            if ($next->isWeekend()) {
                $next->next(Carbon::MONDAY)->setTime(self::WORK_START_HOUR, 0, 0);
                continue;
            }

            if (Holiday::isHoliday($next)) {
                $next->addDay()->setTime(self::WORK_START_HOUR, 0, 0);
                continue;
            }

            if ($next->hour < self::WORK_START_HOUR) {
                return $next->setTime(self::WORK_START_HOUR, 0, 0);
            }

            if ($next->hour >= self::WORK_END_HOUR) {
                $next->addDay()->setTime(self::WORK_START_HOUR, 0, 0);
                continue;
            }

            return $next;
        }

        return null;
    }

    private static function isBusinessDay(Carbon $date, array $holidays): bool
    {
        if ($date->isWeekend()) {
            return false;
        }

        $dateString = $date->format('Y-m-d');
        return !in_array($dateString, $holidays);
    }

    private static function formatDuration(int $totalSeconds): array
    {
        $days = floor($totalSeconds / 86400);
        $hours = floor(($totalSeconds % 86400) / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;

        $parts = [];

        if ($days > 0) {
            $parts[] = $days . ($days === 1 ? ' día' : ' días');
        }
        if ($hours > 0) {
            $parts[] = $hours . ($hours === 1 ? ' hora' : ' horas');
        }
        if ($minutes > 0) {
            $parts[] = $minutes . ($minutes === 1 ? ' minuto' : ' minutos');
        }
        if ($seconds > 0 && $days === 0 && $hours === 0) {
            $parts[] = $seconds . ($seconds === 1 ? ' segundo' : ' segundos');
        }

        if (empty($parts)) {
            $formatted = '0 segundos';
        } else {
            $formatted = implode(', ', $parts);
        }

        return [
            'days' => (int)$days,
            'hours' => (int)$hours,
            'minutes' => (int)$minutes,
            'seconds' => (int)$seconds,
            'total_seconds' => $totalSeconds,
            'formatted' => $formatted,
        ];
    }

    public static function formatDurationShort(int $totalSeconds): string
    {
        $days = floor($totalSeconds / 86400);
        $hours = floor(($totalSeconds % 86400) / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;

        $parts = [];

        if ($days > 0) {
            $parts[] = $days . 'd';
        }
        if ($hours > 0) {
            $parts[] = $hours . 'h';
        }
        if ($minutes > 0) {
            $parts[] = $minutes . 'm';
        }
        if ($days === 0 && $hours === 0 && $minutes === 0) {
            $parts[] = $seconds . 's';
        }

        return !empty($parts) ? implode(' ', $parts) : '0s';
    }
}
