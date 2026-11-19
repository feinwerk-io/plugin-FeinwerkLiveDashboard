<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Unit\Format;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkLiveDashboard\Format\WorldMapFile;

/**
 * @group FeinwerkLiveDashboard
 */
class WorldMapFileTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/fwld-map-' . bin2hex(random_bytes(4));
        mkdir($this->root . '/plugins/UserCountryMap/svg', 0700, true);
    }

    protected function tearDown(): void
    {
        @unlink($this->root . '/plugins/UserCountryMap/svg/world.svg');
        foreach (['/plugins/UserCountryMap/svg', '/plugins/UserCountryMap', '/plugins', ''] as $dir) {
            @rmdir($this->root . $dir);
        }
    }

    public function test_url_pointsToMatomosWorldMap(): void
    {
        file_put_contents($this->root . '/plugins/UserCountryMap/svg/world.svg', '<svg/>');
        $this->assertSame('plugins/UserCountryMap/svg/world.svg', WorldMapFile::url($this->root));
    }

    public function test_withoutTheFile_noMap(): void
    {
        $this->assertSame('', WorldMapFile::url($this->root));
    }
}
