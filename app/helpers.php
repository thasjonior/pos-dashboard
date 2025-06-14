<?php

namespace App\Helpers;


if (!function_exists('format_short_number')) {
    /**
     * Format a number to shortened string with K/M suffixes
     *
     * @param int|float $number
     * @param int $decimals
     * @return string
     */
    function format_short_number($number, int $decimals = 1): string
    {
        if ($number < 1000) {
            return (string) $number;
        }

        if ($number < 1000000) {
            $formatted = $number / 1000;
            $result = number_format($formatted, $decimals);
            return rtrim(rtrim($result, '0'), '.') . 'K';
        }

        if ($number < 1000000000) {
            $formatted = $number / 1000000;
            $result = number_format($formatted, $decimals);
            return rtrim(rtrim($result, '0'), '.') . 'M';
        }

        // For billions and above
        $formatted = $number / 1000000000;
        $result = number_format($formatted, $decimals);
        return rtrim(rtrim($result, '0'), '.') . 'B';
    }
}

