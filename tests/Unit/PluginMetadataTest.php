<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * The plugin's metadata follows the Feinwerk rules. Loads no Matomo code and never pins the plugin version: fw-release
 * sets it from CHANGELOG.md when it pushes a release.
 *
 * @group FeinwerkLiveDashboard
 */
class PluginMetadataTest extends TestCase
{
    private const PLUGIN = 'FeinwerkLiveDashboard';

    private static function path(string $file): string
    {
        return dirname(__DIR__, 2) . '/' . $file;
    }

    private static function json(string $file): array
    {
        $data = json_decode((string) file_get_contents(self::path($file)), true);
        self::assertIsArray($data, "$file is no JSON object");
        return $data;
    }

    public function test_pluginJson_hasTheFeinwerkMetadata(): void
    {
        $plugin = self::json('plugin.json');

        $this->assertSame(self::PLUGIN, $plugin['name']);
        $this->assertSame('GPL v3+', $plugin['license']);
        // listed on the Matomo Marketplace: the plugin website link goes to the store page (marketplace-listings.md)
        $this->assertSame(
            'https://plugins.feinwerk.io/en/plugins/' . self::PLUGIN . '?mtm_campaign=matomo-marketplace&mtm_source=' . self::PLUGIN,
            $plugin['homepage']
        );
        $this->assertSame(
            [['name' => 'Peter Boehlke, Feinwerk.io', 'email' => 'support@feinwerk.io', 'homepage' => 'https://feinwerk.io']],
            $plugin['authors']
        );
        // one build for Matomo 5 and 6 on main (PluginLead on Peter's start, 2026-11-18), like Sentinel and the connector
        $this->assertSame(['matomo' => '>=5.0.0,<7.0.0-b1', 'php' => '>=8.1.0'], $plugin['require']);
    }

    public function test_version_isFiveYZAndFollowsTheChangelog(): void
    {
        $plugin = self::json('plugin.json');
        // a one-build plugin carries 5.y.z, Matomo 5 being its base (docs/release-lines.md)
        // preg_match, not assertMatchesRegularExpression: tests/Unit also runs in Matomo's PluginTests suite, which is
        // PHPUnit 8.5 on Matomo 5
        $this->assertSame(1, preg_match('/^5\.\d+\.\d+(-[0-9A-Za-z.]+)?$/', $plugin['version']), $plugin['version']);

        // once there is a version heading in CHANGELOG.md, the newest one is the version of plugin.json
        $changelog = (string) file_get_contents(self::path('CHANGELOG.md'));
        if (preg_match('/^## v?(\d+\.\d+\.\d+(?:-[0-9A-Za-z.]+)?)(?: \(\d{4}-\d{2}-\d{2}\))?\s*$/m', $changelog, $heading)) {
            $this->assertSame($heading[1], $plugin['version']);
        }
    }

    public function test_translations_haveTheSameKeysInEnglishAndGerman(): void
    {
        $en = self::json('lang/en.json')[self::PLUGIN];
        $de = self::json('lang/de.json')[self::PLUGIN];

        $this->assertSame([], array_values(array_diff(array_keys($en), array_keys($de))), 'missing in de.json');
        $this->assertSame([], array_values(array_diff(array_keys($de), array_keys($en))), 'missing in en.json');
        $this->assertNotSame('', $en['PluginName']);
    }

    public function test_licenseFileAndPhpHeaders_matchTheLicense(): void
    {
        // LICENSE.txt instead of LICENSE once the plugin has a License/ directory (case-insensitive file systems)
        $file = is_file(self::path('LICENSE')) ? 'LICENSE' : 'LICENSE.txt';
        $license = (string) file_get_contents(self::path($file));
        foreach (['GNU GENERAL PUBLIC LICENSE', 'Version 3'] as $mark) {
            $this->assertStringContainsString($mark, $license, $file);
        }

        $root = dirname(__DIR__, 2);
        $dirs = new \RecursiveCallbackFilterIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS),
            static function (\SplFileInfo $file): bool {
                return !($file->isDir() && in_array($file->getFilename(), ['vendor', 'node_modules', '.git'], true));
            }
        );
        $checked = 0;
        foreach (new \RecursiveIteratorIterator($dirs) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $head = (string) file_get_contents($file->getPathname(), false, null, 0, 2048);
            $this->assertStringContainsString('@license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later', $head, substr($file->getPathname(), strlen($root) + 1));
            $checked++;
        }
        $this->assertGreaterThan(0, $checked);
    }
}
