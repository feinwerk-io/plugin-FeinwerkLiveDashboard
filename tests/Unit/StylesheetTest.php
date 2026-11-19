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
 * The LESS compiler of Matomo 5 (less.php 3) computes "a / b" in a value: "grid-column: 1 / -1" became
 * "grid-column: -1" and broke the board's layout on Matomo 5.0 to 5.14 only (found 2026-11-18 in a 5.0.0 sandbox).
 * Values with a slash must be escaped (~"1 / -1").
 *
 * @group FeinwerkLiveDashboard
 */
class StylesheetTest extends TestCase
{
    public function test_slashValues_areEscapedForLess3(): void
    {
        $files = glob(dirname(__DIR__, 2) . '/stylesheets/*.less') ?: [];
        $this->assertNotEmpty($files);
        foreach ($files as $file) {
            foreach (file($file) as $number => $line) {
                $code = preg_replace('~//.*$~', '', $line);
                $code = preg_replace('/~"[^"]*"/', '', (string) $code);
                if (preg_match('/^\s*[a-z-]+\s*:[^;]*\S\s*\/\s*\S/', (string) $code)) {
                    $this->fail(basename($file) . ':' . ($number + 1) . ' has an unescaped "/" in a value: ' . trim($line));
                }
            }
        }
        $this->addToAssertionCount(1);
    }

    /**
     * The live log's table scrolls inside its panel on narrow screens. Its column labels for screen readers are
     * absolutely positioned; a scroll container clips such children only when it is positioned itself. Without that
     * the labels of the right-hand columns stuck out of the panel and made the whole page wider than a phone screen
     * (found 2026-11-18 at 390 px: page 591 px wide, Matomo's own real-time page 390 px).
     */
    public function test_scrollContainers_clipTheirAbsolutelyPositionedChildren(): void
    {
        $less = (string) file_get_contents(dirname(__DIR__, 2) . '/stylesheets/board.less');
        $this->assertSame(1, preg_match('/^\.fwld-log__scroll \{([^}]*)\}/m', $less, $m), 'the rule .fwld-log__scroll exists');
        $this->assertMatchesPattern('/overflow-x:\s*auto;/', $m[1]);
        $this->assertMatchesPattern('/position:\s*relative;/', $m[1]);
    }

    /** assertMatchesRegularExpression is PHPUnit 9.1+; Matomo 5 runs this file with PHPUnit 8.5 */
    private function assertMatchesPattern(string $pattern, string $subject): void
    {
        $this->assertSame(1, preg_match($pattern, $subject), $pattern . ' in: ' . trim($subject));
    }
}
