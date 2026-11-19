<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Categories;

use Piwik\Category\Category;

/**
 * The dashboard widgets of the live board (Live: Overview, Pages, Sources and goals, Latest visits). They have no
 * subcategory, so they appear in the dashboard's widget menu only, not on a report page.
 */
class LiveWidgetsCategory extends Category
{
    protected $id = 'FeinwerkLiveDashboard_Widgets';
    protected $order = 7;
}
