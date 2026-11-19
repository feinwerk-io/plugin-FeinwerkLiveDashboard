/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { parseWorldMap, WorldMap } from './geo';

type Get = (url: string) => Promise<string>;

// one request per page, however many boards and widgets show the map
const loads: Record<string, Promise<WorldMap | null>> = {};

function getText(url: string): Promise<string> {
  return fetch(url, { credentials: 'same-origin' }).then((r) => (r.ok ? r.text() : ''));
}

/** Matomo's world map, parsed; null without a URL, on a failed request or an unusable file. */
export function loadWorldMap(url: string, get: Get = getText): Promise<WorldMap | null> {
  if (!url) {
    return Promise.resolve(null);
  }
  if (!loads[url]) {
    loads[url] = get(url).then(parseWorldMap).catch(() => null);
  }
  return loads[url];
}
