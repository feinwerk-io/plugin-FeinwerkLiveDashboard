<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Unit\Engine;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\LocalDay;

/**
 * @group FeinwerkLiveDashboard
 */
class LocalDayTest extends TestCase
{
    /**
     * @dataProvider days
     */
    public function test_startUtc(string $now, string $timezone, string $expected): void
    {
        $this->assertSame($expected, gmdate('Y-m-d H:i:s', LocalDay::startUtc(strtotime($now . ' UTC'), $timezone)));
    }

    public static function days(): array
    {
        return [
            'UTC' => ['2026-11-18 03:00:00', 'UTC', '2026-11-18 00:00:00'],
            'Matomo offset with half hours' => ['2026-11-18 03:00:00', 'UTC+5.5', '2026-11-17 18:30:00'],
            'Matomo offset with quarter hours' => ['2026-11-18 03:00:00', 'UTC+5.75', '2026-11-17 18:15:00'],
            'Matomo negative offset, local day before' => ['2026-11-18 02:00:00', 'UTC-3', '2026-11-17 03:00:00'],
            'Matomo negative offset with half hours' => ['2026-11-18 12:00:00', 'UTC-9.5', '2026-11-18 09:30:00'],
            'IANA zone' => ['2026-11-18 12:00:00', 'Europe/Berlin', '2026-11-17 23:00:00'],
            'Berlin, day summer time starts' => ['2026-03-29 12:00:00', 'Europe/Berlin', '2026-03-28 23:00:00'],
            'Berlin, day summer time ends' => ['2026-10-25 12:00:00', 'Europe/Berlin', '2026-10-24 22:00:00'],
            'unknown zone falls back to UTC' => ['2026-11-18 03:00:00', 'Not/AZone', '2026-11-18 00:00:00'],
            'empty zone falls back to UTC' => ['2026-11-18 03:00:00', '', '2026-11-18 00:00:00'],
        ];
    }
}
