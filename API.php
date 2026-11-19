<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard;

use Piwik\Container\StaticContainer;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Plugins\FeinwerkLiveDashboard\Feedback\PluginFeedback;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Clock;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\Windows;
use Piwik\Plugins\FeinwerkLiveDashboard\Format\LiveLogIcons;
use Piwik\Plugins\FeinwerkLiveDashboard\Format\MatomoLabeler;
use Piwik\Plugins\FeinwerkLiveDashboard\Format\PayloadFormatter;
use Piwik\Plugins\FeinwerkLiveDashboard\Format\Privacy;
use Piwik\Plugins\FeinwerkLiveDashboard\Format\VisitorLog;
use Piwik\Plugins\FeinwerkLiveDashboard\Snapshot\SnapshotService;
use Piwik\Site;
use Piwik\Translation\Translator;
use Piwik\Version;

/**
 * Live data of a website from the shared snapshot: one primary key read per poll, at most one tier computed.
 *
 * @method static \Piwik\Plugins\FeinwerkLiveDashboard\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /** The free plugin has only "All visits"; Pro adds live views with segments. */
    public const ALL_VISITS = 0;

    public function __construct(private readonly SnapshotService $service, private readonly Clock $clock)
    {
    }

    /**
     * The live snapshot of a website.
     *
     * @param int $idSite
     * @param int $view the live view, 0 = all visits
     * @param string $since the version of the last answer: unchanged data then gives notModified without payload
     * @return array{payload: array, ages: array, level: int, version: string, nextPollSeconds: int, notModified: bool,
     *     lag: int, delayed: bool, stale: bool, generatedAt: int, computedMs: array, logIcons: array}
     */
    public function getSnapshot($idSite, $view = self::ALL_VISITS, $since = '')
    {
        $idSite = (int) trim((string) $idSite);
        Piwik::checkUserHasViewAccess($idSite);
        $view = (int) trim((string) $view);
        if ($view !== self::ALL_VISITS) {
            throw new \Exception(Piwik::translate('FeinwerkLiveDashboard_ErrorUnknownView'));
        }
        $since = trim((string) $since);

        $visitorLogEnabled = self::isVisitorLogEnabled($idSite);
        $result = $this->service->poll($idSite, $view, (string) Site::getTimezoneFor($idSite), $visitorLogEnabled, $since);

        $payload = [];
        if (!$result->notModified) {
            $payload = Privacy::apply(PayloadFormatter::format($result->payload, new MatomoLabeler($idSite)), $visitorLogEnabled, self::rounding($idSite));
        }

        return [
            'payload' => $payload,
            'ages' => $result->ages,
            'level' => $result->level,
            'version' => $result->version,
            'nextPollSeconds' => $result->nextPollSeconds,
            'notModified' => $result->notModified,
            'lag' => $result->lag,
            'delayed' => $result->lag > Windows::DELAY_SECONDS,
            'stale' => $result->lag > Windows::STALE_SECONDS,
            'generatedAt' => $this->clock->now(),
            'computedMs' => $result->computedMs,
            'logIcons' => self::logIcons($idSite),
        ];
    }

    /**
     * One thumbs vote under the board (Feinwerk feedback kit). The Matomo server sends it to Feinwerk, never the browser;
     * nothing about the user, the website or its visitors. Off with [FeinwerkLiveDashboard] feedback = 0.
     *
     * @return array{sent: bool}
     */
    public function sendFeedback(int $idSite, string $topic, string $vote): array
    {
        Piwik::checkUserIsNotAnonymous();
        Piwik::checkUserHasViewAccess($idSite);
        if (!PluginFeedback::enabled()) {
            return ['sent' => false];
        }
        try {
            $payload = PluginFeedback::payload(
                trim($topic),
                trim($vote),
                (string) PluginManager::getInstance()->getLoadedPlugin(PluginFeedback::PLUGIN)->getVersion(),
                Version::VERSION,
                (string) ($_SERVER['HTTP_USER_AGENT'] ?? ''),
                (string) StaticContainer::get(Translator::class)->getCurrentLanguage()
            );
        } catch (\InvalidArgumentException $e) {
            throw new \Exception(Piwik::translate('FeinwerkLiveDashboard_ErrorInvalidValue', [$e->getMessage()]));
        }
        // one vote per user and question in 10 seconds, at most PluginFeedback::SERVER_CAP a minute per server
        $key = PluginFeedback::throttleOption(Piwik::getCurrentUserLogin(), $payload['topic']);
        if (time() - (int) Option::get($key) < 10) {
            return ['sent' => false];
        }
        $server = PluginFeedback::countVote((string) Option::get(PluginFeedback::SERVER_OPTION), time());
        if ($server === null) {
            return ['sent' => false];
        }
        Option::set($key, (string) time());
        Option::set(PluginFeedback::SERVER_OPTION, $server);
        return ['sent' => (new PluginFeedback())->send($payload)];
    }

    /** The places of the live log's icon column: Matomo's own icons, then those of other plugins (event). */
    private static function logIcons(int $idSite): array
    {
        $extra = [];
        try {
            Piwik::postEvent(LiveLogIcons::EVENT_ICONS, [&$extra, $idSite]);
        } catch (\Throwable $e) {
            $extra = [];
        }
        return LiveLogIcons::definitions((bool) Site::isEcommerceEnabledFor($idSite), [Piwik::class, 'translate'], $extra);
    }

    /** Visitor-level panels only while the website's visits log is on (Matomo's own rule for its real-time widget). */
    private static function isVisitorLogEnabled(int $idSite): bool
    {
        $live = 'Piwik\Plugins\Live\Live';
        return VisitorLog::enabled(
            PluginManager::getInstance()->isPluginActivated('Live'),
            static fn (): bool => class_exists($live) && method_exists($live, 'isVisitorLogEnabled') && $live::isVisitorLogEnabled($idSite)
        );
    }

    /** Matomo 5.14+ rounds counts when data rounding is on for the website (CNIL mode); 5.0 to 5.13 have no rounding. */
    private static function rounding(int $idSite): ?callable
    {
        $class = 'Piwik\Plugins\PrivacyManager\DataRounding';
        if (!class_exists($class) || !method_exists($class, 'isDataRoundingEnabledForAnySites') || !method_exists($class, 'roundCount')) {
            return null;
        }
        if (!$class::isDataRoundingEnabledForAnySites([$idSite])) {
            return null;
        }
        return static fn ($count): int => (int) $class::roundCount($count);
    }
}
