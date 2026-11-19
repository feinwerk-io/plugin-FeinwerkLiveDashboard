<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Panels;

/**
 * Splits hit counts by log_action.type (Matomo's Tracker\Action::TYPE_* values).
 */
final class ActionKinds
{
    public const TYPE_PAGE_URL = 1;
    public const TYPE_OUTLINK = 2;
    public const TYPE_DOWNLOAD = 3;
    public const TYPE_SITE_SEARCH = 8;

    /**
     * @param array<int, int> $urlCounts idaction_url => hits
     * @param array<int, int> $types idaction => type, possibly only for the most frequent ids (lookup cap)
     * @return array{pages: array<int, int>, downloads: array<int, int>, outlinks: array<int, int>, typesPartial: bool}
     */
    public static function split(array $urlCounts, array $types): array
    {
        $split = ['pages' => [], 'downloads' => [], 'outlinks' => [], 'typesPartial' => false];
        foreach ($urlCounts as $id => $count) {
            if (!isset($types[$id])) {
                // not looked up (above the lookup cap): most hits are page views
                $split['pages'][$id] = $count;
                $split['typesPartial'] = true;
                continue;
            }
            switch ((int) $types[$id]) {
                case self::TYPE_PAGE_URL:
                    $split['pages'][$id] = $count;
                    break;
                case self::TYPE_DOWNLOAD:
                    $split['downloads'][$id] = $count;
                    break;
                case self::TYPE_OUTLINK:
                    $split['outlinks'][$id] = $count;
                    break;
            }
        }
        return $split;
    }

    /**
     * @param array<int, int> $nameCounts idaction_name => hits of hits without a URL
     * @return array<int, int> only site search keywords
     */
    public static function searches(array $nameCounts, array $types): array
    {
        return array_filter(
            $nameCounts,
            static fn (int $id): bool => isset($types[$id]) && (int) $types[$id] === self::TYPE_SITE_SEARCH,
            ARRAY_FILTER_USE_KEY
        );
    }
}
