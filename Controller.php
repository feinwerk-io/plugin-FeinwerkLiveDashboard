<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Nonce;
use Piwik\Piwik;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Plugins\FeinwerkLiveDashboard\ConnectorInstaller\ConnectorInstaller;
use Piwik\Url;
use Piwik\View;

/**
 * Only the Feinwerk kit's Connector installer pages; the live dashboard itself comes as widgets (Widgets/).
 */
class Controller extends \Piwik\Plugin\Controller
{
    /**
     * Kit: the Feinwerk Connector installer page (ConnectorInstaller/ConnectorInstaller.php). Super users only.
     */
    public function connectorInstaller()
    {
        Piwik::checkUserHasSuperUserAccess();
        $env = ConnectorInstaller::environment();
        if (ConnectorInstaller::decide($env) === ConnectorInstaller::HIDDEN) {
            // nothing to offer: the connector's own pages when it is active, else Matomo's admin home (switched off)
            Url::redirectToUrl($env['active'] ? ConnectorInstaller::CONNECTOR_URL : 'index.php?module=CoreAdminHome&action=home');
        }

        return $this->renderConnectorInstaller($env, null);
    }

    /**
     * Kit: the click. Matomo's password check first (it comes back here by GET with these parameters), then the nonce
     * (verified and discarded, as Matomo's own install action does), then install when the files are missing, and
     * activate. Success: on to the connector; any failure: back on the page with Matomo's reason.
     */
    public function connectorInstallerRun()
    {
        Piwik::checkUserHasSuperUserAccess();
        $env = ConnectorInstaller::environment();
        if ($env['active']) {
            Url::redirectToUrl(ConnectorInstaller::CONNECTOR_URL);
        }
        $state = ConnectorInstaller::decide($env);
        if ($state === ConnectorInstaller::HIDDEN) {
            Url::redirectToUrl('index.php?module=CoreAdminHome&action=home');
        }
        if ($state !== ConnectorInstaller::INSTALL && $state !== ConnectorInstaller::ACTIVATE) {
            return $this->renderConnectorInstaller($env, null);
        }

        $nonce = Common::getRequestVar('nonce', '', 'string');
        $verified = StaticContainer::get(\Piwik\Plugins\Login\PasswordVerifier::class)->requirePasswordVerifiedRecently([
            'module' => ConnectorInstaller::PLUGIN,
            'action' => 'connectorInstallerRun',
            'nonce' => $nonce,
        ]);
        if (!$verified) {
            // Matomo shows its password page instead
            return '';
        }

        $error = Nonce::verifyNonceWithErrorMessage(ConnectorInstaller::NONCE, $nonce);
        if ($error !== '') {
            // Matomo's own reason, from its translations (may hold its FAQ link)
            return $this->renderConnectorInstaller($env, ['ok' => false, 'message' => $error, 'html' => true]);
        }
        Nonce::discardNonce(ConnectorInstaller::NONCE);

        $result = ConnectorInstaller::run(
            $env['present'],
            static function (): void {
                (new \Piwik\Plugins\CorePluginsAdmin\PluginInstaller())->installOrUpdatePluginFromMarketplace(ConnectorInstaller::CONNECTOR);
            },
            static function (): void {
                PluginManager::getInstance()->activatePlugin(ConnectorInstaller::CONNECTOR);
            }
        );
        if ($result['ok']) {
            Url::redirectToUrl(ConnectorInstaller::CONNECTOR_URL);
        }

        return $this->renderConnectorInstaller(ConnectorInstaller::environment(), $result);
    }

    /**
     * @param array<string, bool> $env
     * @param array{ok: bool, message: string, html: bool}|null $result
     */
    private function renderConnectorInstaller(array $env, ?array $result): string
    {
        $view = new View('@FeinwerkLiveDashboard/connectorInstaller');
        // an admin page: no website needed (the menu entry is reached from pages without idSite too), and Matomo's
        // admin rule for frames (enable_framed_settings, X-Frame-Options sameorigin by default)
        $this->setBasicVariablesView($view);
        \Piwik\Plugin\ControllerAdmin::setBasicVariablesAdminView($view);
        $state = ConnectorInstaller::decide($env);
        $view->installerState = $state;
        $view->installerError = $result !== null && !$result['ok'] ? $result : null;
        $view->installerNonce = $state === ConnectorInstaller::INSTALL || $state === ConnectorInstaller::ACTIVATE
            ? Nonce::getNonce(ConnectorInstaller::NONCE)
            : '';
        $view->installerLinks = ConnectorInstaller::manualLinks();
        $view->installerWordPressUrl = ConnectorInstaller::wordpressUrl();
        $view->installerConnectorUrl = ConnectorInstaller::CONNECTOR_URL;
        $view->installerPhpFloor = ConnectorInstaller::PHP_FLOOR;

        return $view->render();
    }
}
