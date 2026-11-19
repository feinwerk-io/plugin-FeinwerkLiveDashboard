<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Snapshot;

/**
 * The outcome of one tier computation. A partial result (budget ended before every read) holds only the keys it could
 * compute; the others keep their previous values.
 */
final class TierResult
{
    public function __construct(
        public readonly array $payload,
        public readonly int $dataUntil,
        public readonly bool $capHit,
        public readonly bool $partial = false
    ) {
    }
}
