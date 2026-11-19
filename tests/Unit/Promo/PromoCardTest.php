<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Unit\Promo;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkLiveDashboard\Promo\PromoCard;
use Piwik\Plugins\FeinwerkLiveDashboard\Promo\PromoLine;

/**
 * Where the board shows its promotion and the card in its dashboard widgets (Peter, 2026-11-19: the tip line under the
 * board page, the card in the dashboard widgets; the kit's single-card form).
 *
 * @group Plugins
 * @group FeinwerkLiveDashboard
 */
class PromoCardTest extends TestCase
{
    public function test_the_line_on_the_boards_page_the_card_in_a_dashboard_nothing_in_an_embed(): void
    {
        self::assertSame(PromoCard::LINE, PromoCard::placeFor(true, false, false), 'Visitors > Live Dashboard');
        self::assertSame(PromoCard::CARD, PromoCard::placeFor(false, false, true), 'a dashboard widget');
        self::assertSame(PromoCard::CARD, PromoCard::placeFor(true, false, true), 'the whole board on a dashboard: the card, never the line');
        self::assertSame(PromoCard::CARD, PromoCard::placeFor(false, false, false), 'a part of the board is a dashboard widget only');
        foreach ([[true, false], [true, true], [false, false], [false, true]] as [$fullBoard, $inDashboard]) {
            self::assertNull(PromoCard::placeFor($fullBoard, true, $inDashboard), 'Widgetize');
        }
    }

    public function test_the_card_shows_the_message_of_the_tip_line_under_a_key_of_its_own(): void
    {
        $translate = static function (string $key): string {
            return '[' . $key . ']';
        };
        $line = PromoLine::variables('campaigns', 'de', $translate);
        $card = PromoCard::fromLine($line, 'Learn more');

        // the card's link is short (a narrow dashboard column); the line's long link text goes into its title
        self::assertSame([
            'id' => 'campaigns-card',
            'days' => 30,
            'demo' => false,
            'name' => '[FeinwerkLiveDashboard_PromoCampaignsTitle]',
            'text' => '[FeinwerkLiveDashboard_PromoCampaignsText]',
            'link' => 'Learn more',
            'url' => 'https://campaigns.feinwerk.io/de/matomo?mtm_campaign=plugin-promo&mtm_source=FeinwerkLiveDashboard&mtm_content=campaigns',
            'external' => true,
            'note' => '',
            'title' => '[FeinwerkLiveDashboard_PromoCampaignsLink]',
        ], $card);
        self::assertNotSame($line['id'], $card['id'], 'hiding the card never hides the line');
        self::assertNull(PromoCard::fromLine(null, 'Learn more'));
    }

    /** The line's sentence may end in a colon (its link finishes it there); the card's short link does not, so it goes. */
    public function test_the_card_sentence_does_not_end_in_a_colon(): void
    {
        $line = [
            'id' => 'connector', 'dismissDays' => 30, 'demo' => false, 'title' => 'Feinwerk Connector',
            'text' => 'Exclusive Plugins for Matomo by Feinwerk, free, freemium and premium: ', 'link' => 'Install it',
            'url' => 'x', 'external' => true, 'note' => '',
        ];
        self::assertSame('Exclusive Plugins for Matomo by Feinwerk, free, freemium and premium', PromoCard::fromLine($line, 'Learn more')['text']);
    }

    /** For a super user the connector's link stays inside Matomo; an admin gets the note that the super user installs. */
    public function test_the_connector_card_keeps_where_its_link_leads(): void
    {
        $translate = static function (string $key): string {
            return '[' . $key . ']';
        };
        $super = PromoCard::fromLine(PromoLine::variables('connector', 'de', $translate, 'line', false, ['superUser' => true, 'marketplace' => true]), 'More');
        $admin = PromoCard::fromLine(PromoLine::variables('connector', 'en', $translate, 'line', false, ['superUser' => false, 'marketplace' => false]), 'More');

        self::assertSame(['connector-card', false, ''], [$super['id'], $super['external'], $super['note']]);
        self::assertStringStartsWith('index.php?module=Marketplace', $super['url']);
        self::assertSame([true, '[FeinwerkLiveDashboard_PremiumAskSuperUser]'], [$admin['external'], $admin['note']]);
        self::assertSame('[FeinwerkLiveDashboard_PremiumAskSuperUser]', $admin['title'], 'the admin note wins the title');
    }

    /** Demo mode (test instances): the card's x keeps it away for the browser session only. */
    public function test_in_demo_the_card_hides_for_the_session_only(): void
    {
        $line = PromoLine::variables('hosting', 'en', static function (string $key): string {
            return $key;
        }, 'line', true);

        self::assertSame([0, true], [PromoCard::fromLine($line, 'More')['days'], PromoCard::fromLine($line, 'More')['demo']]);
    }

    /** The kit's single-card form (docs/footer.md), with what the tip line's template honours as well. */
    public function test_the_template(): void
    {
        $card = (string) file_get_contents(__DIR__ . '/../../../templates/_promoCard.twig');
        $markup = (string) preg_replace('/\{#.*?#\}/s', '', $card);

        self::assertStringContainsString('<div class="fwpromo-card" data-fwpromo-id="{{ promoCard.id }}" data-fwpromo-days="{{ promoCard.days }}"', $markup);
        self::assertStringContainsString('{% if promoCard.demo ?? false %} data-fwpromo-demo="1"{% endif %}', $markup);
        self::assertStringContainsString('<span class="fwpromo-card__mark"><svg', $markup);
        self::assertStringContainsString('<strong>{{ promoCard.name }}</strong>', $markup);
        self::assertStringContainsString('{% if promoCard.external ?? true %} target="_blank" rel="noopener"{% endif %}', $markup);
        self::assertStringContainsString('{% if promoCard.title ?? \'\' %} title="{{ promoCard.title }}"{% endif %}', $markup);
        self::assertStringContainsString('class="fwpromo-card__close" title="{{ \'FeinwerkLiveDashboard_PromoDismiss\'|translate }}"', $markup);
        self::assertSame(0, preg_match('/(?<![\w-])id="|(?<![\w-])href="(?!\{\{)|(?<![\w-])src=|url\(/', $markup), 'no ids (five widgets on a page), nothing loaded');
        self::assertSame(0, preg_match('/\|raw\b/', $markup), 'texts are escaped');
    }

    /** The board puts the line or the card after its mount point; the board's own spacing rule depends on that. */
    public function test_the_board_includes_line_and_card_after_its_mount_point(): void
    {
        $board = (string) file_get_contents(__DIR__ . '/../../../templates/board.twig');
        $mount = strpos($board, '></div>');

        self::assertNotFalse($mount);
        self::assertGreaterThan($mount, strpos($board, "{% if promo %}{% include '@FeinwerkLiveDashboard/_promo.twig' %}{% endif %}"));
        self::assertGreaterThan($mount, strpos($board, "{% if promoCard %}{% include '@FeinwerkLiveDashboard/_promoCard.twig' %}{% endif %}"));
    }
}
