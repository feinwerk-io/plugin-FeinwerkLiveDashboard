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
class GetLivePages extends GetLiveBoard
{
    protected const VARIANT = 'pages';

    public static function configure(WidgetConfig $config)
    {
        self::configureDashboardWidget($config, 'FeinwerkLiveDashboard_WidgetPages', 2);
    }
}
