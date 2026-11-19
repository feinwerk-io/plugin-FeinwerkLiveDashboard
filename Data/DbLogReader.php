<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Data;

use Piwik\Common;
use Piwik\Db;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Limits;

/**
 * Log reads on Matomo's reader database when one is configured (concept 3.6), each under the statement time limit.
 */
final class DbLogReader implements LogReader
{
    /** @var object|null the db adapter in use */
    private $db = null;
    private ?string $serverVersion = null;

    public function __construct(private readonly Limits $limits)
    {
    }

    public function scalar(array $query): ?string
    {
        $value = $this->run(static fn ($db, string $sql, array $bind) => $db->fetchOne($sql, $bind), $query);
        return $value === false || $value === null ? null : (string) $value;
    }

    public function rows(array $query): array
    {
        return $this->run(static fn ($db, string $sql, array $bind) => $db->fetchAll($sql, $bind), $query);
    }

    public function each(array $query, callable $row): void
    {
        $this->run(static function ($db, string $sql, array $bind) use ($row): void {
            $statement = $db->query($sql, $bind);
            while (($values = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
                $row($values);
            }
        }, $query);
    }

    public function lagSeconds(int $idSite): int
    {
        if (!$this->limits->useReader || !Db::hasReaderConfigured()) {
            return 0;
        }
        $query = (new LogQueries([Common::class, 'prefixTable']))->newestVisit($idSite);
        $reader = $this->scalar($query);
        $primary = Db::get()->fetchOne($query['sql'], $query['bind']);
        if ($reader === null || empty($primary)) {
            return 0;
        }
        return max(0, (int) strtotime($primary . ' UTC') - (int) strtotime($reader . ' UTC'));
    }

    private function run(callable $call, array $query)
    {
        $db = $this->db();
        $sql = StatementTimeLimit::apply(StatementTimeLimit::tag($query['sql']), $this->limits->statementSeconds, $this->serverVersion($db));
        try {
            return $call($db, $sql, $query['bind']);
        } catch (\Exception $e) {
            if (StatementTimeLimit::isTimeoutError($e, $db)) {
                throw new QueryTimeout('FeinwerkLiveDashboard: a log read hit the time limit of ' . $this->limits->statementSeconds . ' s', 0, $e);
            }
            throw $e;
        }
    }

    private function db()
    {
        if ($this->db === null) {
            $this->db = $this->limits->useReader ? Db::getReader() : Db::get();
        }
        return $this->db;
    }

    private function serverVersion($db): string
    {
        if ($this->serverVersion === null) {
            $this->serverVersion = (string) $db->getServerVersion();
        }
        return $this->serverVersion;
    }
}
