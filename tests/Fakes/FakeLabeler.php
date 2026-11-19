<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Fakes;

use Piwik\Plugins\FeinwerkLiveDashboard\Format\Labeler;

final class FakeLabeler implements Labeler
{
    public function country(string $code): string
    {
        return 'C:' . $code;
    }

    public function referrerType(int $type): string
    {
        return 'R:' . $type;
    }

    public function deviceType(int $type): string
    {
        return 'D:' . $type;
    }

    public function goal(int $idGoal): string
    {
        return 'G:' . $idGoal;
    }

    public function unknown(): string
    {
        return '?';
    }

    public function noPage(): string
    {
        return 'no page';
    }
}
