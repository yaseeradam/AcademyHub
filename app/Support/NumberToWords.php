<?php

namespace App\Support;

class NumberToWords
{
    private static array $units = [
        0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four',
        5 => 'Five', 6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
        10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen',
        14 => 'Fourteen', 15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen',
        18 => 'Eighteen', 19 => 'Nineteen'
    ];

    private static array $tens = [
        2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty',
        6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety'
    ];

    /**
     * Convert a number to Naira in words.
     * e.g., 50000 => "Fifty Thousand Naira"
     *       65500 => "Sixty-Five Thousand, Five Hundred Naira"
     */
    public static function toNairaWords(float|int|string $amount): string
    {
        // Strip any commas, currency symbols, and whitespace
        $clean = preg_replace('/[^\d.]/', '', (string) $amount);
        $number = (float) $clean;

        if ($number <= 0) {
            return 'Zero Naira';
        }

        $parts = explode('.', number_format($number, 2, '.', ''));
        $naira = (int) $parts[0];
        $kobo = (int) ($parts[1] ?? 0);

        $words = self::convertInteger($naira);
        $result = $words ? trim($words) . ' Naira' : 'Zero Naira';

        if ($kobo > 0) {
            $koboWords = self::convertInteger($kobo);
            $result .= ', ' . trim($koboWords) . ' Kobo';
        }

        return $result;
    }

    public static function convertInteger(int $number): string
    {
        if ($number === 0) {
            return '';
        }

        if ($number < 0) {
            return 'Negative ' . self::convertInteger(abs($number));
        }

        if ($number < 20) {
            return self::$units[$number];
        }

        if ($number < 100) {
            $ten = (int) ($number / 10);
            $unit = $number % 10;
            return self::$tens[$ten] . ($unit > 0 ? '-' . self::$units[$unit] : '');
        }

        if ($number < 1000) {
            $hundred = (int) ($number / 100);
            $remainder = $number % 100;
            $res = self::$units[$hundred] . ' Hundred';
            if ($remainder > 0) {
                $res .= ' and ' . self::convertInteger($remainder);
            }
            return $res;
        }

        if ($number < 1000000) {
            $thousand = (int) ($number / 1000);
            $remainder = $number % 1000;
            $res = self::convertInteger($thousand) . ' Thousand';
            if ($remainder > 0) {
                $res .= ($remainder < 100 ? ' and ' : ', ') . self::convertInteger($remainder);
            }
            return $res;
        }

        if ($number < 1000000000) {
            $million = (int) ($number / 1000000);
            $remainder = $number % 1000000;
            $res = self::convertInteger($million) . ' Million';
            if ($remainder > 0) {
                $res .= ($remainder < 100 ? ' and ' : ', ') . self::convertInteger($remainder);
            }
            return $res;
        }

        $billion = (int) ($number / 1000000000);
        $remainder = $number % 1000000000;
        $res = self::convertInteger($billion) . ' Billion';
        if ($remainder > 0) {
            $res .= ($remainder < 100 ? ' and ' : ', ') . self::convertInteger($remainder);
        }
        return $res;
    }
}
