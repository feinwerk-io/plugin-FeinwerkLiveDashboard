<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Format;

/**
 * The fixed icon column of the live log (Peter, 2026-11-18): one place per kind of icon, so nothing moves when rows
 * change, with Matomo's own images. Other plugins add places and flags through two events:
 *
 * - FeinwerkLiveDashboard.getLiveLogIcons (array &$icons, int $idSite): append ['key', 'src', 'label'] (key a-z, 0-9
 *   and "-", src an image path inside Matomo, label translated); own keys cannot be replaced.
 * - FeinwerkLiveDashboard.addLiveLogFlags (array &$flagsByIdVisit, int $idSite, int[] $idVisits): add keys to the
 *   flags of the visits shown, e.g. after one lookup by idvisit IN (...) on an index that starts with idvisit.
 */
final class LiveLogIcons
{
    public const EVENT_ICONS = 'FeinwerkLiveDashboard.getLiveLogIcons';
    public const EVENT_FLAGS = 'FeinwerkLiveDashboard.addLiveLogFlags';

    /** log_visit.visit_goal_buyer: 1 ordered, 2 abandoned cart, 3 both (CoreHome\Columns\VisitGoalBuyer) */
    private const BUYER_ORDERED = 1;
    private const BUYER_CART = 2;

    private const OWN = [
        'goal' => ['plugins/Morpheus/images/goal.svg', 'FeinwerkLiveDashboard_LogGoal', false],
        'order' => ['plugins/Morpheus/images/ecommerceOrder.svg', 'FeinwerkLiveDashboard_LogOrder', true],
        'cart' => ['plugins/Morpheus/images/ecommerceAbandonedCart.svg', 'FeinwerkLiveDashboard_LogCart', true],
        'returning' => ['plugins/Live/images/returningVisitor.png', 'FeinwerkLiveDashboard_LogReturning', false],
    ];

    /** @return string[] the own flags of a visit row, in the order of the places */
    public static function flags(array $row): array
    {
        $buyer = (int) ($row['visit_goal_buyer'] ?? 0);
        $flags = [];
        // visit_goal_converted only says "some conversion"; an order sets it too, and then the order icon says it
        if (!empty($row['visit_goal_converted']) && ($buyer & self::BUYER_ORDERED) === 0) {
            $flags[] = 'goal';
        }
        if ($buyer & self::BUYER_ORDERED) {
            $flags[] = 'order';
        }
        if ($buyer & self::BUYER_CART) {
            $flags[] = 'cart';
        }
        if (!empty($row['visitor_returning'])) {
            $flags[] = 'returning';
        }
        return $flags;
    }

    /**
     * @param callable $translate fn(string $key): string
     * @param array<int, array{key?: mixed, src?: mixed, label?: mixed}> $extra places of other plugins
     * @return array<int, array{key: string, src: string, label: string}>
     */
    public static function definitions(bool $ecommerce, callable $translate, array $extra): array
    {
        $icons = [];
        foreach (self::OWN as $key => [$src, $label, $shopOnly]) {
            if (!$shopOnly || $ecommerce) {
                $icons[$key] = ['key' => $key, 'src' => $src, 'label' => $translate($label)];
            }
        }
        foreach ($extra as $icon) {
            $key = (string) ($icon['key'] ?? '');
            $src = (string) ($icon['src'] ?? '');
            if (!preg_match('/^[a-z0-9-]{1,30}$/', $key) || isset($icons[$key]) || isset(self::OWN[$key])
                || !preg_match('~^plugins/[A-Za-z0-9_/.-]+\.(svg|png)$~', $src) || str_contains($src, '..')
            ) {
                continue;
            }
            $icons[$key] = ['key' => $key, 'src' => $src, 'label' => (string) ($icon['label'] ?? $key)];
        }
        return array_values($icons);
    }
}
