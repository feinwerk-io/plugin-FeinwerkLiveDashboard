<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FeinwerkLiveDashboard\Format;

/**
 * Turns the language-neutral snapshot lists into rows {id, label, count} for one request. Keys that are not there (a
 * partial or empty tier) stay out; scalars and maps of numbers pass through. Every label is plain text: names Matomo's
 * tracker stored HTML-escaped (page URLs and titles, campaigns, events, cities) are decoded like
 * Common::unsanitizeInputValue(), so the UI renders all labels as text, never as HTML.
 */
final class PayloadFormatter
{
    /** fast tier: key => kind of list */
    private const FAST_LISTS = ['channelsNow' => 'referrer', 'countriesNow' => 'country'];

    /** slow tier: key => kind of list; each value is a map list window => list */
    private const WINDOW_LISTS = [
        'channels' => 'referrer',
        'countries' => 'country',
        'devices' => 'device',
        'campaigns' => 'name',
        'campaignSources' => 'name',
        'topPages' => 'page',
        'topTitles' => 'page',
        'downloads' => 'named',
        'outlinks' => 'named',
        'siteSearch' => 'named',
        'eventCategories' => 'named',
        'eventActions' => 'named',
        'goals' => 'goal',
    ];

    public static function format(array $tiers, Labeler $labeler): array
    {
        // the today tier's counting state (Snapshot\IncrementalCount) is for its next run, not for the board
        unset($tiers['today']['counted']);
        foreach (self::FAST_LISTS as $key => $kind) {
            if (isset($tiers['fast'][$key]) && is_array($tiers['fast'][$key])) {
                $tiers['fast'][$key] = self::rows($tiers['fast'][$key], $kind, $labeler);
            }
        }
        if (isset($tiers['fast']['pagesNow']) && is_array($tiers['fast']['pagesNow'])) {
            foreach ($tiers['fast']['pagesNow'] as $part => $list) {
                $tiers['fast']['pagesNow'][$part] = self::rows((array) $list, 'page', $labeler);
            }
        }
        if (isset($tiers['fast']['newestVisits']) && is_array($tiers['fast']['newestVisits'])) {
            $tiers['fast']['newestVisits'] = array_map(static fn (array $v): array => [
                'time' => $v[0],
                'country' => $v[1],
                'countryLabel' => $labeler->country((string) $v[1]),
                'channel' => $labeler->referrerType((int) $v[2]),
                'source' => self::text((string) $v[3]),
                'device' => $labeler->deviceType((int) $v[4]),
                'page' => (int) $v[5] === 0 || (string) $v[6] === '' ? $labeler->noPage() : self::text((string) $v[6]),
                'title' => (int) $v[5] === 0 || (string) $v[7] === '' ? $labeler->noPage() : self::text((string) $v[7]),
                'actions' => $v[8],
                'icons' => array_values(array_map('strval', (array) ($v[9] ?? []))),
                'duration' => (int) ($v[10] ?? 0),
            ], $tiers['fast']['newestVisits']);
        }
        foreach (self::WINDOW_LISTS as $key => $kind) {
            if (isset($tiers['slow'][$key]) && is_array($tiers['slow'][$key])) {
                foreach ($tiers['slow'][$key] as $window => $list) {
                    $tiers['slow'][$key][$window] = self::rows((array) $list, $kind, $labeler);
                }
            }
        }
        if (isset($tiers['slow']['regions']) && is_array($tiers['slow']['regions'])) {
            foreach ($tiers['slow']['regions'] as $window => $regions) {
                $tiers['slow']['regions'][$window] = array_map(static fn (array $r): array => [
                    'country' => (string) $r[0],
                    'region' => self::text((string) $r[1]),
                    'count' => $r[2],
                ], (array) $regions);
            }
        }
        if (isset($tiers['slow']['cities']) && is_array($tiers['slow']['cities'])) {
            foreach ($tiers['slow']['cities'] as $window => $cities) {
                $tiers['slow']['cities'][$window] = array_map(static fn (array $c): array => [
                    'country' => $c[0],
                    'countryLabel' => $labeler->country((string) $c[0]),
                    'region' => self::text((string) $c[1]),
                    'city' => self::text((string) $c[2]),
                    'lat' => $c[3],
                    'lon' => $c[4],
                    'count' => $c[5],
                ], (array) $cities);
            }
        }
        return $tiers;
    }

    /** A name as Matomo's tracker stored it (htmlspecialchars), as plain text. */
    private static function text(string $stored): string
    {
        return htmlspecialchars_decode($stored, ENT_QUOTES);
    }

    private static function rows(array $list, string $kind, Labeler $labeler): array
    {
        return array_map(static function (array $row) use ($kind, $labeler): array {
            if ($kind === 'goal') {
                // [idgoal, conversions, revenue]
                return ['id' => $row[0], 'label' => $labeler->goal((int) $row[0]), 'conversions' => $row[1], 'revenue' => $row[2]];
            }
            if ($kind === 'named' || $kind === 'page') {
                // [id, name, count]; in page lists id 0 is a visit whose last action was no page view
                $empty = $kind === 'page' && (int) $row[0] === 0 ? $labeler->noPage() : $labeler->unknown();
                return ['id' => $row[0], 'label' => (string) $row[1] === '' ? $empty : self::text((string) $row[1]), 'count' => $row[2]];
            }
            [$id, $count] = $row;
            switch ($kind) {
                case 'referrer':
                    $label = $labeler->referrerType((int) $id);
                    break;
                case 'country':
                    $label = $labeler->country((string) $id);
                    break;
                case 'device':
                    $label = $labeler->deviceType((int) $id);
                    break;
                default:
                    $label = (string) $id === '' ? $labeler->unknown() : self::text((string) $id);
            }
            return ['id' => $id, 'label' => $label, 'count' => $count];
        }, $list);
    }
}
