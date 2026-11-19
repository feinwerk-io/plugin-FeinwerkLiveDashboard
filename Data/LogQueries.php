<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Data;

/**
 * The SQL of every log read (concept 3.4). Each one is a range on an existing index: log_visit and log_conversion on
 * (idsite, visit_last_action_time) and (idsite, server_time), log_link_visit_action on (idsite, server_time), log_action
 * by primary key. Columns are named (no SELECT *, no IP, visitor ID, user ID or order ID), times are bound (no NOW()),
 * and above a row cap every k-th row is read by primary key modulo, which the secondary index carries.
 *
 * @phpstan-type Query array{sql: string, bind: array}
 */
final class LogQueries
{
    private const VISIT_COLUMNS = 'idvisit, visit_last_action_time, visit_first_action_time, visit_exit_idaction_url, visit_exit_idaction_name,'
        . ' referer_type, referer_name, location_country, config_device_type, visit_goal_converted';
    private const CITY_COLUMNS = ', location_region, location_city, ROUND(location_latitude, 2) AS lat, ROUND(location_longitude, 2) AS lon';
    private const ACTION_COLUMNS = 'idlink_va, server_time, idaction_url, idaction_name, idaction_event_category, idaction_event_action';

    /** @var callable fn(string $table): string */
    private $prefixTable;

    public function __construct(callable $prefixTable)
    {
        $this->prefixTable = $prefixTable;
    }

    public function countVisits(int $idSite, string $from, string $to): array
    {
        return $this->visitRange('SELECT COUNT(*) AS n', $idSite, $from, $to);
    }

    /**
     * @param bool $campaignSource also log_visit.campaign_source, a column of the plugin MarketingCampaignsReporting:
     *                             only when that plugin is active
     */
    public function visitRows(int $idSite, string $from, string $to, int $k, bool $cities, bool $campaignSource = false): array
    {
        $columns = self::VISIT_COLUMNS . ($cities ? self::CITY_COLUMNS : '') . ($campaignSource ? ', campaign_source' : '');
        $query = $this->visitRange('SELECT ' . $columns, $idSite, $from, $to);
        return self::sampled($query, 'idvisit', $k);
    }

    public function countActions(int $idSite, string $from, string $to): array
    {
        return $this->actionRange('SELECT COUNT(*) AS n', $idSite, $from, $to);
    }

    public function actionRows(int $idSite, string $from, string $to, int $k): array
    {
        return self::sampled($this->actionRange('SELECT ' . self::ACTION_COLUMNS, $idSite, $from, $to), 'idlink_va', $k);
    }

    /**
     * Conversions of goals and ecommerce orders counted in the database per goal and list window (abandoned carts,
     * idgoal -1, are no conversions): at most goals x windows rows, however many conversions an hour has.
     *
     * @param array<string|int, string> $windows window name => first time it holds; each conversion goes into the
     *                                           smallest window that holds it
     */
    public function conversionGroups(int $idSite, string $from, string $to, array $windows): array
    {
        arsort($windows, SORT_STRING); // latest start = smallest window first
        $names = array_map('intval', array_keys($windows));
        $starts = array_values($windows);
        $case = 'CASE';
        $bind = [];
        foreach (array_slice($names, 0, -1) as $i => $name) {
            $case .= " WHEN server_time >= ? THEN '" . $name . "'";
            $bind[] = (string) $starts[$i];
        }
        $case .= " ELSE '" . end($names) . "' END AS w";
        $query = $this->conversionRange(
            'SELECT idgoal, ' . $case . ', COUNT(*) AS conversions, COALESCE(SUM(revenue), 0) AS revenue',
            $idSite,
            $from,
            $to
        );
        return ['sql' => $query['sql'] . ' GROUP BY idgoal, w', 'bind' => array_merge($bind, $query['bind'])];
    }

    public function conversionTotals(int $idSite, string $from, string $to): array
    {
        return $this->conversionRange('SELECT COUNT(*) AS conversions, COALESCE(SUM(revenue), 0) AS revenue', $idSite, $from, $to);
    }

    /**
     * The newest visits for the live log: the end of the index range (idsite, visit_last_action_time), at most $limit
     * rows. No identifier (IP, visitor ID, user ID, config ID) and no city of a single visit (concept 3.10).
     */
    public function newestVisits(int $idSite, string $from, string $to, int $limit): array
    {
        $query = $this->visitRange(
            'SELECT idvisit, visit_last_action_time, visit_first_action_time, visit_total_actions, visitor_returning,'
            . ' referer_type, referer_name, location_country, config_device_type, visit_exit_idaction_url,'
            . ' visit_exit_idaction_name, visit_goal_converted, visit_goal_buyer',
            $idSite,
            $from,
            $to
        );
        $query['sql'] .= ' ORDER BY visit_last_action_time DESC LIMIT ' . max(1, $limit);
        return $query;
    }

    /** The newest activity of a website: one entry of the index (idsite, visit_last_action_time). */
    public function newestVisit(int $idSite): array
    {
        return ['sql' => 'SELECT MAX(visit_last_action_time) AS newest FROM ' . $this->table('log_visit') . ' WHERE idsite = ?', 'bind' => [$idSite]];
    }

    /** @return array|null null when there is no positive id to look up */
    public function actionTypes(array $ids): ?array
    {
        return $this->actionLookup('idaction, type', $ids);
    }

    /** @return array|null null when there is no positive id to look up */
    public function actionNames(array $ids): ?array
    {
        return $this->actionLookup('idaction, name, type', $ids);
    }

    private function visitRange(string $select, int $idSite, string $from, string $to): array
    {
        return [
            'sql' => $select . ' FROM ' . $this->table('log_visit')
                . ' WHERE idsite = ? AND visit_last_action_time >= ? AND visit_last_action_time <= ?',
            'bind' => [$idSite, $from, $to],
        ];
    }

    private function actionRange(string $select, int $idSite, string $from, string $to): array
    {
        return [
            'sql' => $select . ' FROM ' . $this->table('log_link_visit_action') . ' WHERE idsite = ? AND server_time >= ? AND server_time <= ?',
            'bind' => [$idSite, $from, $to],
        ];
    }

    private function conversionRange(string $select, int $idSite, string $from, string $to): array
    {
        return [
            'sql' => $select . ' FROM ' . $this->table('log_conversion')
                . ' WHERE idsite = ? AND server_time >= ? AND server_time <= ? AND idgoal >= 0',
            'bind' => [$idSite, $from, $to],
        ];
    }

    private function actionLookup(string $columns, array $ids): ?array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0)));
        if ($ids === []) {
            return null;
        }
        sort($ids);
        return [
            'sql' => 'SELECT ' . $columns . ' FROM ' . $this->table('log_action') . ' WHERE idaction IN (' . implode(', ', $ids) . ')',
            'bind' => [],
        ];
    }

    /** Every k-th row by primary key; k is an integer, so it is written into the SQL (readable in EXPLAIN). */
    private static function sampled(array $query, string $primaryKey, int $k): array
    {
        if ($k > 1) {
            $query['sql'] .= ' AND ' . $primaryKey . ' % ' . $k . ' = 0';
        }
        return $query;
    }

    private function table(string $name): string
    {
        return ($this->prefixTable)($name);
    }
}
