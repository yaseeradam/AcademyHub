<?php

namespace App\Support;

use Spatie\Browsershot\Browsershot;

class CertificatePdf
{
    /**
     * Generate a PDF from a Blade view using Browsershot (headless Chrome).
     * This gives full CSS support: flexbox, gradients, shadows, transforms, etc.
     */
    public static function fromView(string $view, array $data, string $orientation = 'landscape'): string
    {
        $html = view($view, $data)->render();

        $width = $orientation === 'landscape' ? 842 : 595;
        $height = $orientation === 'landscape' ? 595 : 842;

        $widthMm = $orientation === 'landscape' ? 297 : 210;
        $heightMm = $orientation === 'landscape' ? 210 : 297;

        $pageSize = $orientation === 'landscape' ? 'A4 landscape' : 'A4 portrait';
        $fullPageCss = "<style>
            @page { margin: 0 !important; size: {$pageSize}; }
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                width: {$width}px !important;
                height: {$height}px !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .page {
                box-sizing: border-box !important;
                width: {$width}px !important;
                height: {$height}px !important;
                margin: 0 !important;
                overflow: hidden !important;
            }
        </style>";

        if (str_contains($html, '</head>')) {
            $html = str_replace('</head>', $fullPageCss . '</head>', $html);
        }

        $browsershot = Browsershot::html($html);

        $nodeBinary = config('services.browsershot.node_binary');
        $npmBinary = config('services.browsershot.npm_binary');
        $chromePath = config('services.browsershot.chrome_path') ?: self::findChromePath();

        if (! $nodeBinary && PHP_OS_FAMILY === 'Windows') {
            $windowsNode = 'C:\\Program Files\\nodejs\\node.exe';
            if (is_file($windowsNode)) {
                $nodeBinary = $windowsNode;
            }
        }

        if (! $npmBinary && PHP_OS_FAMILY === 'Windows') {
            $windowsNpm = 'C:\\Program Files\\nodejs\\npm.cmd';
            if (is_file($windowsNpm)) {
                $npmBinary = $windowsNpm;
            }
        }

        if ($nodeBinary) {
            $browsershot->setNodeBinary($nodeBinary);
        }

        if ($npmBinary) {
            $browsershot->setNpmBinary($npmBinary);
        }

        if ($chromePath !== '') {
            $browsershot->setChromePath($chromePath);
        }

        $browsershot
            ->windowSize($width, $height)
            ->emulateMedia('screen')
            ->preferCssPageSize()
            ->format('A4')
            ->landscape($orientation === 'landscape')
            ->showBackground()
            ->waitUntilNetworkIdle()
            ->margins(0, 0, 0, 0)
            ->setOption('args', ['--no-sandbox', '--disable-setuid-sandbox']);

        return $browsershot->pdf();
    }

    /**
     * Locate the Chrome binary installed by Puppeteer.
     */
    private static function findChromePath(): string
    {
        $homeDir = getenv('HOME') ?: getenv('USERPROFILE');
        if (! $homeDir) {
            return '';
        }

        $puppeteerCacheDir = $homeDir
            . DIRECTORY_SEPARATOR . '.cache'
            . DIRECTORY_SEPARATOR . 'puppeteer'
            . DIRECTORY_SEPARATOR . 'chrome';

        if (is_dir($puppeteerCacheDir)) {
            // Find the latest version directory
            $versions = @scandir($puppeteerCacheDir);
            if ($versions) {
                $versions = array_filter($versions, fn($v) => $v !== '.' && $v !== '..');
                rsort($versions); // Latest version first

                foreach ($versions as $version) {
                    $versionDir = $puppeteerCacheDir . DIRECTORY_SEPARATOR . $version;
                    foreach (self::puppeteerChromeCandidates($versionDir) as $chromePath) {
                        if (is_file($chromePath)) {
                            return $chromePath;
                        }
                    }
                }
            }
        }

        foreach (self::systemChromeCandidates() as $chromePath) {
            if (is_file($chromePath)) {
                return $chromePath;
            }
        }

        return '';
    }

    private static function puppeteerChromeCandidates(string $versionDir): array
    {
        return [
            $versionDir . DIRECTORY_SEPARATOR . 'chrome-win64' . DIRECTORY_SEPARATOR . 'chrome.exe',
            $versionDir . DIRECTORY_SEPARATOR . 'chrome-linux64' . DIRECTORY_SEPARATOR . 'chrome',
            $versionDir . DIRECTORY_SEPARATOR . 'chrome-linux' . DIRECTORY_SEPARATOR . 'chrome',
            $versionDir . DIRECTORY_SEPARATOR . 'chrome-mac' . DIRECTORY_SEPARATOR . 'Chromium.app' . DIRECTORY_SEPARATOR . 'Contents' . DIRECTORY_SEPARATOR . 'MacOS' . DIRECTORY_SEPARATOR . 'Chromium',
            $versionDir . DIRECTORY_SEPARATOR . 'chrome-mac-x64' . DIRECTORY_SEPARATOR . 'Chromium.app' . DIRECTORY_SEPARATOR . 'Contents' . DIRECTORY_SEPARATOR . 'MacOS' . DIRECTORY_SEPARATOR . 'Chromium',
            $versionDir . DIRECTORY_SEPARATOR . 'chrome-mac-arm64' . DIRECTORY_SEPARATOR . 'Chromium.app' . DIRECTORY_SEPARATOR . 'Contents' . DIRECTORY_SEPARATOR . 'MacOS' . DIRECTORY_SEPARATOR . 'Chromium',
        ];
    }

    private static function systemChromeCandidates(): array
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return [
                'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
                'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
            ];
        }

        if (PHP_OS_FAMILY === 'Darwin') {
            return [
                '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
                '/Applications/Chromium.app/Contents/MacOS/Chromium',
            ];
        }

        return [
            '/usr/bin/google-chrome',
            '/usr/bin/google-chrome-stable',
            '/usr/bin/chromium',
            '/usr/bin/chromium-browser',
        ];
    }
}
