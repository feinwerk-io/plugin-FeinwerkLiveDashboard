<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Integration;

use Piwik\Plugins\FeinwerkLiveDashboard\Controller;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * The Feinwerk kit's Connector installer page is an admin page: it is reached from admin pages without a website
 * (Marketplace, Plugins), so it must render without idSite (kit fix from Campaign Score's release review, 2026-11-19).
 *
 * @group FeinwerkLiveDashboard
 */
class ConnectorInstallerPageTest extends IntegrationTestCase
{
    public function provideContainerConfig()
    {
        return ['Piwik\\Access' => new FakeAccess()];
    }

    public function setUp(): void
    {
        parent::setUp();
        Fixture::createWebsite('2026-01-01 00:00:00');
        FakeAccess::$superUser = true;
        unset($_GET['idSite'], $_POST['idSite']);
    }

    public function tearDown(): void
    {
        FakeAccess::$superUser = false;
        parent::tearDown();
    }

    public function test_thePage_rendersWithoutAWebsite(): void
    {
        $html = (new Controller())->connectorInstaller();

        $this->assertStringContainsString('class="card fwci"', $html);
        $this->assertStringContainsString('FeinwerkLiveDashboard_ConnectorInstallerIntro', $html);
        // rendered with Matomo's admin variables (the admin title), which also apply its admin rule for frames
        $this->assertStringContainsString('CoreAdminHome_Administration', $html);
    }
}
