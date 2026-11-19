<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Unit\Engine;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Limits;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Windows;

/**
 * @group FeinwerkLiveDashboard
 */
class WindowsTest extends TestCase
{
    private const NOW = 1795000000;

    public function test_withoutLag_windowsEndNow(): void
    {
        $w = Windows::compute(self::NOW, 0, new Limits());

        $this->assertSame(self::NOW, $w->end);
        $this->assertSame(self::NOW - 180, $w->activeFrom);
        $this->assertSame(self::NOW - 300, $w->fastFrom);
        $this->assertSame(self::NOW - 300, $w->fiveFrom);
        $this->assertSame(self::NOW - 3600, $w->slowFrom);
        $this->assertFalse($w->delayed);
        $this->assertFalse($w->stale);
    }

    public function test_lagUpTo30Seconds_isIgnored(): void
    {
        $w = Windows::compute(self::NOW, 30, new Limits());
        $this->assertSame(self::NOW, $w->end);
        $this->assertFalse($w->delayed);
    }

    public function test_lagOver30Seconds_shiftsTheWindows(): void
    {
        $w = Windows::compute(self::NOW, 31, new Limits());
        $this->assertSame(self::NOW - 31, $w->end);
        $this->assertSame(self::NOW - 31 - 3600, $w->slowFrom);
        $this->assertTrue($w->delayed);
        $this->assertFalse($w->stale);
    }

    public function test_lagOver15Minutes_isStale(): void
    {
        $w = Windows::compute(self::NOW, 901, new Limits());
        $this->assertTrue($w->delayed);
        $this->assertTrue($w->stale);
    }

    public function test_longerActiveMinutes_widenTheFastWindow(): void
    {
        $w = Windows::compute(self::NOW, 0, new Limits(activeMinutes: 10));
        $this->assertSame(self::NOW - 600, $w->activeFrom);
        $this->assertSame(self::NOW - 600, $w->fastFrom);
        $this->assertSame(self::NOW - 300, $w->fiveFrom);
    }

    public function test_negativeLag_countsAsNone(): void
    {
        // reader clock ahead of the primary: no shift
        $w = Windows::compute(self::NOW, -40, new Limits());
        $this->assertSame(self::NOW, $w->end);
        $this->assertFalse($w->delayed);
    }

    public function test_listWindows_are3To60MinutesBeforeTheEnd(): void
    {
        $w = Windows::compute(strtotime('2026-11-18 12:00:00 UTC'), 0, new Limits());
        $this->assertSame([
            '3' => '2026-11-18 11:57:00',
            '10' => '2026-11-18 11:50:00',
            '30' => '2026-11-18 11:30:00',
            '60' => '2026-11-18 11:00:00',
        ], $w->listFroms());
        $this->assertSame([3, 10, 30, 60], Windows::LIST_MINUTES);
    }

    public function test_utc_formatsForSql(): void
    {
        $this->assertSame('1970-01-01 00:00:00', Windows::utc(0));
        $this->assertSame('2026-11-18 12:00:00', Windows::utc(strtotime('2026-11-18 12:00:00 UTC')));
    }
}
