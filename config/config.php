<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

use Piwik\Common;
use Piwik\DI;
use Piwik\Plugins\FeinwerkLiveDashboard\Data\DbLogReader;
use Piwik\Plugins\FeinwerkLiveDashboard\Data\LogQueries;
use Piwik\Plugins\FeinwerkLiveDashboard\Data\LogReader;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Clock;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Limits;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\LimitsProvider;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\SystemClock;
use Piwik\Plugins\FeinwerkLiveDashboard\Snapshot\DbSnapshotStore;
use Piwik\Plugins\FeinwerkLiveDashboard\Snapshot\Locks;
use Piwik\Plugins\FeinwerkLiveDashboard\Snapshot\MatomoLocks;
use Piwik\Plugins\FeinwerkLiveDashboard\Snapshot\MatomoTierComputer;
use Piwik\Plugins\FeinwerkLiveDashboard\Snapshot\SnapshotStore;
use Piwik\Plugins\FeinwerkLiveDashboard\Snapshot\TierComputer;

// Piwik\DI exists since Matomo 5.0 (the floor of this plugin).
return [
    Clock::class => DI::autowire(SystemClock::class),
    Limits::class => DI::factory(static fn (): Limits => LimitsProvider::get()),
    SnapshotStore::class => DI::autowire(DbSnapshotStore::class),
    Locks::class => DI::autowire(MatomoLocks::class),
    LogReader::class => DI::autowire(DbLogReader::class),
    LogQueries::class => DI::factory(static fn (): LogQueries => new LogQueries([Common::class, 'prefixTable'])),
    TierComputer::class => DI::autowire(MatomoTierComputer::class),
];
