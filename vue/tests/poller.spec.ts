/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { createPoller, PollerDeps, PollerState } from '../src/poller';
import { SnapshotAnswer } from '../src/types';

function answer(version: string, extra: Partial<SnapshotAnswer> = {}): SnapshotAnswer {
  return {
    payload: { fast: { activeNow: 3 }, slow: {}, today: {} },
    ages: { fast: 1, slow: null, today: null },
    level: 0,
    version,
    nextPollSeconds: 10,
    notModified: false,
    delayed: false,
    stale: false,
    generatedAt: 1000,
    computedMs: {},
    ...extra,
  };
}

class Fake implements PollerDeps {
  calls: string[] = [];

  replies: Array<SnapshotAnswer | Error> = [];

  pending: Array<{ resolve: (a: SnapshotAnswer) => void; reject: (e: Error) => void }> = [];

  timers: Array<{ fn: () => void; ms: number; id: number }> = [];

  hidden = false;

  visibleListener: (() => void) | null = null;

  nextId = 1;

  now = 0;

  fetch(since: string): Promise<SnapshotAnswer> {
    this.calls.push(since);
    return new Promise((resolve, reject) => this.pending.push({ resolve, reject }));
  }

  setTimer(fn: () => void, ms: number): number {
    const id = this.nextId;
    this.nextId += 1;
    this.timers.push({ fn, ms, id });
    return id;
  }

  clearTimer(id: unknown): void {
    this.timers = this.timers.filter((t) => t.id !== id);
  }

  isHidden(): boolean {
    return this.hidden;
  }

  onVisible(fn: () => void): () => void {
    this.visibleListener = fn;
    return () => { this.visibleListener = null; };
  }

  clock(): number {
    return this.now;
  }

  /** answers the oldest open request */
  async reply(value: SnapshotAnswer | Error): Promise<void> {
    const request = this.pending.shift();
    if (!request) {
      throw new Error('no open request');
    }
    if (value instanceof Error) {
      request.reject(value);
    } else {
      request.resolve(value);
    }
    await Promise.resolve();
    await Promise.resolve();
  }

  /** runs the only scheduled timer */
  fire(): number {
    expect(this.timers).toHaveLength(1);
    const [timer] = this.timers;
    this.timers = [];
    this.now += timer.ms;
    timer.fn();
    return timer.ms;
  }
}

describe('poller', () => {
  it('makes one request for two subscribers and gives both the answer', async () => {
    const fake = new Fake();
    const poller = createPoller(fake);
    const a: PollerState[] = [];
    const b: PollerState[] = [];
    poller.subscribe((s) => a.push(s));
    poller.subscribe((s) => b.push(s));

    expect(fake.calls).toEqual(['']);
    await fake.reply(answer('v1'));

    expect(a[a.length - 1].data?.version).toBe('v1');
    expect(b[b.length - 1].data?.version).toBe('v1');
    expect(fake.calls).toEqual(['']);
  });

  it('waits the interval the server names and sends the last version', async () => {
    const fake = new Fake();
    createPoller(fake).subscribe(() => undefined);
    await fake.reply(answer('v1', { nextPollSeconds: 12 }));

    expect(fake.fire()).toBe(12000);
    expect(fake.calls).toEqual(['', 'v1']);
  });

  it('keeps the payload on a not-modified answer and takes its ages', async () => {
    const fake = new Fake();
    const states: PollerState[] = [];
    createPoller(fake).subscribe((s) => states.push(s));
    await fake.reply(answer('v1'));
    fake.fire();
    await fake.reply(answer('v1', {
      payload: {}, notModified: true, ages: { fast: 11, slow: 40, today: 100 },
    }));

    const last = states[states.length - 1];
    expect(last.data?.payload.fast).toEqual({ activeNow: 3 });
    expect(last.data?.ages.fast).toBe(11);
    expect(last.error).toBe(false);
  });

  it('backs off on errors up to 5 minutes and starts again after a success', async () => {
    const fake = new Fake();
    const states: PollerState[] = [];
    createPoller(fake).subscribe((s) => states.push(s));
    await fake.reply(answer('v1'));
    fake.fire();

    const waits: number[] = [];
    for (let i = 0; i < 7; i += 1) {
      // eslint-disable-next-line no-await-in-loop
      await fake.reply(new Error('offline'));
      expect(states[states.length - 1].error).toBe(true);
      expect(states[states.length - 1].nextAt).toBe(fake.now + fake.timers[0].ms);
      expect(states[states.length - 1].data?.version).toBe('v1');
      waits.push(fake.fire());
    }
    expect(waits).toEqual([10000, 20000, 40000, 80000, 160000, 300000, 300000]);

    await fake.reply(answer('v2', { nextPollSeconds: 10 }));
    expect(states[states.length - 1].error).toBe(false);
    expect(fake.fire()).toBe(10000);
  });

  it('stops after the last subscriber leaves and ignores a late answer', async () => {
    const fake = new Fake();
    const poller = createPoller(fake);
    const states: PollerState[] = [];
    const stopA = poller.subscribe((s) => states.push(s));
    const stopB = poller.subscribe(() => undefined);
    stopA();
    expect(poller.active()).toBe(true);
    stopB();
    expect(poller.active()).toBe(false);

    await fake.reply(answer('v1'));
    expect(fake.timers).toHaveLength(0);
    expect(states.filter((s) => s.data !== null)).toHaveLength(0);
  });

  it('pauses while the page is hidden and polls at once when it shows again', async () => {
    const fake = new Fake();
    createPoller(fake).subscribe(() => undefined);
    await fake.reply(answer('v1'));
    fake.hidden = true;
    fake.fire();

    expect(fake.calls).toEqual(['']);
    expect(fake.timers).toHaveLength(0);

    fake.hidden = false;
    fake.visibleListener?.();
    expect(fake.calls).toEqual(['', 'v1']);
  });

  it('refresh() polls at once, but never while a request is open', async () => {
    const fake = new Fake();
    const poller = createPoller(fake);
    poller.subscribe(() => undefined);
    poller.refresh();
    expect(fake.calls).toEqual(['']);

    await fake.reply(answer('v1'));
    poller.refresh();
    expect(fake.calls).toEqual(['', 'v1']);
    expect(fake.timers).toHaveLength(0);
  });

  it('gives a late subscriber the last state at once', async () => {
    const fake = new Fake();
    const poller = createPoller(fake);
    poller.subscribe(() => undefined);
    await fake.reply(answer('v1'));

    const late: PollerState[] = [];
    poller.subscribe((s) => late.push(s));
    expect(late[0].data?.version).toBe('v1');
    expect(fake.calls).toEqual(['']);
  });

  it('notes when the last answer arrived', async () => {
    const fake = new Fake();
    fake.now = 5000;
    const states: PollerState[] = [];
    createPoller(fake).subscribe((s) => states.push(s));
    await fake.reply(answer('v1'));
    expect(states[states.length - 1].receivedAt).toBe(5000);
  });
});
