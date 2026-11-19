/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { loadWorldMap } from '../src/worldMap';

const SVG = `<svg xmlns="http://www.w3.org/2000/svg"><metadata><views><view h="443" padding="0"
  w="1000"><proj id="naturalearth" lon0="0"/><bbox x="-5.89" y="13.28" w="1013.97" h="449.01"/>
  </view></views></metadata><g id="countries"><path d="M1,1L2,2Z" data-iso="DEU"
  data-name="Germany"/></g></svg>`;

describe('loadWorldMap', () => {
  it('fetches the file once for every board and widget of the page', async () => {
    const get = jest.fn(() => Promise.resolve(SVG));
    const [a, b] = await Promise.all([loadWorldMap('a.svg', get), loadWorldMap('a.svg', get)]);
    expect(get).toHaveBeenCalledTimes(1);
    expect(a?.countries).toHaveLength(1);
    expect(b).toBe(a);
  });

  it('gives no map without a file, on a failed request or on a file it cannot read', async () => {
    const get = jest.fn(() => Promise.resolve(SVG));
    expect(await loadWorldMap('', get)).toBeNull();
    expect(get).not.toHaveBeenCalled();
    expect(await loadWorldMap('b.svg', () => Promise.reject(new Error('404')))).toBeNull();
    expect(await loadWorldMap('c.svg', () => Promise.resolve('<html>Not found</html>'))).toBeNull();
  });
});
