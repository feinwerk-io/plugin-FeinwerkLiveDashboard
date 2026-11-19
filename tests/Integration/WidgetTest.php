<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Integration;

use Piwik\Category\CategoryList;
use Piwik\NoAccessException;
use Piwik\Piwik;
use Piwik\Plugins\FeinwerkLiveDashboard\Widgets\GetLiveBoard;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Widget\WidgetConfig;
use Piwik\Widget\WidgetsList;

/**
 * Visitors › Live Dashboard: one wide widget that mounts the Vue board.
 *
 * @group FeinwerkLiveDashboard
 */
class WidgetTest extends IntegrationTestCase
{
    public function provideContainerConfig()
    {
        return ['Piwik\\Access' => new FakeAccess()];
    }

    public function setUp(): void
    {
        parent::setUp();
        Fixture::loadAllTranslations();
        FakeAccess::$superUser = true;
        Fixture::createWebsite('2026-01-01 00:00:00', 1, 'Shop & <Co>', 'https://example.com', 1, null, null, 'Europe/Berlin');
        \Piwik\Plugins\SitesManager\API::getInstance()->updateSite(1, null, null, null, null, null, null, null, null, null, 'EUR');
        $_GET['idSite'] = '1';
    }

    public function tearDown(): void
    {
        unset($_GET['idSite']);
        Fixture::resetTranslations();
        parent::tearDown();
    }

    private function widget(): ?WidgetConfig
    {
        foreach (WidgetsList::get()->getWidgetConfigs() as $config) {
            if ($config->getModule() === 'FeinwerkLiveDashboard' && $config->getAction() === 'getLiveBoard') {
                return $config;
            }
        }
        return null;
    }

    public function test_viewer_seesTheWideWidgetUnderVisitors(): void
    {
        FakeAccess::clearAccess(false, [], [1], 'viewer');

        $widget = $this->widget();

        $this->assertNotNull($widget);
        $this->assertSame('General_Visitors', $widget->getCategoryId());
        $this->assertSame('FeinwerkLiveDashboard_LiveDashboard', $widget->getSubcategoryId());
        $this->assertTrue($widget->isWide());
        $this->assertTrue($widget->isEnabled());
        $subcategory = CategoryList::get()->getCategory('General_Visitors')->getSubcategory('FeinwerkLiveDashboard_LiveDashboard');
        $this->assertNotNull($subcategory);
        $this->assertSame('Live Dashboard', Piwik::translate('FeinwerkLiveDashboard_LiveDashboard'));
    }

    public function test_render_mountsTheBoardWithItsProps(): void
    {
        FakeAccess::clearAccess(false, [], [1], 'viewer');

        $html = (new GetLiveBoard())->render();

        $this->assertStringContainsString('vue-entry="FeinwerkLiveDashboard.LiveBoard"', $html);
        $this->assertSame(1, preg_match('/id-site="1"/', $html));
        $this->assertStringContainsString('currency-symbol="€"', html_entity_decode($html, ENT_QUOTES | ENT_HTML5));
        $this->assertSame(1, preg_match('/active-minutes="3"/', $html));
        $this->assertStringNotContainsString('<Co>', $html, 'the site name is escaped');
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function variants(): array
    {
        return [
            'overview' => ['getLiveOverview', 'overview'],
            'pages' => ['getLivePages', 'pages'],
            'sources' => ['getLiveSources', 'sources'],
            'visits' => ['getLiveVisits', 'visits'],
            'map' => ['getLiveMap', 'map'],
        ];
    }

    /**
     * @dataProvider variants
     */
    public function test_dashboardWidgets_areListedButNotOnThePage(string $action, string $variant): void
    {
        FakeAccess::clearAccess(false, [], [1], 'viewer');
        $widget = null;
        foreach (WidgetsList::get()->getWidgetConfigs() as $config) {
            if ($config->getModule() === 'FeinwerkLiveDashboard' && $config->getAction() === $action) {
                $widget = $config;
            }
        }

        $this->assertNotNull($widget, $action);
        $this->assertSame('FeinwerkLiveDashboard_Widgets', $widget->getCategoryId());
        $this->assertSame('', (string) $widget->getSubcategoryId(), 'no subcategory: not on a report page');
        $this->assertTrue($widget->isEnabled());

        $class = 'Piwik\\Plugins\\FeinwerkLiveDashboard\\Widgets\\' . ucfirst($action);
        $html = (new $class())->render();
        $this->assertStringContainsString('vue-entry="FeinwerkLiveDashboard.LiveBoard"', $html);
        $this->assertStringContainsString('variant="' . $variant . '"', $html);
    }

    public function test_siteName_isPlainText(): void
    {
        // Matomo stores names typed in its interface HTML-escaped; the board shows them as text
        Fixture::createWebsite('2026-01-01 00:00:00', 1, 'Tom &amp; Jerry', 'https://example.org');
        FakeAccess::clearAccess(false, [], [1, 2], 'viewer');
        $_GET['idSite'] = '2';
        try {
            $html = html_entity_decode((new GetLiveBoard())->render());
        } finally {
            $_GET['idSite'] = '1';
        }
        $this->assertStringContainsString('site-name="Tom & Jerry"', $html);
    }

    public function test_thePage_rendersTheFullBoard(): void
    {
        FakeAccess::clearAccess(false, [], [1], 'viewer');
        $html = (new GetLiveBoard())->render();
        $this->assertStringContainsString('variant="full"', $html);
        // attribute values are escaped for HTML (also "/"); the browser reads them decoded
        $this->assertStringContainsString('world-map="plugins/UserCountryMap/svg/world.svg"', html_entity_decode($html), 'Matomo\'s own world map');
    }

    public function test_userWithoutAccess_getsNoWidgetAndNoRender(): void
    {
        FakeAccess::clearAccess(false, [], [], 'anonymous');

        // the whole widget list cannot be built without access (other plugins' widgets check access too)
        $config = new WidgetConfig();
        GetLiveBoard::configure($config);
        $this->assertFalse($config->isEnabled());

        $this->expectException(NoAccessException::class);
        (new GetLiveBoard())->render();
    }
}
