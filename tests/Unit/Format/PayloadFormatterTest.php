<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Unit\Format;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkLiveDashboard\Format\PayloadFormatter;
use Piwik\Plugins\FeinwerkLiveDashboard\tests\Fakes\FakeLabeler;

/**
 * @group FeinwerkLiveDashboard
 */
class PayloadFormatterTest extends TestCase
{
    private static function format(array $tiers): array
    {
        return PayloadFormatter::format($tiers, new FakeLabeler());
    }

    public function test_today_keepsItsCountingStateInside(): void
    {
        $out = self::format(['today' => ['visits' => 6, 'actions' => 12, 'counted' => ['actions' => ['until' => 1]]]]);

        $this->assertSame(['visits' => 6, 'actions' => 12], $out['today']);
    }

    public function test_fast_labelsChannelsCountriesAndPages(): void
    {
        $out = self::format(['fast' => [
            'activeNow' => 2,
            'channelsNow' => [[2, 1], [6, 1]],
            'countriesNow' => [['de', 3]],
            'pagesNow' => ['urls' => [[5, 'shop.example/home', 2], [0, '', 1]], 'titles' => [[7, 'Home', 2]]],
        ], 'slow' => [], 'today' => []]);

        $this->assertSame(2, $out['fast']['activeNow']);
        $this->assertSame([['id' => 2, 'label' => 'R:2', 'count' => 1], ['id' => 6, 'label' => 'R:6', 'count' => 1]], $out['fast']['channelsNow']);
        $this->assertSame([['id' => 'de', 'label' => 'C:de', 'count' => 3]], $out['fast']['countriesNow']);
        $this->assertSame(
            [['id' => 5, 'label' => 'shop.example/home', 'count' => 2], ['id' => 0, 'label' => 'no page', 'count' => 1]],
            $out['fast']['pagesNow']['urls']
        );
        $this->assertSame([['id' => 7, 'label' => 'Home', 'count' => 2]], $out['fast']['pagesNow']['titles']);
    }

    public function test_pageListsWithoutAPage_sayNoPageView(): void
    {
        // a visit whose last action was a site search or a download has no exit page (id 0)
        $out = self::format(['fast' => ['pagesNow' => ['urls' => [], 'titles' => [[0, '', 4]]]], 'slow' => [
            'topPages' => ['3' => [[0, '', 1]]],
            'downloads' => ['3' => [[0, '', 2]]],
        ], 'today' => []]);

        $this->assertSame('no page', $out['fast']['pagesNow']['titles'][0]['label']);
        $this->assertSame('no page', $out['slow']['topPages']['3'][0]['label']);
        $this->assertSame('?', $out['slow']['downloads']['3'][0]['label'], 'other lists keep "unknown"');
    }

    public function test_slow_labelsEveryListOfEveryWindow(): void
    {
        $out = self::format(['fast' => [], 'today' => [], 'slow' => [
            'visits' => ['3' => 2, '60' => 9],
            'channels' => ['3' => [[1, 2]], '60' => [[1, 5], [2, 4]]],
            'campaigns' => ['3' => [['black-friday', 1], ['', 1]], '60' => []],
            'campaignSources' => ['3' => [['news &amp; mail', 2]]],
            'countries' => ['3' => [['xx', 1]]],
            'devices' => ['3' => [[1, 4], [-1, 1]]],
            'cities' => ['3' => [], '10' => [['de', 'BE', 'Berlin', 52.52, 13.4, 3]]],
            'regions' => ['3' => [['de', 'BY', 4]]],
            'goals' => ['3' => [], '10' => [[1, 2, 5.5]]],
            'topPages' => ['3' => [[5, 'a', 1]], '30' => [[5, 'a', 2], [0, '', 1]]],
            'topTitles' => ['3' => [], '30' => [[7, 'Home', 2]]],
            'downloads' => ['3' => [[9, 'http://x/f.pdf', 1]]],
            'outlinks' => ['3' => []],
            'siteSearch' => ['3' => [[11, 'shoes', 2]]],
            'eventCategories' => ['3' => [[12, 'Video', 1]]],
            'eventActions' => ['3' => [[13, 'Play', 1]]],
            'sparkline' => [0, 1],
        ]]);
        $slow = $out['slow'];

        $this->assertSame(['3' => 2, '60' => 9], $slow['visits']);
        $this->assertSame([['id' => 1, 'label' => 'R:1', 'count' => 2]], $slow['channels']['3']);
        $this->assertSame([['id' => 1, 'label' => 'R:1', 'count' => 5], ['id' => 2, 'label' => 'R:2', 'count' => 4]], $slow['channels']['60']);
        $this->assertSame([['id' => 'black-friday', 'label' => 'black-friday', 'count' => 1], ['id' => '', 'label' => '?', 'count' => 1]], $slow['campaigns']['3']);
        $this->assertSame([], $slow['campaigns']['60']);
        $this->assertSame([['id' => 'news &amp; mail', 'label' => 'news & mail', 'count' => 2]], $slow['campaignSources']['3']);
        $this->assertSame([['id' => 'xx', 'label' => 'C:xx', 'count' => 1]], $slow['countries']['3']);
        $this->assertSame([['id' => 1, 'label' => 'D:1', 'count' => 4], ['id' => -1, 'label' => 'D:-1', 'count' => 1]], $slow['devices']['3']);
        $this->assertSame(
            ['3' => [], '10' => [['country' => 'de', 'countryLabel' => 'C:de', 'region' => 'BE', 'city' => 'Berlin', 'lat' => 52.52, 'lon' => 13.4, 'count' => 3]]],
            $slow['cities']
        );
        $this->assertSame(['3' => [], '10' => [['id' => 1, 'label' => 'G:1', 'conversions' => 2, 'revenue' => 5.5]]], $slow['goals']);
        $this->assertSame(['3' => [['country' => 'de', 'region' => 'BY', 'count' => 4]]], $slow['regions']);
        $this->assertSame([['id' => 5, 'label' => 'a', 'count' => 2], ['id' => 0, 'label' => 'no page', 'count' => 1]], $slow['topPages']['30']);
        $this->assertSame([], $slow['topTitles']['3']);
        $this->assertSame([['id' => 9, 'label' => 'http://x/f.pdf', 'count' => 1]], $slow['downloads']['3']);
        $this->assertSame([['id' => 11, 'label' => 'shoes', 'count' => 2]], $slow['siteSearch']['3']);
        $this->assertSame([['id' => 12, 'label' => 'Video', 'count' => 1]], $slow['eventCategories']['3']);
        $this->assertSame([['id' => 13, 'label' => 'Play', 'count' => 1]], $slow['eventActions']['3']);
        $this->assertSame([0, 1], $slow['sparkline']);
    }

    public function test_storedNames_becomePlainText(): void
    {
        // Matomo's tracker stores names HTML-escaped; the API answers plain text like the translated labels, and the UI
        // renders every label as text
        $out = self::format(['fast' => [
            'pagesNow' => ['urls' => [[5, 'shop.example/?a=1&amp;b=2', 1]], 'titles' => [[7, 'Tom &amp; Jerry &quot;live&quot;', 1]]],
        ], 'slow' => [
            'campaigns' => ['3' => [['sale &amp; more', 2]]],
            'eventCategories' => ['3' => [[12, 'Video &amp; &lt;Audio&gt;', 1]]],
            'cities' => ['3' => [['de', 'BE', 'K&amp;ln', 1.0, 2.0, 3]]],
        ], 'today' => []]);

        $this->assertSame('shop.example/?a=1&b=2', $out['fast']['pagesNow']['urls'][0]['label']);
        $this->assertSame('Tom & Jerry "live"', $out['fast']['pagesNow']['titles'][0]['label']);
        $this->assertSame('sale & more', $out['slow']['campaigns']['3'][0]['label']);
        $this->assertSame('Video & <Audio>', $out['slow']['eventCategories']['3'][0]['label']);
        $this->assertSame('K&ln', $out['slow']['cities']['3'][0]['city']);
    }

    public function test_liveLog_rowsGetLabelsAndPlainText(): void
    {
        $out = self::format(['fast' => ['newestVisits' => [
            [1773144000, 'de', 6, 'black-friday &amp; more', 1, 5, 'shop.example/p/1', 'Product &quot;A&quot;', 4, ['goal', 'order'], 260],
            [1773143990, 'xx', 1, '', 0, 0, '', '', 1, ['returning'], 0],
        ]], 'slow' => [], 'today' => []]);

        $this->assertSame([
            [
                'time' => 1773144000, 'country' => 'de', 'countryLabel' => 'C:de', 'channel' => 'R:6',
                'source' => 'black-friday & more', 'device' => 'D:1', 'page' => 'shop.example/p/1', 'title' => 'Product "A"',
                'actions' => 4, 'icons' => ['goal', 'order'], 'duration' => 260,
            ],
            [
                'time' => 1773143990, 'country' => 'xx', 'countryLabel' => 'C:xx', 'channel' => 'R:1',
                'source' => '', 'device' => 'D:0', 'page' => 'no page', 'title' => 'no page',
                'actions' => 1, 'icons' => ['returning'], 'duration' => 0,
            ],
        ], $out['fast']['newestVisits']);
    }

    public function test_missingKeys_stayMissing(): void
    {
        $out = self::format(['fast' => ['activeNow' => 1, 'partial' => true], 'slow' => [], 'today' => ['visits' => 3]]);
        $this->assertSame(['fast' => ['activeNow' => 1, 'partial' => true], 'slow' => [], 'today' => ['visits' => 3]], $out);
    }
}
