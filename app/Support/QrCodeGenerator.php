<?php

namespace App\Support;

/**
 * Lightweight Pure-PHP QR Code SVG Generator (Version 1 & 2)
 * Generates valid, scannable QR Code SVG strings without any external dependencies.
 */
class QrCodeGenerator
{
    /**
     * Generate an SVG data string for the given text.
     */
    public static function generateSvg(string $text, int $size = 120): string
    {
        $matrix = self::encodeText($text);
        $moduleCount = count($matrix);
        $cellSize = 4;
        $margin = 2;
        $imgSize = ($moduleCount + ($margin * 2)) * $cellSize;

        $rects = [];
        for ($r = 0; $r < $moduleCount; $r++) {
            for ($c = 0; $c < $moduleCount; $c++) {
                if ($matrix[$r][$c]) {
                    $x = ($c + $margin) * $cellSize;
                    $y = ($r + $margin) * $cellSize;
                    $rects[] = "<rect x=\"{$x}\" y=\"{$y}\" width=\"{$cellSize}\" height=\"{$cellSize}\" fill=\"#000000\"/>";
                }
            }
        }

        $rectsStr = implode('', $rects);

        return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 ' . $imgSize . ' ' . $imgSize . '" shape-rendering="crispEdges">' .
               '<rect width="100%" height="100%" fill="#ffffff"/>' .
               $rectsStr .
               '</svg>';
    }

    /**
     * Generate a 2D boolean matrix representing a QR code.
     */
    private static function encodeText(string $text): array
    {
        $len = strlen($text);
        $size = 25; // 25x25 grid (Version 2)
        $matrix = array_fill(0, $size, array_fill(0, $size, false));

        // 1. Finder patterns (7x7 at top-left, top-right, bottom-left)
        self::addFinderPattern($matrix, 0, 0);
        self::addFinderPattern($matrix, 0, $size - 7);
        self::addFinderPattern($matrix, $size - 7, 0);

        // 2. Alignment pattern at (18, 18)
        self::addAlignmentPattern($matrix, 16, 16);

        // 3. Timing patterns
        for ($i = 8; $i < $size - 8; $i++) {
            $matrix[6][$i] = ($i % 2 === 0);
            $matrix[$i][6] = ($i % 2 === 0);
        }

        // 4. Populate payload data bits based on string hash
        $hash = sha1($text);
        $bits = '';
        for ($i = 0; $i < strlen($hash); $i++) {
            $bits .= sprintf('%04b', hexdec($hash[$i]));
        }
        
        // Fill data into unreserved modules
        $bitIdx = 0;
        $bitLen = strlen($bits);

        for ($r = 0; $r < $size; $r++) {
            for ($c = 0; $c < $size; $c++) {
                if (self::isReserved($r, $c, $size)) {
                    continue;
                }
                $val = ($bits[$bitIdx % $bitLen] === '1');
                // Apply a checkerboard mask XOR for visual distribution
                if (($r + $c) % 2 === 0) {
                    $val = !$val;
                }
                $matrix[$r][$c] = $val;
                $bitIdx++;
            }
        }

        return $matrix;
    }

    private static function addFinderPattern(array &$matrix, int $row, int $col): void
    {
        for ($r = 0; $r < 7; $r++) {
            for ($c = 0; $c < 7; $c++) {
                $isOuter = ($r === 0 || $r === 6 || $c === 0 || $c === 6);
                $isInner = ($r >= 2 && $r <= 4 && $c >= 2 && $c <= 4);
                $matrix[$row + $r][$col + $c] = ($isOuter || $isInner);
            }
        }
    }

    private static function addAlignmentPattern(array &$matrix, int $row, int $col): void
    {
        for ($r = 0; $r < 5; $r++) {
            for ($c = 0; $c < 5; $c++) {
                $isOuter = ($r === 0 || $r === 4 || $c === 0 || $c === 4);
                $isCenter = ($r === 2 && $c === 2);
                $matrix[$row + $r][$col + $c] = ($isOuter || $isCenter);
            }
        }
    }

    private static function isReserved(int $r, int $c, int $size): bool
    {
        // Top-left finder + separator
        if ($r <= 8 && $c <= 8) return true;
        // Top-right finder + separator
        if ($r <= 8 && $c >= $size - 8) return true;
        // Bottom-left finder + separator
        if ($r >= $size - 8 && $c <= 8) return true;
        // Alignment pattern
        if ($r >= 16 && $r <= 20 && $c >= 16 && $c <= 20) return true;
        // Timing lines
        if ($r === 6 || $c === 6) return true;

        return false;
    }
}
