<?php

namespace Uptacknerd\FxBtScripts;

use RuntimeException;

class Timeframe {
    public static function convertTimeframe(string $timeframe): int {
        if ($timeframe == 'tick') {
            return 0; // 0 means tick
        }

        $matches = null;
        preg_match('/^(M|H|D|W|MN)(\d+)$/', strtoupper($timeframe), $matches);
        if (!is_array($matches) || count($matches) != 3)  {
            throw new RuntimeException("Invalid timeframe $timeframe");
        }
        $scale = 60; // M1 = 60 seconds
        switch ($matches[1]) {
            case 'MN':
                $scale = 30 * 24 * 60 * 60; // 30 days * 24 hours * 60 minutes * 60 seconds
                break;

            case 'W':
                $scale *= 7; // 7 days
            case 'D':
                $scale *= 24; // 24 hours
            case 'H':
                $scale *= 60; // 60 minutes
            case 'M':
                $scale *= 1; // 1 minute
        }
        $scale *= $matches[2]; // multiplier (for M15, M30, H4, ...)

        return $scale;
    }

    public static function convertNumericTimeframe($timeframe): string {
        if ($timeframe == 0) {
            return 'tick';
        }

        if (($timeframe % 60) != 0) {
            throw new RuntimeException("unknown timeframe value $timeframe (not a multiple of 60)");
        }

        $timeframe = $timeframe / 60;
        switch ($timeframe) {
            case 1:
                return 'M1';
            case 5:
                return 'M5';
            case 15:
                return 'M15';
            case 60:
                return 'H1';
            case 240:
                return 'H4';
            case 1440:
                return 'D1';
            case 43200:
                return 'MN';
        }

        $timeframe = (int) ($timeframe);
        throw new RuntimeException("unknown timeframe value $timeframe");
    }
}