<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Format;

/**
 * Labels in the viewer's language. Snapshots store raw keys (country codes, referrer type ids, device type ids, goal
 * ids), so every language shares one snapshot (concept 3.3).
 */
interface Labeler
{
    public function country(string $code): string;

    public function referrerType(int $type): string;

    public function deviceType(int $type): string;

    public function goal(int $idGoal): string;

    /** For empty names (a campaign without name, an action whose name is gone). */
    public function unknown(): string;

    /** For page lists: a visit whose last action was no page view (a site search, a download), page id 0. */
    public function noPage(): string;
}
