<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;

class PlatformSettings
{
    protected static string $cacheKey = 'academyhub_global_pricing_settings';

    /**
     * Get the absolute path to the global pricing JSON file.
     */
    public static function settingsPath(): string
    {
        return storage_path('app/academyhub/global_pricing.json');
    }

    /**
     * Get the termly fee per student. Defaults to 1000.00.
     */
    public static function getStudentTermlyFee(): float
    {
        $settings = Cache::rememberForever(self::$cacheKey, function () {
            $path = self::settingsPath();
            if (!File::exists($path)) {
                return ['student_termly_fee' => 1000.00];
            }
            try {
                $data = json_decode(File::get($path), true);
                return is_array($data) ? $data : ['student_termly_fee' => 1000.00];
            } catch (\Throwable $e) {
                return ['student_termly_fee' => 1000.00];
            }
        });

        return (float) ($settings['student_termly_fee'] ?? 1000.00);
    }

    /**
     * Set the termly fee per student and clear the cache.
     */
    public static function setStudentTermlyFee(float $fee): void
    {
        $path = self::settingsPath();
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $data = ['student_termly_fee' => $fee];
        File::put($path, json_encode($data, JSON_PRETTY_PRINT));
        Cache::forget(self::$cacheKey);
    }
}
