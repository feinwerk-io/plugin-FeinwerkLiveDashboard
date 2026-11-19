/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { PollerState } from './poller';
import {
  ByWindow, ListWindow, Row, SnapshotAnswer,
} from './types';

export type PageLabel = 'url' | 'title';
export const LIST_WINDOWS: ListWindow[] = ['3', '10', '30', '60'];

/** The value of a list window, or the fallback while that window has none yet. */
export function inWindow<T>(map: ByWindow<T> | undefined, window: ListWindow, fallback: T): T {
  const value = map?.[window];
  return value === undefined ? fallback : value;
}

/** The minutes the sparkline shows: the chosen window, but at least the last 30 minutes. */
export function sparkFor(values: number[], window: ListWindow): number[] {
  const minutes = Math.max(30, Number(window));
  return values.slice(Math.max(0, values.length - minutes));
}

export function pagesNow(data: SnapshotAnswer | null, label: PageLabel): Row[] {
  const pages = data?.payload.fast?.pagesNow;
  if (!pages) {
    return [];
  }
  return (label === 'title' ? pages.titles : pages.urls) || [];
}

export function topPages(
  data: SnapshotAnswer | null,
  label: PageLabel,
  window: ListWindow,
): Row[] {
  const slow = data?.payload.slow;
  return inWindow(label === 'title' ? slow?.topTitles : slow?.topPages, window, []);
}

export interface BoardStatus {
  kind: 'loading' | 'live' | 'delayed' | 'stale' | 'offline';
  reduced: boolean;
}

/**
 * What the status line says: the connection first, then how fresh the data is, and whether detail
 * is reduced.
 */
export function boardStatus(state: PollerState): BoardStatus {
  const reduced = (state.data?.level ?? 0) > 0;
  if (state.error) {
    return { kind: 'offline', reduced };
  }
  if (!state.data) {
    return { kind: 'loading', reduced };
  }
  if (state.data.stale) {
    return { kind: 'stale', reduced };
  }
  return { kind: state.data.delayed ? 'delayed' : 'live', reduced };
}

export interface Bar {
  height: number;
  value: number;
  current: boolean;
}

/**
 * Sparkline bars in percent of the top of the scale (default: the busiest minute); the last one is
 * the running minute.
 */
export function bars(values: number[], top?: number): Bar[] {
  const max = top ?? Math.max(0, ...values);
  return values.map((value, i) => ({
    height: max > 0 ? Math.round((value / max) * 100) : 0,
    value,
    current: i === values.length - 1,
  }));
}

/**
 * Seconds since a visit's last action: the server's time of the answer plus the browser time since
 * it arrived.
 */
export function visitAge(
  visitTime: number,
  generatedAt: number,
  receivedAtMs: number,
  nowMs: number,
): number {
  return Math.max(0, Math.floor(generatedAt + (nowMs - receivedAtMs) / 1000 - visitTime));
}

/**
 * Columns of the live log in the panel grid: at most 3 (Peter, 2026-11-18), never more than the
 * grid has.
 */
export function logSpan(gridColumns: number): number {
  return Math.max(1, Math.min(3, gridColumns));
}

/**
 * The map's width in grid columns: one column stays free beside it for the countries list, and the
 * map takes at most 3 (its height is limited, a wider map only adds empty sides).
 */
export function mapSpan(gridColumns: number): number {
  return gridColumns >= 3 ? Math.min(3, gridColumns - 1) : Math.max(1, gridColumns);
}

/** The number of columns of a CSS grid from its computed grid-template-columns ("320px ..."). */
export function gridColumnCount(template: string): number {
  const parts = template.trim().split(/\s+/).filter((part) => /^[\d.]+px$/.test(part));
  return Math.max(1, parts.length);
}

/**
 * The Unix time of the minute each of `count` bars stands for; the last bar is the running minute.
 */
export function barMinutes(count: number, endUnix: number): number[] {
  const last = Math.floor(endUnix / 60) * 60;
  return Array.from({ length: count }, (_, i) => last - (count - 1 - i) * 60);
}

/** The top of the bar scale: the busiest minute rounded up to a readable number. */
export function scaleMax(max: number): number {
  if (max <= 10) {
    return Math.max(0, Math.ceil(max));
  }
  const power = 10 ** Math.floor(Math.log10(max));
  const step = [1, 1.2, 1.5, 2, 2.5, 3, 4, 5, 6, 8, 10].find((s) => s * power >= max) || 10;
  return Math.round(step * power);
}

export interface Sections {
  pulse: boolean;
  log: boolean;
  pagesNow: boolean;
  topPages: boolean;
  channels: boolean;
  campaigns: boolean;
  campaignSources: boolean;
  countries: boolean;
  devices: boolean;
  goals: boolean;
  map: boolean;
  more: boolean;
  windowSwitch: boolean;
  fullscreen: boolean;
}

const NONE: Sections = {
  pulse: false,
  log: false,
  pagesNow: false,
  topPages: false,
  channels: false,
  campaigns: false,
  campaignSources: false,
  countries: false,
  devices: false,
  goals: false,
  map: false,
  more: false,
  windowSwitch: false,
  fullscreen: false,
};

/**
 * What a board variant shows: the page everything; the dashboard widgets (Peter, 2026-11-18: "in 3
 * oder 4 verschiedenen Varianten") a part each. Unknown variants show everything.
 */
export function sectionsOf(variant: string): Sections {
  switch (variant) {
    case 'overview':
      return { ...NONE, pulse: true, windowSwitch: true };
    case 'pages':
      return {
        ...NONE, pagesNow: true, topPages: true, windowSwitch: true,
      };
    case 'sources':
      return {
        ...NONE,
        channels: true,
        campaigns: true,
        campaignSources: true,
        goals: true,
        windowSwitch: true,
      };
    case 'visits':
      return { ...NONE, log: true };
    case 'map':
      return {
        ...NONE, map: true, countries: true, windowSwitch: true,
      };
    default:
      return Object.fromEntries(Object.keys(NONE).map((key) => [key, true])) as unknown as Sections;
  }
}

/** The full live log table needs this width; a narrower panel gets the narrow layout. */
export const LOG_FULL_WIDTH = 560;

/** Narrow live log (phones, narrow dashboard columns); unmeasured (0) stays full. */
export function logNarrow(width: number): boolean {
  return width > 0 && width < LOG_FULL_WIDTH;
}

/** The narrow live log puts its icon places into two rows: the columns that takes. */
export function markColumns(places: number): number {
  return Math.ceil(Math.max(0, places) / 2);
}

/**
 * Whether the board sits in a widget (dashboard, Widgetize, the widget preview) rather than on its
 * own report page: Matomo wraps each widget outside report pages in an element of class "widget".
 */
export function inWidget(el: Element | null): boolean {
  return !!el && el.closest('.widget') !== null;
}

/** The feedback thumbs: only on the board's own page, never in a dashboard widget (kept quiet). */
export function feedbackShown(settings: unknown, inAWidget: boolean): boolean {
  return !!settings && !inAWidget;
}

/** The end of the minute bars: as the server counted them, else when the minutes were computed. */
export function sparkEnd(data: SnapshotAnswer | null): number {
  if (!data) {
    return 0;
  }
  if (typeof data.payload.slow?.end === 'number') {
    return data.payload.slow.end;
  }
  return data.generatedAt - (data.ages.slow ?? 0);
}

export function retryInSeconds(nextAt: number, now: number): number {
  return Math.max(0, Math.ceil((nextAt - now) / 1000));
}

interface OptionStorage {
  getItem(key: string): string | null;
  setItem(key: string, value: string): void;
}

/**
 * A viewer option (localStorage key fwld.NAME); a missing, unknown or unreadable value gives the
 * fallback.
 */
export function readOption<T extends string>(
  storage: OptionStorage,
  name: string,
  allowed: T[],
  fallback: T,
): T {
  try {
    const value = storage.getItem(`fwld.${name}`);
    return allowed.find((candidate) => candidate === value) ?? fallback;
  } catch (e) {
    return fallback;
  }
}

export function writeOption(storage: OptionStorage, name: string, value: string): void {
  try {
    storage.setItem(`fwld.${name}`, value);
  } catch (e) {
    // private windows and blocked storage: the option lasts for this page only
  }
}

/** What techLine() reads of the fast and slow tiers. */
export interface TechSource {
  ms?: number;
  k?: number;
  kVisits?: number;
  kActions?: number;
}

/**
 * The small technical info after the status (Peter, 2026-11-19, switchable in the plugin
 * settings): how long the last calculations of the newest numbers and of the lists took, and
 * whether the lists are estimated from a sample (the largest factor: 1 of k rows read). '' before
 * the first timed calculation.
 */
export function techLine(
  fast: TechSource,
  slow: TechSource,
  tr: (key: string, ...args: (string | number)[]) => string,
): string {
  if (fast.ms === undefined && slow.ms === undefined) {
    return '';
  }
  let times: string;
  if (fast.ms !== undefined && slow.ms !== undefined) {
    times = tr('TechTimes', fast.ms, slow.ms);
  } else {
    times = tr('TechTime', (fast.ms ?? slow.ms) as number);
  }
  const k = Math.max(1, fast.k || 1, slow.kVisits || 1, slow.kActions || 1);
  return `${times} · ${k > 1 ? tr('TechSample', k) : tr('TechSampleNone')}`;
}
