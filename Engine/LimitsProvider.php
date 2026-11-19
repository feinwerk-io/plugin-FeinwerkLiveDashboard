<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Engine;

/**
 * Builds Limits from the config section [FeinwerkLiveDashboard] and Matomo's [General] section. A value that is not a
 * whole number of zero or more falls back to its default; a number outside its range is clamped.
 */
final class LimitsProvider
{
    /** config key => [constructor argument, minimum, maximum] */
    private const INT_KEYS = [
        'fast_seconds' => ['fastSeconds', 5, 300],
        'slow_seconds' => ['slowSeconds', 30, 3600],
        'today_seconds' => ['todaySeconds', 60, 3600],
        'statement_seconds' => ['statementSeconds', 1, 60],
        'budget_seconds' => ['budgetSeconds', 1, 60],
        'visit_row_cap' => ['visitRowCap', 1, 10000000],
        'action_row_cap' => ['actionRowCap', 1, 10000000],
        'max_concurrent' => ['maxConcurrent', 1, 20],
        'max_active_keys' => ['maxActiveKeys', 1, 1000],
        'city_min_visits' => ['cityMinVisits', 1, 1000],
        'type_lookup_cap' => ['typeLookupCap', 1, 1000000],
    ];

    /** Matomo's own "visitors in real time" minutes, the same "active now" as its widget (concept 2.2) */
    private const ACTIVE_MINUTES_KEY = 'live_widget_visitor_count_last_minutes';

    public static function fromArrays(array $section, array $general): Limits
    {
        $defaults = new Limits();
        $args = [];
        foreach (self::INT_KEYS as $key => [$name, $min, $max]) {
            $value = self::wholeNumber($section[$key] ?? null);
            $args[$name] = $value === null ? $defaults->$name : max($min, min($max, $value));
        }

        $active = self::wholeNumber($general[self::ACTIVE_MINUTES_KEY] ?? null);
        $args['activeMinutes'] = $active === null ? $defaults->activeMinutes : max(1, min(30, $active));

        $useReader = self::wholeNumber($section['use_reader'] ?? null);
        $args['useReader'] = $useReader === null ? $defaults->useReader : $useReader > 0;

        return new Limits(...$args);
    }

    /** The limits from Matomo's config; the DI container keeps one instance per request (config/config.php). */
    public static function get(): Limits
    {
        $config = \Piwik\Config::getInstance();
        $section = $config->FeinwerkLiveDashboard;
        $general = $config->General;
        return self::fromArrays(is_array($section) ? $section : [], is_array($general) ? $general : []);
    }

    private static function wholeNumber($value): ?int
    {
        if (is_int($value)) {
            return $value >= 0 ? $value : null;
        }
        if (is_string($value) && preg_match('/^\s*\d+\s*$/', $value)) {
            return (int) $value;
        }
        return null;
    }
}
