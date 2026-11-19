<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Unit\Format;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkLiveDashboard\Format\Privacy;

/**
 * @group FeinwerkLiveDashboard
 */
class PrivacyTest extends TestCase
{
    private static function tiers(): array
    {
        return [
            'fast' => [
                'activeNow' => 14,
                'k' => 1,
                'lag' => 0,
                'revenue5' => 19.99,
                'countriesNow' => [['id' => 'de', 'label' => 'Deutschland', 'count' => 7]],
                'pagesNow' => ['urls' => [['id' => 7, 'label' => 'x', 'count' => 26]], 'titles' => []],
                'newestVisits' => [['idvisit' => 1]],
            ],
            'slow' => [
                'sparkline' => [3, 16],
                'cities' => ['30' => [['country' => 'de', 'countryLabel' => 'D', 'region' => '', 'city' => 'Berlin', 'lat' => 52.52, 'lon' => 13.4, 'count' => 13]]],
                'goals' => ['3' => [['id' => 1, 'label' => 'G', 'conversions' => 12, 'revenue' => 5.5]]],
                'pageviews' => ['3' => 44, '60' => 0],
                'revenue' => ['3' => 19.99],
                'channels' => ['3' => [['id' => 1, 'label' => 'R', 'count' => 26]]],
            ],
            'today' => ['visits' => 1234, 'since' => 1773097200, 'revenue' => 10.5],
        ];
    }

    public function test_withoutRounding_everythingStays(): void
    {
        $this->assertSame(self::tiers(), Privacy::apply(self::tiers(), true, null));
    }

    public function test_rounding_appliesToCountsOnly(): void
    {
        $round = static fn ($n): int => (int) (round($n / 10) * 10);
        $out = Privacy::apply(self::tiers(), true, $round);

        $this->assertSame(10, $out['fast']['activeNow']);
        $this->assertSame(1, $out['fast']['k']);
        $this->assertSame(19.99, $out['fast']['revenue5']);
        $this->assertSame(['id' => 'de', 'label' => 'Deutschland', 'count' => 10], $out['fast']['countriesNow'][0]);
        $this->assertSame(['id' => 7, 'label' => 'x', 'count' => 30], $out['fast']['pagesNow']['urls'][0]);
        $this->assertSame([0, 20], $out['slow']['sparkline']);
        $this->assertSame(52.52, $out['slow']['cities']['30'][0]['lat']);
        $this->assertSame(10, $out['slow']['cities']['30'][0]['count']);
        $this->assertSame(['id' => 1, 'label' => 'G', 'conversions' => 10, 'revenue' => 5.5], $out['slow']['goals']['3'][0]);
        $this->assertSame(['3' => 40, '60' => 0], $out['slow']['pageviews']);
        $this->assertSame(['3' => 19.99], $out['slow']['revenue'], 'money is no count');
        $this->assertSame(30, $out['slow']['channels']['3'][0]['count']);
        $this->assertSame(1230, $out['today']['visits']);
        $this->assertSame(1773097200, $out['today']['since']);
    }

    public function test_visitorLevelKeys_areDroppedWhenTheVisitsLogIsOff(): void
    {
        $off = Privacy::apply(self::tiers(), false, null, ['fast.newestVisits']);
        $this->assertArrayNotHasKey('newestVisits', $off['fast']);
        $this->assertSame(14, $off['fast']['activeNow']);

        $on = Privacy::apply(self::tiers(), true, null, ['fast.newestVisits']);
        $this->assertSame([['idvisit' => 1]], $on['fast']['newestVisits']);
    }

    public function test_theLiveLog_isVisitorLevel(): void
    {
        $this->assertSame(['fast.newestVisits'], Privacy::VISITOR_LEVEL);
        $off = Privacy::apply(self::tiers(), false, null);
        $this->assertArrayNotHasKey('newestVisits', $off['fast']);
        $this->assertSame([['idvisit' => 1]], Privacy::apply(self::tiers(), true, null)['fast']['newestVisits']);
    }

    public function test_dataRounding_dropsVisitorLevelKeys(): void
    {
        // single visits cannot be rounded: with data rounding on (CNIL mode) the live log goes, like Matomo's own
        $out = Privacy::apply(self::tiers(), true, static fn ($n): int => (int) $n);
        $this->assertArrayNotHasKey('newestVisits', $out['fast']);
    }
}
