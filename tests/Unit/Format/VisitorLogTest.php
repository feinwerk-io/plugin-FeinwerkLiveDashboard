<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Unit\Format;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkLiveDashboard\Format\VisitorLog;

/**
 * The latest visits follow Matomo's visits log: off when the Live plugin is deactivated (Matomo's autoloader still loads
 * its class then, so the class alone proves nothing) or when the website or the instance switched the log off.
 *
 * @group FeinwerkLiveDashboard
 */
class VisitorLogTest extends TestCase
{
    public function test_onlyWithLiveActivated_andItsSettingOn(): void
    {
        $this->assertTrue(VisitorLog::enabled(true, static fn (): bool => true));
        $this->assertFalse(VisitorLog::enabled(true, static fn (): bool => false), 'the visits log is off for the site');
        $this->assertFalse(VisitorLog::enabled(false, static fn (): bool => true), 'Live is deactivated');
    }

    public function test_aDeactivatedLive_isNotEvenAsked(): void
    {
        $asked = false;
        VisitorLog::enabled(false, static function () use (&$asked): bool {
            $asked = true;
            return true;
        });
        $this->assertFalse($asked);
    }
}
