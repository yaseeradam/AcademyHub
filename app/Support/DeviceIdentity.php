<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DeviceIdentity
{
    public static function id(): string
    {
        $path = storage_path('app/academyhub/device-id.txt');

        try {
            if (File::exists($path)) {
                $id = trim((string) File::get($path));
                if ($id !== '') {
                    return $id;
                }
            }

            File::ensureDirectoryExists(dirname($path));
            $id = (string) Str::uuid();
            File::put($path, $id);
            return $id;
        } catch (\Throwable) {
            return (string) Str::uuid();
        }
    }

    public static function label(): string
    {
        $name = (string) (getenv('COMPUTERNAME') ?: php_uname('n') ?: 'Device');
        return trim($name) !== '' ? $name : 'Device';
    }
}

