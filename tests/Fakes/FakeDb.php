<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Fakes;

/**
 * Stands in for a Matomo db adapter where only isErrNo() matters.
 */
final class FakeDb
{
    /** @param int[] $errorCodes the codes isErrNo() recognises */
    public function __construct(private readonly array $errorCodes = [])
    {
    }

    public function isErrNo($e, $errno): bool
    {
        return in_array((int) $errno, $this->errorCodes, true);
    }
}
