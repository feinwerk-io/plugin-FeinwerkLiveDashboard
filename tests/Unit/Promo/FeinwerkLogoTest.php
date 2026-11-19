<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Unit\Promo;

use PHPUnit\Framework\TestCase;

/**
 * The Feinwerk logo in banners (Peter, 2026-11-13: "in die banner, wo möglich bitte generell noch das feinwerk-logo
 * unterbringen"; docs/promotions.md): one shared partial, nothing loaded, no font dependency, hidden from screen readers,
 * in the banner and the boxes and at the start of the tip line, never in the feedback row or the dashboard card.
 */
class FeinwerkLogoTest extends TestCase
{
    private const INCLUDE = "{% include '@FeinwerkLiveDashboard/_feinwerkLogo.twig' %}";

    public function test_the_logo_loads_nothing_and_needs_no_font(): void
    {
        $logo = $this->file('templates/_feinwerkLogo.twig');

        self::assertStringContainsString('aria-hidden="true"', $logo);
        self::assertStringContainsString('<svg class="fwlogo__mark"', $logo);
        self::assertSame(0, preg_match('/<text\b|<image\b|<img\b|\bhref=|\bsrc=|url\(/', $logo), 'inline only');
        self::assertSame(0, preg_match('/\burl\(|@import|@font-face/', $this->file('stylesheets/feinwerklogo.css')));
    }

    /** Peter, 2026-11-17: "feinwerk logo immer dabei": also at the start of the tip line; the feedback row and the card stay without. */
    public function test_the_banner_and_the_tip_line_show_it_the_feedback_row_and_the_card_do_not(): void
    {
        foreach (['templates/_promoBox.twig', 'templates/_promo.twig'] as $with) {
            self::assertSame(1, substr_count($this->file($with), self::INCLUDE), $with);
        }
        $line = $this->file('templates/_promo.twig');
        self::assertLessThan(strpos($line, 'fwpromo-line__text'), strpos($line, self::INCLUDE), 'the logo starts the line');
        // the feedback row is part of the Vue board here
        foreach (['vue/src/FeedbackBar.vue', 'vue/src/LiveBoard.vue', 'templates/_promoCard.twig'] as $without) {
            self::assertStringNotContainsString('_feinwerkLogo', $this->file($without), $without);
        }
    }

    /** In the tip line at text height, and on narrow screens only the round mark (no size containment on the line). */
    public function test_in_the_tip_line_at_text_height_and_only_the_mark_on_narrow_screens(): void
    {
        $css = (string) preg_replace('~/\*.*?\*/~s', '', $this->file('stylesheets/promo.css'));

        self::assertSame(0, preg_match('/container-type/', $css), 'containment could collapse the line elsewhere');
        self::assertSame(1, preg_match('/\.fwpromo-line \.fwlogo \.fwlogo__mark\s*\{[^}]*width:\s*14px[^}]*height:\s*14px/', $css));
        self::assertSame(1, preg_match('/@media \(max-width:\s*600px\)\s*\{\s*\.fwpromo-line \.fwlogo \.fwlogo__word\s*\{\s*display:\s*none/', $css));
    }

    public function test_light_and_dark_use_the_colours_of_the_store_logo(): void
    {
        $css = $this->file('stylesheets/feinwerklogo.css');

        // feinwerk-plugins-logo.svg (light) and -logo-white.svg (dark)
        foreach (['#0e7c7b', '#1a1714', '#6b6359', '#9fd8d2', '#e8e6e1', '#6b7180'] as $colour) {
            self::assertStringContainsString($colour, $css);
        }
        self::assertStringContainsString('[data-theme-mode="dark"] .fwlogo', $css);
        self::assertStringContainsString('[data-theme-mode="auto"] .fwlogo', $css);
    }

    private function file(string $path): string
    {
        return (string) file_get_contents(__DIR__ . '/../../../' . $path);
    }
}
