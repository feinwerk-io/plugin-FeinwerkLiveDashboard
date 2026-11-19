<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Snapshot;

use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Tier;

/**
 * The stored state of one tier for one website and live view: when it was last computed (or attempted), up to when its
 * data reaches, and the language-neutral payload.
 */
final class Snapshot
{
    public function __construct(
        public readonly int $idSite,
        public readonly int $viewId,
        public readonly Tier $tier,
        public readonly int $computedAt,
        public readonly int $dataUntil,
        public readonly array $payload
    ) {
    }
}
