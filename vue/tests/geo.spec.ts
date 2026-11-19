/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import {
  Box, bubbleRadius, inside, iso2, iso3, labelAt, parseWorldMap, pathsBox, placeLabels, project,
  shade, unitsPerPixel,
} from '../src/geo';
import {
  COUNTRIES5, COUNTRIES6, MATOMO5, MATOMO6,
} from './fixtures/worldMaps';

// a city and the country its point must fall into (south and west catch flipped signs)
const PLACES: Array<[string, number, number]> = [
  ['DEU', 13.40, 52.52], // Berlin
  ['AUS', 151.21, -33.87], // Sydney
  ['BRA', -46.63, -23.55], // Sao Paulo
  ['USA', -74.01, 40.71], // New York
  ['JPN', 139.69, 35.69], // Tokyo
  ['ZAF', 18.42, -33.92], // Cape Town
];

describe('project', () => {
  [['Matomo 5', MATOMO5], ['Matomo 6', MATOMO6]].forEach(([name, map]) => {
    const { view, boxes } = map as typeof MATOMO5;
    it(`puts cities into their country on the map of ${name}`, () => {
      PLACES.forEach(([iso, lon, lat]) => {
        const [x, y] = project(view, lon, lat);
        const [x1, y1, x2, y2] = boxes[iso];
        expect([iso, x >= x1 && x <= x2 && y >= y1 && y <= y2]).toEqual([iso, true]);
      });
    });
  });
});

describe('iso3', () => {
  it('maps Matomo country codes to the codes of both maps', () => {
    expect(iso3('de')).toBe('DEU');
    expect(iso3('US')).toBe('USA');
    expect(iso3('xk')).toBe('KOS');
    [MATOMO5, MATOMO6].forEach((map) => {
      ['DEU', 'AUS', 'BRA', 'USA', 'KOS', 'CHN', 'IND', 'GBR'].forEach((code) => {
        expect(map.isos).toContain(code);
      });
    });
  });

  it('gives nothing for Matomo special codes', () => {
    ['xx', 'eu', 'ap', 'a1', 'o1', '', 'deu'].forEach((code) => {
      expect(iso3(code)).toBe('');
    });
  });
});

describe('parseWorldMap', () => {
  const svg = `<?xml version="1.0" encoding="UTF-8"?><svg viewBox="0 0 1000 443"
    xmlns="http://www.w3.org/2000/svg"><metadata><views><view h="443" padding="0" w="1000">
    <proj id="naturalearth" lon0="0"/><bbox x="-5.89" y="13.28" w="1013.97" h="449.01"/></view>
    </views></metadata><g id="countries">
    <path d="M1,1L2,2Z" data-iso="DEU" data-name="Germany"/>
    <path d="M3,3L4,4Z" data-iso="FRA" data-name="France"/></g></svg>`;

  it('reads the view and the countries of the file', () => {
    const map = parseWorldMap(svg);
    expect(map?.view).toEqual(MATOMO6.view);
    expect(map?.countries).toEqual([
      { iso: 'DEU', name: 'Germany', d: 'M1,1L2,2Z' },
      { iso: 'FRA', name: 'France', d: 'M3,3L4,4Z' },
    ]);
  });

  it('gives nothing for a file it cannot use', () => {
    expect(parseWorldMap('')).toBeNull();
    expect(parseWorldMap('<html><body>Not found</body></html>')).toBeNull();
    expect(parseWorldMap(svg.replace(/<bbox[^>]*\/>/, ''))).toBeNull();
    expect(parseWorldMap(svg.replace(/<path[^>]*\/>/g, ''))).toBeNull();
  });
});

describe('shade and bubbles', () => {
  it('shades a country by its share of the busiest one, never invisible', () => {
    expect(shade(0, 10)).toBe(0);
    expect(shade(10, 10)).toBeCloseTo(0.9);
    expect(shade(1, 100)).toBeGreaterThan(0.15);
    expect(shade(25, 100)).toBeLessThan(shade(50, 100));
  });

  it('sizes bubbles by area in screen pixels, whatever the map is scaled to', () => {
    // 2 map units per pixel (a small map): the radius in map units doubles
    expect(bubbleRadius(100, 100, 1)).toBeCloseTo(12);
    expect(bubbleRadius(100, 100, 2)).toBeCloseTo(24);
    expect(bubbleRadius(1, 100, 1)).toBeGreaterThanOrEqual(3);
    expect(bubbleRadius(25, 100, 1)).toBeLessThan(bubbleRadius(100, 100, 1));
  });
});


describe('country maps', () => {
  // a place and the region it must fall into on Matomo's map of its country
  const REGIONS: Record<string, Array<[string, number, number]>> = {
    DEU: [['BE', 13.40, 52.52], ['BY', 11.58, 48.14], ['HH', 9.99, 53.55]],
    USA: [['NY', -75.5, 43.0], ['CA', -119.4, 36.8], ['TX', -99.0, 31.0]],
  };

  [['Matomo 5', COUNTRIES5], ['Matomo 6', COUNTRIES6]].forEach(([name, maps]) => {
    it(`puts places into their region on the country maps of ${name}`, () => {
      Object.entries(REGIONS).forEach(([country, places]) => {
        const { view, boxes } = (maps as typeof COUNTRIES5)[country];
        places.forEach(([region, lon, lat]) => {
          const [x, y] = project(view, lon, lat);
          const [x1, y1, x2, y2] = boxes[region];
          expect([country, region, x >= x1 && x <= x2 && y >= y1 && y <= y2])
            .toEqual([country, region, true]);
        });
      });
    });
  });

  it('reads the regions and the neighbours of a country map', () => {
    const svg = `<svg xmlns="http://www.w3.org/2000/svg"><metadata><views><view h="451"
      padding="0" w="450"><proj id="laea" lon0="10.38" lat0="51.1"/><bbox x="946.4" y="945.18"
      w="107.92" h="107.92"/></view></views></metadata>
      <g id="context"><path d="M0,0L1,1Z" data-iso="AUT"/></g>
      <g id="regions">
        <path d="M2,2L3,3Z" data-region="BY" data-iso3="DEU" data-name="Bayern" data-p="BY"/>
        <path d="M4,4L5,5Z" data-fips="GM16" data-iso3="DEU" data-name="Berlin" data-p="BE"/>
      </g></svg>`;
    const map = parseWorldMap(svg);
    expect(map?.view.proj).toEqual({ id: 'laea', lon0: 10.38, lat0: 51.1 });
    expect(map?.context).toEqual([{ iso: 'AUT', name: '', d: 'M0,0L1,1Z' }]);
    // Matomo 6 names the region code in data-region, Matomo 5 in data-p
    expect(map?.regions).toEqual([
      { code: 'BY', name: 'Bayern', d: 'M2,2L3,3Z' },
      { code: 'BE', name: 'Berlin', d: 'M4,4L5,5Z' },
    ]);
    expect(map?.countries).toEqual([]);
  });

  it('turns a map code back into Matomo\'s country code', () => {
    expect(iso2('DEU')).toBe('de');
    expect(iso2('KOS')).toBe('xk');
    expect(iso2('SOL')).toBe('');
  });
});

describe('country outline', () => {
  it('knows the box of a country\'s regions and what lies in it', () => {
    const box = pathsBox(['M10,20L30,20L30,40Z', 'M25,35L50,35L50,60Z']) as Box;
    expect(box).toEqual({
      x: 10, y: 20, w: 40, h: 40,
    });
    expect(inside(box, 30, 30)).toBe(true);
    expect(inside(box, 5, 30)).toBe(false);
    expect(inside(box, 30, 61)).toBe(false);
    expect(pathsBox([])).toBeNull();
  });
});

describe('drawn size', () => {
  it('measures map units per pixel by the side that limits the drawing', () => {
    // a square country map in a wide, height-limited panel: the height decides
    expect(unitsPerPixel(450, 451, 700, 460)).toBeCloseTo(451 / 460);
    // the wide world map in a narrow panel: the width decides
    expect(unitsPerPixel(1000, 443, 300, 400)).toBeCloseTo(1000 / 300);
    expect(unitsPerPixel(450, 451, 0, 0)).toBe(1);
  });
});

describe('city names', () => {
  it('stand right of the bubble, but left of it where they would leave the map', () => {
    // 13 px names at 1 unit per pixel: "Berlin" (6 letters) takes about 47 units
    expect(labelAt(100, 5, 'Berlin', 13, 1, 450)).toMatchObject({ x: 109, anchor: 'start' });
    expect(labelAt(420, 5, 'New York', 13, 1, 450)).toMatchObject({ x: 411, anchor: 'end' });
  });

  it('take the side with more room when the name fits on neither', () => {
    // "Frankfurt am Main" is about 133 units wide: 101 units free on the right, 31 on the left
    expect(labelAt(40, 5, 'Frankfurt am Main', 13, 1, 150)).toMatchObject({ x: 49, anchor: 'start' });
    expect(labelAt(110, 5, 'Frankfurt am Main', 13, 1, 150)).toMatchObject({ x: 101, anchor: 'end' });
  });

  it('give way to bigger cities instead of covering their names', () => {
    // biggest first: New York's name goes left (map edge), then Chicago's would run into it and is left out
    const placed = placeLabels([
      {
        key: 'ny', x: 420, y: 100, r: 8, name: 'New York',
      },
      {
        key: 'chi', x: 330, y: 104, r: 5, name: 'Chicago',
      },
      {
        key: 'mia', x: 400, y: 300, r: 4, name: 'Miami',
      },
    ], 13, 1, 450, 8);
    expect(placed.map((l) => l.key)).toEqual(['ny', 'mia']);
  });
});
