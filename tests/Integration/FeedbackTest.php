<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Integration;

use Piwik\NoAccessException;
use Piwik\Option;
use Piwik\Plugins\FeinwerkLiveDashboard\API;
use Piwik\Plugins\FeinwerkLiveDashboard\Feedback\PluginFeedback;
use Piwik\Plugins\FeinwerkLiveDashboard\FeinwerkLiveDashboard;
use Piwik\Plugins\FeinwerkLiveDashboard\Widgets\GetLiveBoard;
use Piwik\Plugins\FeinwerkLiveDashboard\Widgets\GetLiveOverview;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * The feedback thumbs under the board: the API's access rules, nothing sent in test mode, the user's throttles removed
 * with the user, and the question only on the board's own page.
 *
 * @group FeinwerkLiveDashboard
 */
class FeedbackTest extends IntegrationTestCase
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
        Fixture::createWebsite('2026-01-01 00:00:00', 1, 'Shop', 'https://example.com');
        FakeAccess::clearAccess(false, [], [1], 'viewer');
        $_GET['idSite'] = 1;
    }

    public function tearDown(): void
    {
        unset($_GET['idSite'], $_GET['module']);
        Fixture::resetTranslations();
        parent::tearDown();
    }

    public function test_aViewer_canVote_butNothingLeavesInTestMode(): void
    {
        $this->assertSame(['sent' => false], API::getInstance()->sendFeedback(1, 'board', 'up'));
    }

    public function test_withoutAccessToTheSite_noVote(): void
    {
        FakeAccess::clearAccess(false, [], [], 'stranger');
        $this->expectException(NoAccessException::class);
        API::getInstance()->sendFeedback(1, 'board', 'up');
    }

    public function test_anonymous_noVote(): void
    {
        FakeAccess::clearAccess(false, [], [1], 'anonymous');
        $this->expectException(NoAccessException::class);
        API::getInstance()->sendFeedback(1, 'board', 'up');
    }

    public function test_deletingAUser_removesItsThrottles(): void
    {
        Option::set(PluginFeedback::throttleOption('anna', 'board'), '123');
        Option::set(PluginFeedback::throttleOption('bert', 'board'), '456');

        (new FeinwerkLiveDashboard())->deleteUserFeedback('anna');

        $this->assertFalse(Option::get(PluginFeedback::throttleOption('anna', 'board')));
        $this->assertSame('456', Option::get(PluginFeedback::throttleOption('bert', 'board')));
    }

    public function test_theQuestion_onlyOnTheBoardsPage(): void
    {
        $page = html_entity_decode((new GetLiveBoard())->render());
        $this->assertStringContainsString('feedback="{', $page);
        $this->assertStringContainsString('mtm_source=FeinwerkLiveDashboard', $page);

        $this->assertStringNotContainsString('feedback="{', html_entity_decode((new GetLiveOverview())->render()), 'not in dashboard widgets');

        $_GET['module'] = 'Widgetize';
        try {
            $this->assertStringNotContainsString('feedback="{', html_entity_decode((new GetLiveBoard())->render()), 'not in Widgetize embeds');
        } finally {
            unset($_GET['module']);
        }
    }
}
