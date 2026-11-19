/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import {
  bars, boardStatus, inWindow, LIST_WINDOWS, readOption, retryInSeconds, sparkFor, topPages, pagesNow,
  barMinutes, feedbackShown, gridColumnCount, inWidget, logNarrow, logSpan, mapSpan, markColumns,
  scaleMax, sectionsOf, sparkEnd, techLine,
  visitAge, writeOption,
} from '../src/board';
import { PollerState } from '../src/poller';
import { SnapshotAnswer } from '../src/types';

const row = (id: number, label: string, count: number) => ({ id, label, count });

function answer(extra: Partial<SnapshotAnswer> = {}): SnapshotAnswer {
  return {
    payload: {
      fast: {
        pagesNow: { urls: [row(1, '/a', 3)], titles: [row(2, 'A', 3)] },
      },
      slow: {
        topPages: { 3: [row(1, '/a', 2)], 30: [row(1, '/a', 9), row(3, '/b', 4)] },
        topTitles: { 3: [row(2, 'A', 2)], 30: [row(2, 'A', 9)] },
        visits: { 3: 2, 10: 4, 30: 4, 60: 5 },
      },
      today: {},
    },
    ages: { fast: 2, slow: 20, today: 100 },
    level: 0,
    version: 'v',
    nextPollSeconds: 10,
    notModified: false,
    delayed: false,
    stale: false,
    generatedAt: 1000,
    computedMs: {},
    ...extra,
  };
}

function state(extra: Partial<PollerState> = {}): PollerState {
  return {
    data: answer(), error: false, loading: false, receivedAt: 0, nextAt: 0, ...extra,
  };
}

describe('board', () => {
  it('picks the page lists by label kind and window', () => {
    const data = answer();
    expect(pagesNow(data, 'url')).toEqual([row(1, '/a', 3)]);
    expect(pagesNow(data, 'title')).toEqual([row(2, 'A', 3)]);
    expect(topPages(data, 'url', '30')).toEqual([row(1, '/a', 9), row(3, '/b', 4)]);
    expect(topPages(data, 'title', '3')).toEqual([row(2, 'A', 2)]);
    expect(topPages(data, 'title', '60')).toEqual([]);
  });

  it('gives empty lists before the first data or for a tier still being computed', () => {
    expect(pagesNow(null, 'url')).toEqual([]);
    expect(topPages(answer({ payload: { fast: {}, slow: {}, today: {} } }), 'url', '3')).toEqual([]);
  });

  it('tells the board status: loading, live, delayed, stale, busy and offline', () => {
    expect(boardStatus(state({ data: null, loading: true })).kind).toBe('loading');
    expect(boardStatus(state()).kind).toBe('live');
    expect(boardStatus(state({ data: answer({ delayed: true }) })).kind).toBe('delayed');
    expect(boardStatus(state({ data: answer({ delayed: true, stale: true }) })).kind).toBe('stale');
    expect(boardStatus(state({ error: true })).kind).toBe('offline');
    expect(boardStatus(state({ error: true, data: null })).kind).toBe('offline');
    expect(boardStatus(state({ data: answer({ level: 2 }) }))).toEqual({ kind: 'live', reduced: true });
    expect(boardStatus(state()).reduced).toBe(false);
  });

  it('scales the sparkline bars to the busiest minute and marks the current one', () => {
    expect(bars([0, 5, 10])).toEqual([
      { height: 0, value: 0, current: false },
      { height: 50, value: 5, current: false },
      { height: 100, value: 10, current: true },
    ]);
    expect(bars([])).toEqual([]);
    expect(bars([0, 0]).map((b) => b.height)).toEqual([0, 0]);
  });

  it('offers 3, 10, 30 and 60 minutes and reads a window of a map', () => {
    expect(LIST_WINDOWS).toEqual(['3', '10', '30', '60']);
    expect(inWindow({ 3: 2, 60: 5 }, '60', 0)).toBe(5);
    expect(inWindow({ 3: 2 }, '10', 0)).toBe(0);
    expect(inWindow(undefined, '10', [])).toEqual([]);
  });

  it('shows the sparkline for the chosen window, but at least the last 30 minutes', () => {
    const sixty = Array.from({ length: 60 }, (_, i) => i);
    expect(sparkFor(sixty, '3')).toEqual(sixty.slice(30));
    expect(sparkFor(sixty, '30')).toEqual(sixty.slice(30));
    expect(sparkFor(sixty, '60')).toEqual(sixty);
    expect(sparkFor([1, 2], '60')).toEqual([1, 2]);
  });

  it('ages a visit of the live log with the clock since the answer arrived', () => {
    // answer generated at 1000 (server), received at 50000 ms (browser); now 53500 ms: 3.5 s later
    expect(visitAge(990, 1000, 50000, 53500)).toBe(13);
    expect(visitAge(1000, 1000, 50000, 50000)).toBe(0);
    expect(visitAge(1005, 1000, 50000, 50000)).toBe(0);
  });

  it('lets the live log take at most 3 columns of the grid, never more than there are', () => {
    expect(logSpan(5)).toBe(3);
    expect(logSpan(3)).toBe(3);
    expect(logSpan(2)).toBe(2);
    expect(logSpan(1)).toBe(1);
    expect(logSpan(0)).toBe(1);
    expect(gridColumnCount('320px 320px 320px 320px')).toBe(4);
    expect(gridColumnCount('none')).toBe(1);
    expect(gridColumnCount('')).toBe(1);
  });

  it('leaves one column beside the map for the countries, at most 3 for the map', () => {
    expect(mapSpan(5)).toBe(3);
    expect(mapSpan(4)).toBe(3);
    expect(mapSpan(3)).toBe(2);
    expect(mapSpan(2)).toBe(2);
    expect(mapSpan(1)).toBe(1);
    expect(mapSpan(0)).toBe(1);
  });

  it('gives each bar the minute it stands for, the last one the running minute', () => {
    // 12:00:40 UTC: the bars are 11:58, 11:59 and 12:00
    const end = Date.UTC(2026, 10, 18, 12, 0, 40) / 1000;
    expect(barMinutes(3, end)).toEqual([end - 40 - 120, end - 40 - 60, end - 40]);
    expect(barMinutes(0, end)).toEqual([]);
  });

  it('rounds the top of the scale up to a readable number', () => {
    expect(scaleMax(0)).toBe(0);
    expect(scaleMax(7)).toBe(7);
    expect(scaleMax(13)).toBe(15);
    expect(scaleMax(42)).toBe(50);
    expect(scaleMax(118)).toBe(120);
    expect(scaleMax(1234)).toBe(1500);
  });

  it('counts down to the next try after an error', () => {
    expect(retryInSeconds(12000, 5000)).toBe(7);
    expect(retryInSeconds(12000, 11001)).toBe(1);
    expect(retryInSeconds(12000, 20000)).toBe(0);
  });

  it('keeps viewer options in localStorage and survives a storage that throws', () => {
    const storage = new Map<string, string>();
    const ok = {
      getItem: (k: string) => storage.get(k) ?? null,
      setItem: (k: string, v: string) => { storage.set(k, v); },
    };
    writeOption(ok, 'pageLabel', 'title');
    expect(storage.get('fwld.pageLabel')).toBe('title');
    expect(readOption(ok, 'pageLabel', ['url', 'title'], 'url')).toBe('title');
    expect(readOption(ok, 'window', LIST_WINDOWS, '30')).toBe('30');
    storage.set('fwld.window', 'nonsense');
    expect(readOption(ok, 'window', LIST_WINDOWS, '30')).toBe('30');
    // the old 5 minute window is gone
    storage.set('fwld.window', '5');
    expect(readOption(ok, 'window', LIST_WINDOWS, '30')).toBe('30');

    const broken = {
      getItem: (): string | null => { throw new Error('blocked'); },
      setItem: (): void => { throw new Error('blocked'); },
    };
    expect(readOption(broken, 'window', LIST_WINDOWS, '30')).toBe('30');
    expect(() => writeOption(broken, 'window', '5')).not.toThrow();
  });
});

describe('variants', () => {
  it('shows everything on the page', () => {
    const full = sectionsOf('full');
    expect(Object.values(full).every(Boolean)).toBe(true);
  });

  it('gives each dashboard widget a sensible part of the board', () => {
    expect(sectionsOf('overview')).toMatchObject({
      pulse: true, log: false, pagesNow: false, channels: false, windowSwitch: true, fullscreen: false,
    });
    expect(sectionsOf('pages')).toMatchObject({
      pulse: false, pagesNow: true, topPages: true, channels: false, windowSwitch: true,
    });
    expect(sectionsOf('sources')).toMatchObject({
      pulse: false, channels: true, campaigns: true, campaignSources: true, goals: true, pagesNow: false,
    });
    expect(sectionsOf('visits')).toMatchObject({
      pulse: false, log: true, pagesNow: false, windowSwitch: false,
    });
    expect(sectionsOf('map')).toMatchObject({
      map: true, countries: true, windowSwitch: true, pulse: false, log: false, channels: false,
    });
    expect(sectionsOf('sources').map).toBe(false);
    expect(sectionsOf('nonsense')).toEqual(sectionsOf('full'));
  });
});

describe('inWidget', () => {
  it('knows a board inside a dashboard widget, Widgetize or the widget preview', () => {
    const widget = document.createElement('div');
    widget.className = 'widget';
    const content = document.createElement('div');
    const board = document.createElement('div');
    widget.appendChild(content);
    content.appendChild(board);
    expect(inWidget(board)).toBe(true);
  });

  it('knows the board on its own report page', () => {
    const page = document.createElement('div');
    page.className = 'matomo-widget';
    const board = document.createElement('div');
    page.appendChild(board);
    expect(inWidget(board)).toBe(false);
    expect(inWidget(null)).toBe(false);
  });
});

describe('live log layout', () => {
  it('goes narrow when the panel is smaller than the full table, not before it is measured', () => {
    expect(logNarrow(278)).toBe(true);
    expect(logNarrow(559)).toBe(true);
    expect(logNarrow(560)).toBe(false);
    expect(logNarrow(1200)).toBe(false);
    expect(logNarrow(0)).toBe(false);
  });

  it('puts the icon places of the narrow log into two rows', () => {
    expect(markColumns(4)).toBe(2);
    expect(markColumns(2)).toBe(1);
    expect(markColumns(5)).toBe(3);
    expect(markColumns(0)).toBe(0);
  });
});

describe('feedback and spark labels', () => {
  it('asks for feedback only on the board\'s own page, never in a dashboard widget', () => {
    const settings = { enabled: true, supportUrl: 'x', version: '5' };
    expect(feedbackShown(settings, false)).toBe(true);
    expect(feedbackShown(settings, true)).toBe(false);
    expect(feedbackShown(null, false)).toBe(false);
  });

  it('labels the minute bars from the end of the minutes the server counted', () => {
    const data = answer({ generatedAt: 1000, ages: { fast: 2, slow: 40, today: 100 } });
    expect(sparkEnd(data)).toBe(960);
    // the server ends the minutes earlier while the reader database is behind
    const behind = { ...data, payload: { ...data.payload, slow: { ...data.payload.slow, end: 900 } } };
    expect(sparkEnd(behind)).toBe(900);
    expect(sparkEnd(null)).toBe(0);
  });
});

describe('technical info', () => {
  const tr = (key: string, ...args: (string | number)[]) => `${key}(${args.join(',')})`;

  it('names the calculation times and says when no sample is used', () => {
    expect(techLine({ ms: 48, k: 1 }, { ms: 845, kVisits: 1, kActions: 1 }, tr))
      .toBe('TechTimes(48,845) · TechSampleNone()');
  });

  it('names the largest sampling factor', () => {
    expect(techLine({ ms: 120, k: 1 }, { ms: 910, kVisits: 2, kActions: 4 }, tr))
      .toBe('TechTimes(120,910) · TechSample(4)');
  });

  it('is empty before the first calculation was timed', () => {
    expect(techLine({}, {}, tr)).toBe('');
    expect(techLine({ ms: 48 }, {}, tr)).toBe('TechTime(48) · TechSampleNone()');
  });
});
