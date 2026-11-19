<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\tests\Unit\Snapshot;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\FeinwerkLiveDashboard\Snapshot\PayloadCodec;

/**
 * @group FeinwerkLiveDashboard
 */
class PayloadCodecTest extends TestCase
{
    public function test_roundTrip_keepsNestedValues(): void
    {
        $payload = [
            'activeNow' => 12,
            'revenue5' => 19.9,
            'pagesNow' => ['urls' => [[17, 'https://example.org/größe?<b>', 3]]],
            'countriesNow' => ['de' => 4, 'xx' => 1],
            'empty' => [],
        ];
        $this->assertSame($payload, PayloadCodec::decode(PayloadCodec::encode($payload)));
    }

    public function test_wholeFloats_stayFloats(): void
    {
        $this->assertSame(['revenue' => 20.0], PayloadCodec::decode(PayloadCodec::encode(['revenue' => 20.0])));
    }

    public function test_invalidUtf8_isSubstitutedNotLost(): void
    {
        $decoded = PayloadCodec::decode(PayloadCodec::encode(['title' => "Caf\xE9", 'count' => 2]));
        $this->assertSame(2, $decoded['count']);
        $this->assertSame("Caf\u{FFFD}", $decoded['title']);
    }

    public function test_garbage_decodesToNull(): void
    {
        $this->assertNull(PayloadCodec::decode('xyz'));
        $this->assertNull(PayloadCodec::decode(''));
        $this->assertNull(PayloadCodec::decode(substr(PayloadCodec::encode(['a' => str_repeat('x', 500)]), 0, 10)));
        $this->assertNull(PayloadCodec::decode(gzcompress('{"v":1,"p":{"a":1}}')), 'an older payload version is computed again');
        $this->assertNull(PayloadCodec::decode(gzcompress('{"v":2,"p":"not an object"}')));
        $this->assertNull(PayloadCodec::decode(gzcompress('[1,2]')));
    }
}
