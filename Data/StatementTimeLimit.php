<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Data;

/**
 * Statement time limit for the server actually in use (MariaDB: SET STATEMENT max_statement_time; MySQL 5.7.8+: the
 * MAX_EXECUTION_TIME hint). Matomo's own helper picks the syntax from the configured schema and, at the 5.0.0 floor,
 * knows only the MySQL hint, which MariaDB silently ignores. Same approach as Feinwerk Data Exploration.
 */
final class StatementTimeLimit
{
    private const TIMEOUT_ERROR_CODES = [3024, 1028, 1969];
    private const TIMEOUT_MESSAGES = ['maximum statement execution time exceeded', 'max_statement_time exceeded'];

    /**
     * Names the plugin in a SELECT, so database admins find its reads in SHOW PROCESSLIST and the slow query log.
     * apply() keeps the MySQL optimizer hint first, where MySQL reads it.
     */
    public static function tag(string $sql): string
    {
        $sql = trim($sql);
        return preg_match('/^SELECT\b/i', $sql) ? 'SELECT /* FeinwerkLiveDashboard */' . substr($sql, strlen('SELECT')) : $sql;
    }

    public static function apply(string $sql, int $seconds, string $serverVersion): string
    {
        $sql = trim($sql);
        if ($seconds <= 0 || !preg_match('/^SELECT\b/i', $sql)
            || stripos($sql, 'max_statement_time=') !== false || stripos($sql, 'MAX_EXECUTION_TIME(') !== false
        ) {
            return $sql;
        }

        if (stripos($serverVersion, 'mariadb') !== false) {
            return 'SET STATEMENT max_statement_time=' . $seconds . ' FOR ' . $sql;
        }

        if (preg_match('/^(\d+\.\d+\.\d+)/', $serverVersion, $m) && version_compare($m[1], '5.7.8', '>=')) {
            return 'SELECT /*+ MAX_EXECUTION_TIME(' . ($seconds * 1000) . ') */' . substr($sql, strlen('SELECT'));
        }

        return $sql;
    }

    /**
     * @param object $db a Matomo db adapter (isErrNo())
     */
    public static function isTimeoutError(\Throwable $e, $db): bool
    {
        foreach (self::TIMEOUT_ERROR_CODES as $code) {
            if ($db->isErrNo($e, $code)) {
                return true;
            }
        }
        foreach (self::TIMEOUT_MESSAGES as $message) {
            if (stripos($e->getMessage(), $message) !== false) {
                return true;
            }
        }
        return false;
    }
}
