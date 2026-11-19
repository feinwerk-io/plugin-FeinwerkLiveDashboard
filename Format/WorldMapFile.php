<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Format;

/**
 * The live map draws on Matomo's own world map, a static file of the core plugin UserCountryMap (present in every
 * Matomo 5 and 6, also while that plugin is deactivated). Without the file the board shows no map, only the countries.
 */
final class WorldMapFile
{
    public const PATH = 'plugins/UserCountryMap/svg/world.svg';

    /** The URL relative to Matomo's root, '' when the file is missing. */
    public static function url(string $matomoRoot): string
    {
        return is_file(rtrim($matomoRoot, '/') . '/' . self::PATH) ? self::PATH : '';
    }
}
