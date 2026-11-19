<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Data;

/**
 * Runs the log reads of LogQueries.
 */
interface LogReader
{
    /** @throws QueryTimeout */
    public function scalar(array $query): ?string;

    /** @throws QueryTimeout */
    public function rows(array $query): array;

    /**
     * Streams the rows one by one, so PHP holds one row at a time (the buffered result set stays at its byte size).
     *
     * @param callable $row fn(array $row): void
     * @throws QueryTimeout
     */
    public function each(array $query, callable $row): void;

    /** Seconds the reader is behind the primary for this website (0 without a separate reader). */
    public function lagSeconds(int $idSite): int;
}
