<?php

namespace App\Support;

use Carbon\Carbon;

class AttendanceShiftConfig
{
    public const SHIFT_WESTERN = 'Western';
    public const SHIFT_ISLAMIC = 'Islamic';

    public static function normalizeShift(?string $shift): string
    {
        if (!$shift) {
            return self::SHIFT_WESTERN;
        }

        $s = strtolower(trim($shift));
        if (in_array($s, ['islamic', 'afternoon', 'arabiyyah', 'islamiyyah'], true)) {
            return self::SHIFT_ISLAMIC;
        }

        return self::SHIFT_WESTERN;
    }

    /**
     * Retrieve the configuration array for a given shift.
     *
     * @return array{start_time: string, late_threshold: string, end_time: string, formatted_start: string, formatted_late: string, formatted_end: string, formatted_range: string}
     */
    public static function getShiftConfig(string $shift = self::SHIFT_WESTERN, ?int $tenantId = null): array
    {
        $norm = self::normalizeShift($shift);

        if ($norm === self::SHIFT_ISLAMIC) {
            $start = (string) TenantSettings::getForTenant('islamic_start_time', $tenantId, config('academyhub.islamic_start_time', '12:00:00'));
            $late  = (string) TenantSettings::getForTenant('islamic_late_threshold', $tenantId, config('academyhub.islamic_late_threshold', '12:45:00'));
            $end   = (string) TenantSettings::getForTenant('islamic_end_time', $tenantId, config('academyhub.islamic_end_time', '17:00:00'));
        } else {
            $start = (string) TenantSettings::getForTenant('western_start_time', $tenantId, config('academyhub.western_start_time', '07:00:00'));
            $late  = (string) TenantSettings::getForTenant('western_late_threshold', $tenantId, config('academyhub.western_late_threshold', '08:15:00'));
            $end   = (string) TenantSettings::getForTenant('western_end_time', $tenantId, config('academyhub.western_end_time', '12:30:00'));
        }

        // Standardize time strings (H:i:s)
        $start = self::standardizeTime($start, $norm === self::SHIFT_ISLAMIC ? '12:00:00' : '07:00:00');
        $late  = self::standardizeTime($late, $norm === self::SHIFT_ISLAMIC ? '12:45:00' : '08:15:00');
        $end   = self::standardizeTime($end, $norm === self::SHIFT_ISLAMIC ? '17:00:00' : '12:30:00');

        return [
            'shift'            => $norm,
            'label'            => $norm . ' Section',
            'start_time'       => $start,
            'late_threshold'   => $late,
            'end_time'         => $end,
            'formatted_start'  => self::formatTime($start),
            'formatted_late'   => self::formatTime($late),
            'formatted_end'    => self::formatTime($end),
            'formatted_range'  => self::formatTime($start) . ' – ' . self::formatTime($end),
        ];
    }

    public static function getLateThreshold(string $shift = self::SHIFT_WESTERN, ?int $tenantId = null): string
    {
        return self::getShiftConfig($shift, $tenantId)['late_threshold'];
    }

    public static function getStartTime(string $shift = self::SHIFT_WESTERN, ?int $tenantId = null): string
    {
        return self::getShiftConfig($shift, $tenantId)['start_time'];
    }

    public static function getEndTime(string $shift = self::SHIFT_WESTERN, ?int $tenantId = null): string
    {
        return self::getShiftConfig($shift, $tenantId)['end_time'];
    }

    public static function allShifts(?int $tenantId = null): array
    {
        return [
            self::SHIFT_WESTERN => self::getShiftConfig(self::SHIFT_WESTERN, $tenantId),
            self::SHIFT_ISLAMIC => self::getShiftConfig(self::SHIFT_ISLAMIC, $tenantId),
        ];
    }

    /**
     * Evaluate arrival punch time: returns 'Present' or 'Late' based on shift threshold.
     */
    public static function evaluateStatus(string $timeStr, string $shift = self::SHIFT_WESTERN, ?int $tenantId = null): string
    {
        $config = self::getShiftConfig($shift, $tenantId);
        $time = self::standardizeTime($timeStr, '00:00:00');

        return ($time <= $config['late_threshold']) ? 'Present' : 'Late';
    }

    public static function formatTime(?string $time): string
    {
        if (empty($time)) {
            return '';
        }

        try {
            return Carbon::parse($time)->format('g:i A');
        } catch (\Throwable) {
            return (string) $time;
        }
    }

    public static function standardizeTime(string $time, string $default = '00:00:00'): string
    {
        $trimmed = trim($time);
        if (empty($trimmed)) {
            return $default;
        }

        // If format is already HH:MM:SS
        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $trimmed)) {
            return $trimmed;
        }

        // If format is HH:MM
        if (preg_match('/^\d{1,2}:\d{2}$/', $trimmed)) {
            $parts = explode(':', $trimmed);
            return sprintf('%02d:%02d:00', (int) $parts[0], (int) $parts[1]);
        }

        try {
            return Carbon::parse($trimmed)->format('H:i:s');
        } catch (\Throwable) {
            return $default;
        }
    }
}
