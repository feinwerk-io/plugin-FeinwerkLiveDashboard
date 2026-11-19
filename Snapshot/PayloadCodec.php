<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Snapshot;

/**
 * Snapshot payloads as stored: JSON in an envelope with a format version, gzip-compressed. Anything that does not decode
 * to the current version counts as missing, so a broken or older row is simply computed again.
 */
final class PayloadCodec
{
    /** 2 since the list windows of 3, 10, 30 and 60 minutes, 3 since the cities per window, 4 with the regions (all 2026-11-18): older rows are computed again */
    public const VERSION = 4;
    private const MAX_BYTES = 16777215; // MEDIUMBLOB

    public static function encode(array $payload): string
    {
        $json = json_encode(
            ['v' => self::VERSION, 'p' => $payload],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION | JSON_INVALID_UTF8_SUBSTITUTE
            | JSON_THROW_ON_ERROR
        );
        return gzcompress($json, 6);
    }

    public static function decode(string $blob): ?array
    {
        if ($blob === '') {
            return null;
        }
        $json = @gzuncompress($blob, self::MAX_BYTES);
        if (!is_string($json)) {
            return null;
        }
        $data = json_decode($json, true);
        if (!is_array($data) || ($data['v'] ?? null) !== self::VERSION || !is_array($data['p'] ?? null)) {
            return null;
        }
        return $data['p'];
    }
}
