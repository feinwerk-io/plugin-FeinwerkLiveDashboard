/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { AjaxHelper } from 'CoreHome';
import { createPoller, Poller } from './poller';
import { SnapshotAnswer } from './types';

declare global {
  interface Window {
    fwldPollers?: Record<string, Poller>;
  }
}

/**
 * The poller of a website and live view, shared by every board and widget of the page
 * (window.fwldPollers), so five live widgets on a dashboard still make one request per interval.
 */
export function sharedPoller(idSite: number, view = 0): Poller {
  const key = `${idSite}:${view}`;
  window.fwldPollers = window.fwldPollers || {};
  if (!window.fwldPollers[key]) {
    window.fwldPollers[key] = createPoller({
      fetch: (since) => AjaxHelper.post<SnapshotAnswer>(
        {
          module: 'API', method: 'FeinwerkLiveDashboard.getSnapshot', idSite, view,
        },
        { since },
        { createErrorNotification: false },
      ),
      setTimer: (fn, ms) => window.setTimeout(fn, ms),
      clearTimer: (handle) => window.clearTimeout(handle as number),
      isHidden: () => document.visibilityState === 'hidden',
      onVisible: (fn) => {
        const listener = () => {
          if (document.visibilityState !== 'hidden') {
            fn();
          }
        };
        document.addEventListener('visibilitychange', listener);
        return () => document.removeEventListener('visibilitychange', listener);
      },
      clock: () => Date.now(),
    });
  }
  return window.fwldPollers[key];
}
