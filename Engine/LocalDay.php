<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Engine;

/**
 * Start of "today" in a website's time zone, as a Unix timestamp. Matomo allows manual offsets such as "UTC+5.5" that
 * PHP's DateTimeZone does not know; those are applied as fixed offsets. An unknown zone counts as UTC.
 */
final class LocalDay
{
    public static function startUtc(int $now, string $timezone): int
    {
        if (preg_match('/^UTC([+-])(\d{1,2}(?:\.\d+)?)$/', $timezone, $m)) {
            $offset = (int) round(((float) $m[2]) * 3600) * ($m[1] === '-' ? -1 : 1);
            $local = $now + $offset;
            return $local - self::mod($local, 86400) - $offset;
        }

        try {
            $zone = new \DateTimeZone($timezone === '' ? 'UTC' : $timezone);
        } catch (\Exception $e) {
            $zone = new \DateTimeZone('UTC');
        }
        $midnight = (new \DateTimeImmutable('@' . $now))->setTimezone($zone)->setTime(0, 0);
        return $midnight->getTimestamp();
    }

    private static function mod(int $a, int $b): int
    {
        return (($a % $b) + $b) % $b;
    }
}
