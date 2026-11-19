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
 * At least English, German, French, Spanish and Italian (Peter, 2026-11-19: "bitte alle Plugins mindestens in
 * französisch, spanisch, italienisch, deutsch, englisch ausliefern"): every language file holds the keys of en.json in
 * its order (alphabetical), with the same placeholders, without en or em dashes and without URLs.
 *
 * @group FeinwerkLiveDashboard
 */
class LanguageFilesTest extends TestCase
{
    private const LANGUAGES = ['en', 'de', 'fr', 'es', 'it'];

    /**
     * Placeholders a language may use although English does not: PromoLine passes Matomo's own word for the super user
     * (Installation_SuperUser), which the German sentence takes.
     */
    private const OPTIONAL_PLACEHOLDERS = ['PremiumAskSuperUser' => ['%s']];

    /** @return array<string, string> */
    private static function texts(string $language): array
    {
        $path = __DIR__ . '/../../lang/' . $language . '.json';
        self::assertFileExists($path, "lang/$language.json is missing");
        $data = json_decode((string) file_get_contents($path), true);
        self::assertIsArray($data, "lang/$language.json is no JSON object");
        self::assertSame(['FeinwerkLiveDashboard'], array_keys($data), "lang/$language.json: one section, the plugin's");

        return $data['FeinwerkLiveDashboard'];
    }

    /** @return string[] the placeholders of a text, sorted */
    private static function placeholders(string $text): array
    {
        preg_match_all('/%(?:\d+\$)?s/', $text, $m);
        $found = $m[0];
        sort($found);

        return $found;
    }

    public function test_englishKeys_areSortedAlphabetically(): void
    {
        $keys = array_keys(self::texts('en'));
        $sorted = $keys;
        sort($sorted, SORT_STRING);

        $this->assertSame($sorted, $keys);
    }

    public function test_everyLanguage_hasTheKeysOfEnglishInTheirOrder(): void
    {
        $english = array_keys(self::texts('en'));
        foreach (self::LANGUAGES as $language) {
            $this->assertSame($english, array_keys(self::texts($language)), $language);
        }
    }

    public function test_everyText_keepsThePlaceholdersOfEnglish(): void
    {
        $english = self::texts('en');
        foreach (self::LANGUAGES as $language) {
            foreach (self::texts($language) as $key => $text) {
                $found = self::placeholders($text);
                if ($found !== self::placeholders($english[$key]) && isset(self::OPTIONAL_PLACEHOLDERS[$key])) {
                    $this->assertSame(self::OPTIONAL_PLACEHOLDERS[$key], $found, "$language: $key");
                    continue;
                }
                $this->assertSame(self::placeholders($english[$key]), $found, "$language: $key");
                $this->assertNotSame('', trim($text), "$language: $key is empty");
            }
        }
    }

    public function test_noText_hasADashOrAUrl(): void
    {
        foreach (self::LANGUAGES as $language) {
            foreach (self::texts($language) as $key => $text) {
                $this->assertSame(0, preg_match('/[\x{2013}\x{2014}]/u', $text), "$language: $key has an en or em dash");
                $this->assertStringNotContainsString('http', $text, "$language: $key has a URL");
            }
        }
    }
}
