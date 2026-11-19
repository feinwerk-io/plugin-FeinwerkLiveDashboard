<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Widgets;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Plugins\FeinwerkLiveDashboard\Feedback\PluginFeedback;
use Piwik\Plugins\FeinwerkLiveDashboard\Engine\LimitsProvider;
use Piwik\Plugins\FeinwerkLiveDashboard\Format\WorldMapFile;
use Piwik\Plugins\FeinwerkLiveDashboard\Promo\PromoCard;
use Piwik\Plugins\FeinwerkLiveDashboard\Promo\PromoLine;
use Piwik\Site;
use Piwik\Translation\Translator;
use Piwik\Widget\Widget;
use Piwik\Widget\WidgetConfig;

/**
 * Visitors › Live Dashboard: the live board of a website as one wide widget. The Vue app polls the shared snapshot
 * itself (FeinwerkLiveDashboard.getSnapshot); configure() runs whenever Matomo builds the widget list, so it stays cheap.
 */
class GetLiveBoard extends Widget
{
    /** which part of the board the widget shows (vue/src/board.ts sectionsOf) */
    protected const VARIANT = 'full';

    /** The dashboard widgets: category only (no report page), enabled with view access. */
    protected static function configureDashboardWidget(WidgetConfig $config, string $name, int $order): void
    {
        $config->setCategoryId('FeinwerkLiveDashboard_Widgets');
        $config->setName($name);
        $config->setOrder($order);
        $idSite = Common::getRequestVar('idSite', 0, 'int');
        $config->setIsEnabled($idSite > 0 && Piwik::isUserHasViewAccess($idSite));
    }

    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('General_Visitors');
        $config->setSubcategoryId('FeinwerkLiveDashboard_LiveDashboard');
        $config->setName('FeinwerkLiveDashboard_LiveDashboard');
        $config->setOrder(1);
        $config->setIsWide();
        $idSite = Common::getRequestVar('idSite', 0, 'int');
        $config->setIsEnabled($idSite > 0 && Piwik::isUserHasViewAccess($idSite));
    }

    public function render()
    {
        $idSite = Common::getRequestVar('idSite', 0, 'int');
        Piwik::checkUserHasViewAccess($idSite);
        $promotion = self::promotion($idSite, static::VARIANT === 'full');

        return $this->renderTemplate('board', [
            'idSite' => $idSite,
            // stored HTML-escaped like every name typed in Matomo; the board renders text
            'siteName' => Common::unsanitizeInputValue(Site::getNameFor($idSite)),
            'currencySymbol' => Site::getCurrencySymbolFor($idSite),
            'activeMinutes' => LimitsProvider::get()->activeMinutes,
            'cityMinVisits' => LimitsProvider::get()->cityMinVisits,
            'variant' => static::VARIANT,
            'worldMap' => WorldMapFile::url(PIWIK_INCLUDE_PATH),
            'feedback' => static::VARIANT === 'full' ? self::feedback() : null,
            'promo' => $promotion[PromoCard::LINE],
            'promoCard' => $promotion[PromoCard::CARD],
        ]);
    }

    /**
     * The Feinwerk promotion kit (docs/promotions.md; Peter, 2026-11-19): the tip line under the feedback row of the
     * board's own page, the card in a dashboard widget (PromoCard::placeFor()), both with the message of the day of the
     * tip line rotation. Only for admins and super users, with every off switch (PromoLine::current()), never in a
     * Widgetize embed. A failing promotion never breaks the board.
     *
     * @return array{line: array<string, mixed>|null, card: array<string, mixed>|null}
     */
    private static function promotion(int $idSite, bool $fullBoard): array
    {
        $none = [PromoCard::LINE => null, PromoCard::CARD => null];
        try {
            $place = PromoCard::placeFor(
                $fullBoard,
                PromoLine::isEmbedded(Piwik::getModule(), Common::getRequestVar('disableLink', 0, 'int')),
                Common::getRequestVar('widget', 0, 'int') === 1
            );
            $line = $place === null ? null : PromoLine::current($idSite);
            if ($line === null) {
                return $none;
            }

            return $place === PromoCard::LINE
                ? [PromoCard::LINE => $line, PromoCard::CARD => null]
                : [PromoCard::LINE => null, PromoCard::CARD => PromoCard::fromLine($line, Piwik::translate('FeinwerkLiveDashboard_PromoCardMore'))];
        } catch (\Throwable $e) {
            return $none;
        }
    }

    /**
     * The thumbs under the board (Feinwerk feedback kit): only on the board itself, not in its dashboard widgets (they
     * stay quiet) nor in Widgetize embeds for other viewers. A failing question never breaks the board.
     */
    private static function feedback(): ?array
    {
        try {
            if (Common::getRequestVar('module', '', 'string') === 'Widgetize') {
                return null;
            }
            return [
                'enabled' => PluginFeedback::enabled(),
                'supportUrl' => PluginFeedback::supportUrl((string) StaticContainer::get(Translator::class)->getCurrentLanguage()),
                'version' => (string) PluginManager::getInstance()->getLoadedPlugin(PluginFeedback::PLUGIN)->getVersion(),
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }
}
