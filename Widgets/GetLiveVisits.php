<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Widgets;

use Piwik\Widget\WidgetConfig;

/**
 * Dashboard widget with a part of the live board; every live widget of a page shares one poller.
 */
class GetLiveVisits extends GetLiveBoard
{
    protected const VARIANT = 'visits';

    public static function configure(WidgetConfig $config)
    {
        self::configureDashboardWidget($config, 'FeinwerkLiveDashboard_WidgetVisits', 4);
    }
}
