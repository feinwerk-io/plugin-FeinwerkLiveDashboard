<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Format;

/**
 * Whether visitor-level panels (the latest visits) may show: Matomo's own rule for its real-time widgets, the Live
 * plugin activated and its visits log on for the website. Matomo's autoloader loads the Live class also while the
 * plugin is deactivated, so its class alone proves nothing.
 */
final class VisitorLog
{
    /**
     * @param callable $logOn fn(): bool, Live's setting for the website; asked only while Live is activated
     */
    public static function enabled(bool $liveActivated, callable $logOn): bool
    {
        return $liveActivated && (bool) $logOn();
    }
}
