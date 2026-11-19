<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Promo;

/**
 * Where the live board shows its promotion, and the card in its dashboard widgets (Peter, 2026-11-19: the tip line under
 * the feedback row of the board page, the card in the dashboard widgets; the kit's single-card form, Page Filter
 * Report docs/footer.md "The card in dashboard widgets"). The card shows the tip line's message of the day: its title
 * as the bold name, its sentence and its link. Its id ends in "-card", so hiding the card never hides the line of the
 * same message and the other way round (localStorage fwpromo:<id>:dismissed, javascripts/promocard.js). Markup in
 * templates/_promoCard.twig, styles in stylesheets/promocard.css. Pure.
 */
final class PromoCard
{
    /** appended to the message id: a key of its own for the card */
    public const ID_SUFFIX = '-card';

    public const LINE = 'line';
    public const CARD = 'card';

    /**
     * The tip line on the board's own page, the card in a dashboard widget: the five dashboard-only widgets, and the
     * full board when someone puts it on a dashboard (house rule: in a dashboard widget the promotion is the card, never
     * the tip line). Nothing in a Widgetize embed.
     *
     * @param bool $fullBoard   the widget is the whole board (Visitors > Live Dashboard), not one of its parts
     * @param bool $embedded    Widgetize (module=Widgetize or disableLink=1, PromoLine::isEmbedded())
     * @param bool $inDashboard the request loads a dashboard widget (Matomo's dashboard sends widget=1)
     * @return string|null LINE, CARD or null
     */
    public static function placeFor(bool $fullBoard, bool $embedded, bool $inDashboard): ?string
    {
        if ($embedded) {
            return null;
        }

        return $fullBoard && !$inDashboard ? self::LINE : self::CARD;
    }

    /**
     * Variables of templates/_promoCard.twig from the tip line's variables (PromoLine::variables()), null without a line.
     *
     * @param array<string, mixed>|null $line
     * @return array{id: string, days: int, demo: bool, name: string, text: string, link: string, url: string, external: bool, note: string, title: string}|null
     */
    public static function fromLine(?array $line, string $more): ?array
    {
        if ($line === null) {
            return null;
        }

        return [
            'id' => $line['id'] . self::ID_SUFFIX,
            'days' => (int) $line['dismissDays'],
            'demo' => (bool) $line['demo'],
            'name' => (string) $line['title'],
            // the line's sentence may end in a colon that its link finishes; the card's short link does not
            'text' => rtrim((string) $line['text'], " \t:"),
            // a short link: the card sits in a narrow dashboard column; the line's long link text becomes its title
            'link' => $more,
            'url' => (string) $line['url'],
            'external' => (bool) $line['external'],
            'note' => (string) $line['note'],
            'title' => (string) $line['note'] !== '' ? (string) $line['note'] : (string) $line['link'],
        ];
    }
}
