<?php

namespace App\Support;

final class MoneyWords
{
    /**
     * Convert a currency amount into words for receipts, without requiring the intl extension.
     *
     * Example: "40000.50" => "Forty Thousand Naira And Fifty Kobo Only"
     */
    public static function forReceipt(mixed $amount, string $currencyName = 'Naira', string $subunitName = 'Kobo'): string
    {
        $currencyName = trim($currencyName) !== '' ? trim($currencyName) : 'Naira';
        $subunitName = trim($subunitName) !== '' ? trim($subunitName) : 'Kobo';

        [$negative, $naira, $kobo] = self::splitAmount($amount);

        $words = [];
        if ($negative) {
            $words[] = 'minus';
        }

        $words[] = self::spellInt($naira);
        $words[] = $currencyName;

        if ($kobo > 0) {
            $words[] = 'and';
            $words[] = self::spellInt($kobo);
            $words[] = $subunitName;
        }

        $words[] = 'only';

        return ucwords(trim(implode(' ', array_filter($words, fn ($w) => trim((string) $w) !== ''))));
    }

    /**
     * @return array{0:bool,1:int,2:int} [negative, whole, fraction(0-99)]
     */
    private static function splitAmount(mixed $amount): array
    {
        $raw = trim((string) $amount);
        if ($raw === '') {
            return [false, 0, 0];
        }

        $negative = false;
        if (str_starts_with($raw, '-')) {
            $negative = true;
            $raw = ltrim(substr($raw, 1));
        }

        // Keep digits and decimal point only.
        $raw = preg_replace('/[^0-9.]/', '', $raw) ?? '';
        if ($raw === '' || ! preg_match('/^\d+(\.\d+)?$/', $raw)) {
            return [$negative, 0, 0];
        }

        [$wholeStr, $fractionStr] = array_pad(explode('.', $raw, 2), 2, '0');
        $whole = (int) $wholeStr;

        $fractionStr = substr(str_pad($fractionStr, 2, '0'), 0, 2);
        $fraction = (int) $fractionStr;

        return [$negative, $whole, $fraction];
    }

    private static function spellInt(int $number): string
    {
        if ($number === 0) {
            return 'zero';
        }

        if ($number < 0) {
            return 'minus '.self::spellInt(abs($number));
        }

        $scales = [
            1_000_000_000_000 => 'trillion',
            1_000_000_000 => 'billion',
            1_000_000 => 'million',
            1_000 => 'thousand',
        ];

        $parts = [];
        foreach ($scales as $value => $label) {
            if ($number >= $value) {
                $count = intdiv($number, $value);
                $number = $number % $value;
                $parts[] = self::spellBelowThousand($count).' '.$label;
            }
        }

        if ($number > 0) {
            $parts[] = self::spellBelowThousand($number);
        }

        return trim(implode(' ', $parts));
    }

    private static function spellBelowThousand(int $number): string
    {
        $number = $number % 1000;

        $ones = [
            0 => '',
            1 => 'one',
            2 => 'two',
            3 => 'three',
            4 => 'four',
            5 => 'five',
            6 => 'six',
            7 => 'seven',
            8 => 'eight',
            9 => 'nine',
            10 => 'ten',
            11 => 'eleven',
            12 => 'twelve',
            13 => 'thirteen',
            14 => 'fourteen',
            15 => 'fifteen',
            16 => 'sixteen',
            17 => 'seventeen',
            18 => 'eighteen',
            19 => 'nineteen',
        ];

        $tens = [
            2 => 'twenty',
            3 => 'thirty',
            4 => 'forty',
            5 => 'fifty',
            6 => 'sixty',
            7 => 'seventy',
            8 => 'eighty',
            9 => 'ninety',
        ];

        $words = [];

        if ($number >= 100) {
            $words[] = $ones[intdiv($number, 100)].' hundred';
            $number = $number % 100;
        }

        if ($number >= 20) {
            $words[] = $tens[intdiv($number, 10)] ?? '';
            $number = $number % 10;
            if ($number > 0) {
                $words[] = $ones[$number] ?? '';
            }
        } elseif ($number > 0) {
            $words[] = $ones[$number] ?? '';
        }

        return trim(implode(' ', array_filter($words, fn ($w) => trim((string) $w) !== '')));
    }
}

