<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Fixtures;

use Piwik\Plugins\Goals\API as GoalsApi;
use Piwik\Plugins\PrivacyManager\IPAnonymizer;
use Piwik\Tests\Framework\Fixture;

/**
 * Live traffic around a fixed "now" of 2026-03-10 12:00:00 UTC (no test depends on today's date). Site 1 is a shop in
 * Europe/Berlin (local midnight 2026-03-09 23:00 UTC) with goal 1; site 2 has no visits.
 *
 * | visit | last hit (UTC)  | hits                                                        | channel        | country | device     |
 * |-------|-----------------|-------------------------------------------------------------|----------------|---------|------------|
 * | V1    | 11:59:00        | pageview /home 11:58, /product 11:59                         | campaign       | de, Berlin with coordinates | desktop |
 * | V2    | 11:58:00        | download file.pdf 11:57:30, pageview /home 11:58            | search         | at      | smartphone |
 * | V3    | 11:56:50        | pageview /home 11:56, content impression 11:56:05, event "Video & <Audio>"/Play 11:56:20, outlink partner.example 11:56:30, goal 1 (5) 11:56:40, site search "shoes" 11:56:50 | direct | de | desktop |
 * | V4    | 11:50:30        | pageview /product 11:50, order 49.90 11:50:30               | direct         | fr      | desktop    |
 * | V5    | 11:20:00        | pageview /home 11:20 (outside 30 minutes)                    | direct         | de      | desktop    |
 * | V6    | 03-09 23:30:00  | pageview /home (today in Berlin)                             | direct         | de      | desktop    |
 * | V7    | 03-09 22:30:00  | pageview /home (yesterday in Berlin)                         | direct         | de      | desktop    |
 */
class LiveTrafficFixture extends Fixture
{
    public const NOW = '2026-03-10 12:00:00';
    public const SITE = 1;
    public const EMPTY_SITE = 2;

    private const DESKTOP = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
    private const PHONE = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';
    private const BASE = 'http://shop.example';

    public $dateTime = '2026-03-09 20:00:00';

    public function setUp(): void
    {
        self::createSuperUser(false);
        IPAnonymizer::deactivate();

        if (!self::siteCreated(self::SITE)) {
            self::createWebsite('2026-01-01 00:00:00', 1, 'Berlin shop', self::BASE, 1, null, null, 'Europe/Berlin');
        }
        if (!self::siteCreated(self::EMPTY_SITE)) {
            self::createWebsite('2026-01-01 00:00:00', 1, 'Quiet site', 'http://quiet.example', 1, null, null, 'UTC');
        }
        if (!self::goalExists(self::SITE, 1)) {
            GoalsApi::getInstance()->addGoal(self::SITE, 'Newsletter', 'manually', '', 'contains');
        }

        // V1: campaign (Matomo strips the parameter from the page URL and lowercases the name), Berlin with coordinates
        $t = $this->visit('2026-03-10 11:58:00', '198.51.100.1', 'de', self::DESKTOP);
        $t->setRegion('BE');
        $t->setCity('Berlin');
        $t->setLatitude(52.52);
        $t->setLongitude(13.40);
        $this->pageview($t, '2026-03-10 11:58:00', '/home?mtm_campaign=Black-Friday', 'Home');
        $this->pageview($t, '2026-03-10 11:59:00', '/product', 'Product');

        // V2: search engine, phone, a download before the pageview
        $t = $this->visit('2026-03-10 11:57:30', '198.51.100.2', 'at', self::PHONE);
        $t->setUrlReferrer('https://www.google.com/search?q=shoes');
        $t->setUrl(self::BASE . '/home');
        self::checkResponse($t->doTrackAction(self::BASE . '/file.pdf', 'download'));
        $this->pageview($t, '2026-03-10 11:58:00', '/home', 'Home');

        // V3: direct; a content impression (no pageview), an event, an outlink, a goal conversion, a site search
        $t = $this->visit('2026-03-10 11:56:00', '198.51.100.3', 'de', self::DESKTOP);
        $this->pageview($t, '2026-03-10 11:56:00', '/home', 'Home');
        $t->setForceVisitDateTime('2026-03-10 11:56:05');
        self::checkResponse($t->doTrackContentImpression('Banner', 'Sale', self::BASE . '/sale'));
        $t->setForceVisitDateTime('2026-03-10 11:56:20');
        self::checkResponse($t->doTrackEvent('Video & <Audio>', 'Play', 'Intro'));
        $t->setForceVisitDateTime('2026-03-10 11:56:30');
        self::checkResponse($t->doTrackAction('https://partner.example/offer', 'link'));
        $t->setForceVisitDateTime('2026-03-10 11:56:40');
        self::checkResponse($t->doTrackGoal(1, 5));
        $t->setForceVisitDateTime('2026-03-10 11:56:50');
        self::checkResponse($t->doTrackSiteSearch('shoes', 'Products', 3));

        // V4: direct, ecommerce order
        $t = $this->visit('2026-03-10 11:50:00', '198.51.100.4', 'fr', self::DESKTOP);
        $this->pageview($t, '2026-03-10 11:50:00', '/product', 'Product');
        $t->setForceVisitDateTime('2026-03-10 11:50:30');
        $t->addEcommerceItem('SKU1', 'Shoe', 'Shoes', 49.90, 1);
        self::checkResponse($t->doTrackEcommerceOrder('order-1', 49.90));

        // V5 to V7: outside the live windows
        foreach (['2026-03-10 11:20:00', '2026-03-09 23:30:00', '2026-03-09 22:30:00'] as $i => $time) {
            $t = $this->visit($time, '198.51.100.' . (5 + $i), 'de', self::DESKTOP);
            $this->pageview($t, $time, '/home', 'Home');
        }
    }

    private function visit(string $time, string $ip, string $country, string $userAgent)
    {
        $t = self::getTracker(self::SITE, $time, true, true);
        $t->setNewVisitorId();
        $t->setForceNewVisit();
        $t->setIp($ip);
        $t->setCountry($country);
        $t->setUserAgent($userAgent);
        return $t;
    }

    private function pageview($t, string $time, string $path, string $title): void
    {
        $t->setForceVisitDateTime($time);
        $t->setUrl(self::BASE . $path);
        self::checkResponse($t->doTrackPageView($title));
    }
}
