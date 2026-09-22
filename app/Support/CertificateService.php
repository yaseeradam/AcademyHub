<?php

namespace App\Support;

use App\Models\Certificate;
use App\Models\Student;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class CertificateService
{
    public function generate(Certificate $certificate): string
    {
        if (!extension_loaded('gd')) {
            throw new \Exception('GD PHP extension is required. Please enable it in php.ini');
        }

        $student = $certificate->student;
        $template = $certificate->template ?: 'achievement';
        $templatePath = public_path("certificates/templates/{$template}.png");

        if (!file_exists($templatePath)) {
            $templatePath = $this->createDefaultTemplate($template);
        }

        $manager = new ImageManager(new Driver());
        $image = $manager->read($templatePath);

        // Add student name
        $image->text($student->full_name, 877, 520, function ($font) {
            $font->size(48);
            $font->color('#1a1a1a');
            $font->align('center');
            $font->valign('middle');
        });

        // Add certificate title
        $image->text($certificate->title, 877, 380, function ($font) {
            $font->size(32);
            $font->color('#2c5282');
            $font->align('center');
            $font->valign('middle');
        });

        // Add description
        if ($certificate->description) {
            $image->text($certificate->description, 877, 620, function ($font) {
                $font->size(20);
                $font->color('#4a5568');
                $font->align('center');
                $font->valign('middle');
            });
        }

        // Add date
        $image->text($certificate->issue_date->format('F d, Y'), 877, 720, function ($font) {
            $font->size(18);
            $font->color('#4a5568');
            $font->align('center');
            $font->valign('middle');
        });

        // Add school logo if exists
        $logoPath = public_path('uploads/school-logo.png');
        if (file_exists($logoPath)) {
            $logo = $manager->read($logoPath);
            $logo->scale(height: 100);
            $image->place($logo, 'top', 50, 50);
        }

        // Save to storage
        $filename = "certificate_{$certificate->id}_{$student->id}.png";
        $savePath = storage_path("app/certificates/{$filename}");
        
        if (!is_dir(dirname($savePath))) {
            mkdir(dirname($savePath), 0755, true);
        }

        $image->save($savePath);

        return $savePath;
    }

    private function createDefaultTemplate(string $type): string
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->create(1754, 1240);

        // Background
        $image->fill('#ffffff');

        // Border
        $image->drawRectangle(50, 50, function ($rectangle) {
            $rectangle->size(1654, 1140);
            $rectangle->border('#2c5282', 10);
        });

        $image->drawRectangle(70, 70, function ($rectangle) {
            $rectangle->size(1614, 1100);
            $rectangle->border('#d4af37', 3);
        });

        // Save default template
        $savePath = public_path("certificates/templates/{$type}.png");
        $image->save($savePath);

        return $savePath;
    }
}
