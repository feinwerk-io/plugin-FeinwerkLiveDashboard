<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Unit\Data;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkLiveDashboard\Data\StatementTimeLimit;
use Piwik\Plugins\FeinwerkLiveDashboard\tests\Fakes\FakeDb;

/**
 * @group FeinwerkLiveDashboard
 */
class StatementTimeLimitTest extends TestCase
{
    private const SQL = 'SELECT COUNT(*) AS n FROM log_visit WHERE idsite = ?';

    public function test_tag_namesThePlugin(): void
    {
        $this->assertSame('SELECT /* FeinwerkLiveDashboard */ COUNT(*) AS n FROM log_visit WHERE idsite = ?', StatementTimeLimit::tag(self::SQL));
        $this->assertSame('UPDATE x SET a = 1', StatementTimeLimit::tag('UPDATE x SET a = 1'));
    }

    public function test_mariaDb_getsSetStatement(): void
    {
        $this->assertSame(
            'SET STATEMENT max_statement_time=3 FOR ' . StatementTimeLimit::tag(self::SQL),
            StatementTimeLimit::apply(StatementTimeLimit::tag(self::SQL), 3, '10.11.6-MariaDB-0+deb12u1')
        );
        $this->assertStringStartsWith('SET STATEMENT max_statement_time=3 FOR ', StatementTimeLimit::apply(self::SQL, 3, '5.5.5-10.6.18-MariaDB'));
    }

    public function test_mysql_getsTheOptimizerHintFirst(): void
    {
        $this->assertSame(
            'SELECT /*+ MAX_EXECUTION_TIME(3000) */ /* FeinwerkLiveDashboard */ COUNT(*) AS n FROM log_visit WHERE idsite = ?',
            StatementTimeLimit::apply(StatementTimeLimit::tag(self::SQL), 3, '8.0.36')
        );
        $this->assertStringContainsString('MAX_EXECUTION_TIME(3000)', StatementTimeLimit::apply(self::SQL, 3, '5.7.8-log'));
    }

    public function test_oldMysqlAndNonSelects_stayUnchanged(): void
    {
        $this->assertSame(self::SQL, StatementTimeLimit::apply(self::SQL, 3, '5.7.7'));
        $this->assertSame('DELETE FROM x', StatementTimeLimit::apply('DELETE FROM x', 3, '8.0.36'));
        $this->assertSame(self::SQL, StatementTimeLimit::apply(self::SQL, 0, '8.0.36'));
    }

    public function test_aLimitIsNeverAppliedTwice(): void
    {
        $once = StatementTimeLimit::apply(self::SQL, 3, '10.11.6-MariaDB');
        $this->assertSame($once, StatementTimeLimit::apply($once, 3, '10.11.6-MariaDB'));
    }

    public function test_timeouts_areRecognisedByCodeAndMessage(): void
    {
        $db = new FakeDb([3024]);
        $this->assertTrue(StatementTimeLimit::isTimeoutError(new \Exception('x'), $db));
        $this->assertTrue(StatementTimeLimit::isTimeoutError(new \Exception('Query execution was interrupted (max_statement_time exceeded)'), new FakeDb()));
        $this->assertTrue(StatementTimeLimit::isTimeoutError(new \Exception('maximum statement execution time exceeded'), new FakeDb()));
        $this->assertTrue(StatementTimeLimit::isTimeoutError(new \Exception('x'), new FakeDb([1969])));
        $this->assertTrue(StatementTimeLimit::isTimeoutError(new \Exception('x'), new FakeDb([1028])));
        $this->assertFalse(StatementTimeLimit::isTimeoutError(new \Exception('Table does not exist'), new FakeDb([1146])));
    }
}
