<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Data;

/**
 * A statement hit the time limit. The snapshot keeps its previous values and the website's degrade level rises.
 */
final class QueryTimeout extends \RuntimeException
{
}
