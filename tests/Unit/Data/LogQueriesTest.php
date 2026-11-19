<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Unit\Data;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkLiveDashboard\Data\LogQueries;

/**
 * Every read is a range on an existing index, names its columns, binds its times and never uses NOW() (concept 3.4).
 *
 * @group FeinwerkLiveDashboard
 */
class LogQueriesTest extends TestCase
{
    private const FROM = '2026-11-18 11:30:00';
    private const TO = '2026-11-18 12:00:00';

    private static function queries(): LogQueries
    {
        return new LogQueries(static fn (string $table): string => 'mt_' . $table);
    }

    private static function assertSafeSelect(array $query): void
    {
        self::assertMatchesPattern('/^SELECT /', $query['sql']);
        self::assertStringNotContainsString('*', preg_replace('/COUNT\(\*\)/', '', $query['sql']));
        self::assertStringNotContainsStringIgnoringCase('NOW()', $query['sql']);
        self::assertSame(substr_count($query['sql'], '?'), count($query['bind']));
    }

    private static function assertMatchesPattern(string $pattern, string $subject): void
    {
        self::assertSame(1, preg_match($pattern, $subject), "$subject does not match $pattern");
    }

    public function test_countVisits_isAnIndexRangeOnLastActionTime(): void
    {
        $q = self::queries()->countVisits(3, self::FROM, self::TO);

        self::assertSafeSelect($q);
        $this->assertSame(
            'SELECT COUNT(*) AS n FROM mt_log_visit WHERE idsite = ? AND visit_last_action_time >= ? AND visit_last_action_time <= ?',
            $q['sql']
        );
        $this->assertSame([3, self::FROM, self::TO], $q['bind']);
    }

    public function test_visitRows_namesItsColumnsAndSamplesOnlyAboveTheCap(): void
    {
        $q = self::queries()->visitRows(3, self::FROM, self::TO, 1, false);

        self::assertSafeSelect($q);
        foreach (['idvisit', 'visit_last_action_time', 'visit_first_action_time', 'visit_exit_idaction_url', 'visit_exit_idaction_name',
                     'referer_type', 'referer_name', 'location_country', 'config_device_type', 'visit_goal_converted'] as $column) {
            $this->assertStringContainsString($column, $q['sql']);
        }
        $this->assertStringNotContainsString('location_city', $q['sql']);
        $this->assertStringNotContainsString('%', $q['sql']);
        $this->assertStringContainsString('FROM mt_log_visit WHERE idsite = ? AND visit_last_action_time >= ? AND visit_last_action_time <= ?', $q['sql']);
        $this->assertSame([3, self::FROM, self::TO], $q['bind']);

        $sampled = self::queries()->visitRows(3, self::FROM, self::TO, 4, false);
        $this->assertStringEndsWith(' AND idvisit % 4 = 0', $sampled['sql']);
        $this->assertSame([3, self::FROM, self::TO], $sampled['bind']);
    }

    public function test_visitRows_withCampaignSource_addsTheColumn(): void
    {
        $this->assertStringNotContainsString('campaign_source', self::queries()->visitRows(3, self::FROM, self::TO, 1, false)['sql']);
        $this->assertStringContainsString(', campaign_source FROM', self::queries()->visitRows(3, self::FROM, self::TO, 1, false, true)['sql']);
    }

    public function test_visitRows_withCities_addsRoundedCoordinates(): void
    {
        $q = self::queries()->visitRows(3, self::FROM, self::TO, 1, true);

        foreach (['location_region', 'location_city', 'ROUND(location_latitude, 2) AS lat', 'ROUND(location_longitude, 2) AS lon'] as $part) {
            $this->assertStringContainsString($part, $q['sql']);
        }
    }

    public function test_countActions_isAnIndexRangeOnServerTime(): void
    {
        $q = self::queries()->countActions(3, self::FROM, self::TO);

        self::assertSafeSelect($q);
        $this->assertSame(
            'SELECT COUNT(*) AS n FROM mt_log_link_visit_action WHERE idsite = ? AND server_time >= ? AND server_time <= ?',
            $q['sql']
        );
        $this->assertSame([3, self::FROM, self::TO], $q['bind']);
    }

    public function test_actionRows_namesItsColumnsAndSamplesByHitId(): void
    {
        $q = self::queries()->actionRows(3, self::FROM, self::TO, 1);

        self::assertSafeSelect($q);
        $this->assertSame(
            'SELECT idlink_va, server_time, idaction_url, idaction_name, idaction_event_category, idaction_event_action'
            . ' FROM mt_log_link_visit_action WHERE idsite = ? AND server_time >= ? AND server_time <= ?',
            $q['sql']
        );
        $this->assertStringEndsWith(' AND idlink_va % 2 = 0', self::queries()->actionRows(3, self::FROM, self::TO, 2)['sql']);
    }

    public function test_conversions_areAnIndexRangeWithoutAbandonedCarts(): void
    {
        // counted in the database per goal and window: at most goals x windows rows, however many conversions
        $froms = ['60' => '2026-11-18 11:00:00', '3' => '2026-11-18 11:57:00', '30' => '2026-11-18 11:30:00', '10' => '2026-11-18 11:50:00'];
        $groups = self::queries()->conversionGroups(3, self::FROM, self::TO, $froms);
        self::assertSafeSelect($groups);
        $this->assertSame(
            "SELECT idgoal, CASE WHEN server_time >= ? THEN '3' WHEN server_time >= ? THEN '10' WHEN server_time >= ? THEN '30'"
            . " ELSE '60' END AS w, COUNT(*) AS conversions, COALESCE(SUM(revenue), 0) AS revenue"
            . ' FROM mt_log_conversion WHERE idsite = ? AND server_time >= ? AND server_time <= ? AND idgoal >= 0 GROUP BY idgoal, w',
            $groups['sql']
        );
        $this->assertSame(['2026-11-18 11:57:00', '2026-11-18 11:50:00', '2026-11-18 11:30:00', 3, self::FROM, self::TO], $groups['bind']);

        $totals = self::queries()->conversionTotals(3, self::FROM, self::TO);
        self::assertSafeSelect($totals);
        $this->assertSame(
            'SELECT COUNT(*) AS conversions, COALESCE(SUM(revenue), 0) AS revenue FROM mt_log_conversion'
            . ' WHERE idsite = ? AND server_time >= ? AND server_time <= ? AND idgoal >= 0',
            $totals['sql']
        );
        $this->assertSame([3, self::FROM, self::TO], $totals['bind']);
    }

    public function test_newestVisit_readsOneIndexEntry(): void
    {
        $q = self::queries()->newestVisit(3);
        self::assertSafeSelect($q);
        $this->assertSame('SELECT MAX(visit_last_action_time) AS newest FROM mt_log_visit WHERE idsite = ?', $q['sql']);
        $this->assertSame([3], $q['bind']);
    }

    public function test_actionLookups_usePrimaryKeysOfUniqueIntegers(): void
    {
        $types = self::queries()->actionTypes([5, '5', 3, 9]);
        self::assertSafeSelect($types);
        $this->assertSame('SELECT idaction, type FROM mt_log_action WHERE idaction IN (3, 5, 9)', $types['sql']);
        $this->assertSame([], $types['bind']);

        $names = self::queries()->actionNames([7]);
        $this->assertSame('SELECT idaction, name, type FROM mt_log_action WHERE idaction IN (7)', $names['sql']);
    }

    public function test_newestVisits_readTheEndOfTheIndexRange(): void
    {
        $q = self::queries()->newestVisits(3, self::FROM, self::TO, 10);

        self::assertSafeSelect($q);
        $this->assertStringContainsString(
            'FROM mt_log_visit WHERE idsite = ? AND visit_last_action_time >= ? AND visit_last_action_time <= ?'
            . ' ORDER BY visit_last_action_time DESC LIMIT 10',
            $q['sql']
        );
        foreach (['idvisit', 'visit_last_action_time', 'visit_first_action_time', 'visit_total_actions', 'visitor_returning',
                     'referer_type', 'referer_name', 'location_country', 'config_device_type', 'visit_exit_idaction_url',
                     'visit_exit_idaction_name', 'visit_goal_converted', 'visit_goal_buyer'] as $column) {
            $this->assertStringContainsString($column, $q['sql']);
        }
        foreach (['location_ip', 'idvisitor', 'user_id', 'config_id', 'location_city', 'location_latitude'] as $private) {
            $this->assertStringNotContainsString($private, $q['sql'], 'no identifier and no city for a single visit');
        }
        $this->assertSame([3, self::FROM, self::TO], $q['bind']);
        $this->assertStringEndsWith('LIMIT 1', self::queries()->newestVisits(3, self::FROM, self::TO, 0)['sql']);
    }

    public function test_actionLookups_withoutIds_areNoQuery(): void
    {
        $this->assertNull(self::queries()->actionTypes([]));
        $this->assertNull(self::queries()->actionNames([0, -3]));
    }
}
