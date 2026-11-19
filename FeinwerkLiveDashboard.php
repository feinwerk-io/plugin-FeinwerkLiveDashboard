<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard;

use Piwik\Option;
use Piwik\Plugins\FeinwerkLiveDashboard\Feedback\PluginFeedback;
use Piwik\Plugins\FeinwerkLiveDashboard\Promo\PromoPlacements;
use Piwik\Plugins\FeinwerkLiveDashboard\Snapshot\DbSnapshotStore;

class FeinwerkLiveDashboard extends \Piwik\Plugin
{
    public function registerEvents()
    {
        return [
            'Db.getTablesInstalled' => 'getTablesInstalled',
            'AssetManager.getJavaScriptFiles' => 'getJsFiles',
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'UsersManager.deleteUser' => 'deleteUserFeedback',
            // Feinwerk promotion kit (Promo/): the banner at the top of the admin home (Peter, 2026-11-19). No context boxes
            // (no Widget.filterWidgets), and not Template.adminHome: that renders below Matomo's Marketplace blocks.
            'Template.beforeContent' => 'addPromoBanner',
        ];
    }

    public function install()
    {
        (new DbSnapshotStore())->install();
    }

    public function uninstall()
    {
        (new DbSnapshotStore())->uninstall();
        Option::deleteLike(PluginFeedback::THROTTLE_OPTION_PREFIX . '%');
    }

    /** A deleted user's feedback throttles go with them. */
    public function deleteUserFeedback($login): void
    {
        Option::deleteLike(PluginFeedback::userOptionPattern((string) $login));
    }

    public function getTablesInstalled(&$allTablesInstalled): void
    {
        $allTablesInstalled[] = DbSnapshotStore::tableName();
    }

    public function getStylesheetFiles(&$files): void
    {
        $files[] = 'plugins/FeinwerkLiveDashboard/stylesheets/board.less';
        $files[] = 'plugins/FeinwerkLiveDashboard/stylesheets/feedback.less';
        // Feinwerk promotion kit, plain CSS (copies of the kit, identical in every Feinwerk plugin)
        $files[] = 'plugins/FeinwerkLiveDashboard/stylesheets/promo.css';
        $files[] = 'plugins/FeinwerkLiveDashboard/stylesheets/promocard.css';
        $files[] = 'plugins/FeinwerkLiveDashboard/stylesheets/feinwerklogo.css';
    }

    /** Feinwerk promotion kit: the tip line and the banner (promo.js), the card in dashboard widgets (promocard.js). */
    public function getJsFiles(&$files): void
    {
        $files[] = 'plugins/FeinwerkLiveDashboard/javascripts/promo.js';
        $files[] = 'plugins/FeinwerkLiveDashboard/javascripts/promocard.js';
    }

    /**
     * Template.beforeContent: the banner of the Feinwerk promotion kit at the top of Matomo's admin home only, for
     * admins and super users, once per page across all Feinwerk plugins (the connector's recommendation first).
     *
     * @param string $out
     */
    public function addPromoBanner(&$out, $type = null, $module = null, $action = null): void
    {
        PromoPlacements::beforeContent($out, $type, $module, $action);
    }

    /** The board uses the texts of lang/en.json. */
    public function getClientSideTranslationKeys(&$translationKeys): void
    {
        $translations = json_decode((string) file_get_contents(__DIR__ . '/lang/en.json'), true);
        foreach (array_keys($translations['FeinwerkLiveDashboard'] ?? []) as $key) {
            $translationKeys[] = 'FeinwerkLiveDashboard_' . $key;
        }
    }
}
