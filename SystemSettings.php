<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard;

use Piwik\Piwik;
use Piwik\Settings\FieldConfig;
use Piwik\Settings\Plugin\SystemSetting;

/**
 * The plugin's one setting in Matomo's General settings (the limits stay config.ini.php options, README). Peter,
 * 2026-11-19: "als kleine unauffällige info wäre es noch super, wenn das plugin die query latency anzeigen könnte und
 * ob gesampelt wird oder nicht. Diese Info kann man in den Plugin-Settings an und ausschalten".
 */
class SystemSettings extends \Piwik\Settings\Plugin\SystemSettings
{
    /** the heading: the name with spaces, the same in every language, never translated (house rule 2026-10-20) */
    public const TITLE = 'Feinwerk Live Dashboard';

    /** @var SystemSetting */
    public $showTechInfo;

    protected function init()
    {
        $this->title = self::TITLE;

        $this->showTechInfo = $this->makeSetting(
            'showTechInfo',
            true,
            FieldConfig::TYPE_BOOL,
            function (FieldConfig $field) {
                $field->title = Piwik::translate('FeinwerkLiveDashboard_SettingTechInfo');
                $field->description = Piwik::translate('FeinwerkLiveDashboard_SettingTechInfoHelp');
                $field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;
            }
        );
    }

    /** The switch for the board; on any doubt the default (on). Never throws. */
    public static function techInfoShown(): bool
    {
        try {
            return (bool) (new self())->showTechInfo->getValue();
        } catch (\Throwable $e) {
            return true;
        }
    }
}
