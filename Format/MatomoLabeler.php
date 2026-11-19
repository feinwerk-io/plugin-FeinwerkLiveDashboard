<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Format;

use Piwik\Piwik;
use Piwik\Plugins\Goals\API as GoalsApi;

/**
 * Labels through Matomo's own helpers, in the language of the request. Goal names of the website are read once.
 */
final class MatomoLabeler implements Labeler
{
    private const REFERRER_TYPE_AI_ASSISTANT = 8;

    /** @var array<int, string>|null */
    private ?array $goals = null;

    public function __construct(private readonly int $idSite)
    {
        foreach (['UserCountry', 'Referrers', 'DevicesDetection'] as $plugin) {
            $file = PIWIK_INCLUDE_PATH . '/plugins/' . $plugin . '/functions.php';
            if (is_file($file)) {
                require_once $file;
            }
        }
    }

    public function country(string $code): string
    {
        return function_exists('Piwik\Plugins\UserCountry\countryTranslate')
            ? \Piwik\Plugins\UserCountry\countryTranslate($code)
            : strtoupper($code);
    }

    public function referrerType(int $type): string
    {
        if ($type <= 0) {
            return $this->unknown();
        }
        // Matomo knows AI assistants as a channel from 5.14 on; before, its label helper calls them "Others"
        if ($type === self::REFERRER_TYPE_AI_ASSISTANT && !defined('Piwik\Common::REFERRER_TYPE_AI_ASSISTANT')) {
            return Piwik::translate('FeinwerkLiveDashboard_AiAssistants');
        }
        return function_exists('Piwik\Plugins\Referrers\getReferrerTypeLabel')
            ? \Piwik\Plugins\Referrers\getReferrerTypeLabel($type)
            : (string) $type;
    }

    public function deviceType(int $type): string
    {
        return function_exists('Piwik\Plugins\DevicesDetection\getDeviceTypeLabel')
            ? \Piwik\Plugins\DevicesDetection\getDeviceTypeLabel($type)
            : $this->unknown();
    }

    public function goal(int $idGoal): string
    {
        if ($this->goals === null) {
            $this->goals = [];
            try {
                foreach ((array) GoalsApi::getInstance()->getGoals($this->idSite) as $goal) {
                    $this->goals[(int) $goal['idgoal']] = (string) $goal['name'];
                }
            } catch (\Throwable $e) {
                // goal names are a nicety: without them the goal number is shown
            }
        }
        return $this->goals[$idGoal] ?? Piwik::translate('Goals_GoalX', [$idGoal]);
    }

    public function unknown(): string
    {
        return Piwik::translate('General_Unknown');
    }

    public function noPage(): string
    {
        return Piwik::translate('FeinwerkLiveDashboard_NoPage');
    }
}
