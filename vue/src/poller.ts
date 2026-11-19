/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { SnapshotAnswer } from './types';

/**
 * One poller per website and live view, shared by every board and widget on the page (concept 2.5
 * and 3.9): it asks when the server says (nextPollSeconds), sends the version of its last answer so
 * an unchanged snapshot comes back as "not modified", pauses while the page is hidden and backs off
 * after errors (10 s, doubling, at most 5 minutes). Everything that touches the browser comes in
 * through PollerDeps, so the logic is tested without one.
 */
export interface PollerDeps {
  fetch(since: string): Promise<SnapshotAnswer>;
  setTimer(fn: () => void, ms: number): unknown;
  clearTimer(handle: unknown): void;
  isHidden(): boolean;
  /** registers a listener for "the page became visible"; returns its remover */
  onVisible(fn: () => void): () => void;
  /** milliseconds, for receivedAt */
  clock(): number;
}

export interface PollerState {
  data: SnapshotAnswer | null;
  error: boolean;
  loading: boolean;
  /** clock() of the last answer, 0 before the first */
  receivedAt: number;
  /** clock() when the next request is due, 0 when none is scheduled */
  nextAt: number;
}

export interface Poller {
  subscribe(listener: (state: PollerState) => void): () => void;
  refresh(): void;
  active(): boolean;
}

export const FIRST_BACKOFF_MS = 10000;
export const MAX_BACKOFF_MS = 300000;

export function createPoller(deps: PollerDeps): Poller {
  const listeners = new Set<(state: PollerState) => void>();
  let state: PollerState = {
    data: null, error: false, loading: false, receivedAt: 0, nextAt: 0,
  };
  let timer: unknown = null;
  let inFlight = false;
  let failures = 0;
  let generation = 0;
  let removeVisible: (() => void) | null = null;
  let waitingForVisible = false;

  function emit(next: Partial<PollerState>) {
    state = { ...state, ...next };
    listeners.forEach((listener) => listener(state));
  }

  function clearTimer() {
    if (timer !== null) {
      deps.clearTimer(timer);
      timer = null;
    }
  }

  function poll() {
    if (inFlight || listeners.size === 0) {
      return;
    }
    if (deps.isHidden()) {
      waitingForVisible = true;
      return;
    }
    clearTimer();
    inFlight = true;
    const mine = generation;
    emit({ loading: true });
    deps.fetch(state.data?.version ?? '').then((answer) => {
      if (mine !== generation) {
        return;
      }
      inFlight = false;
      failures = 0;
      const data = answer.notModified && state.data
        ? { ...answer, payload: state.data.payload }
        : answer;
      const wait = Math.max(1, answer.nextPollSeconds) * 1000;
      // eslint-disable-next-line no-use-before-define
      schedule(wait);
      emit({
        data, error: false, loading: false, receivedAt: deps.clock(), nextAt: deps.clock() + wait,
      });
    }, () => {
      if (mine !== generation) {
        return;
      }
      inFlight = false;
      failures += 1;
      const wait = Math.min(MAX_BACKOFF_MS, FIRST_BACKOFF_MS * 2 ** (failures - 1));
      // eslint-disable-next-line no-use-before-define
      schedule(wait);
      emit({ error: true, loading: false, nextAt: deps.clock() + wait });
    });
  }

  function schedule(ms: number) {
    clearTimer();
    timer = deps.setTimer(() => {
      timer = null;
      poll();
    }, ms);
  }

  function start() {
    generation += 1;
    removeVisible = deps.onVisible(() => {
      if (waitingForVisible) {
        waitingForVisible = false;
        poll();
      }
    });
    poll();
  }

  function stop() {
    generation += 1;
    clearTimer();
    inFlight = false;
    waitingForVisible = false;
    if (removeVisible) {
      removeVisible();
      removeVisible = null;
    }
  }

  return {
    subscribe(listener) {
      listeners.add(listener);
      if (listeners.size === 1) {
        start();
      } else {
        listener(state);
      }
      return () => {
        if (listeners.delete(listener) && listeners.size === 0) {
          stop();
        }
      };
    },
    refresh() {
      if (!inFlight) {
        poll();
      }
    },
    active() {
      return listeners.size > 0;
    },
  };
}
