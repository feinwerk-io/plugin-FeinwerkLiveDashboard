/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { flushPromises, mount } from '@vue/test-utils';
import LiveMap from '../src/LiveMap.vue';
import { project, WorldMap } from '../src/geo';
import { loadWorldMap } from '../src/worldMap';
import { COUNTRIES6, MATOMO6 } from './fixtures/worldMaps';

jest.mock('../src/worldMap', () => ({ loadWorldMap: jest.fn() }));

const WORLD: WorldMap = {
  view: MATOMO6.view,
  countries: [
    { iso: 'DEU', name: 'Germany', d: 'M512,82L534,82L534,105Z' },
    { iso: 'FRA', name: 'France', d: 'M490,95L510,95L510,115Z' },
    { iso: 'SOL', name: 'Somaliland', d: 'M600,250L610,250L610,260Z' },
  ],
  context: [],
  regions: [],
};

const GERMANY: WorldMap = {
  view: COUNTRIES6.DEU.view,
  countries: [],
  context: [{ iso: 'AUT', name: '', d: 'M300,420L350,420L350,450Z' }],
  regions: [
    { code: 'BY', name: 'Bayern', d: 'M177,257L340,257L340,426Z' },
    { code: 'BE', name: 'Berlin', d: 'M309,146L329,146L329,163Z' },
    { code: 'HH', name: 'Hamburg', d: 'M204,94L222,94L222,110Z' },
  ],
};

// Matomo 6: a small country is one area coded __ALL__
const LUXEMBOURG: WorldMap = {
  view: COUNTRIES6.DEU.view,
  countries: [],
  context: [],
  regions: [{ code: '__ALL__', name: 'Luxembourg', d: 'M10,10L20,10L20,20Z' }],
};

// Matomo 5: regions known by their old FIPS codes only, so none matches a stored region
const CZECHIA: WorldMap = {
  view: COUNTRIES6.DEU.view,
  countries: [],
  context: [],
  regions: [
    { code: '', name: 'Praha', d: 'M10,10L20,10L20,20Z' },
    { code: '', name: 'Brno', d: 'M30,30L40,30L40,40Z' },
  ],
};

const maps: Record<string, WorldMap | null> = {
  'plugins/UserCountryMap/svg/world.svg': WORLD,
  'plugins/UserCountryMap/svg/DEU.svg': GERMANY,
  'plugins/UserCountryMap/svg/FRA.svg': null,
  'plugins/UserCountryMap/svg/LUX.svg': LUXEMBOURG,
  'plugins/UserCountryMap/svg/CZE.svg': CZECHIA,
};

function map(country: string) {
  (loadWorldMap as jest.Mock).mockImplementation((url: string) => Promise.resolve(maps[url]));
  return mount(LiveMap, {
    props: {
      title: 'Live map',
      windowLabel: 'last 30 minutes',
      url: 'plugins/UserCountryMap/svg/world.svg',
      country,
      countries: [
        {
          code: 'de', label: 'Germany', count: 10, title: 'Germany: 10 visits',
        },
        {
          code: 'fr', label: 'France', count: 1, title: 'France: 1 visit',
        },
        {
          code: 'xx', label: 'Unknown', count: 4, title: 'Unknown: 4 visits',
        },
        {
          code: 'lu', label: 'Luxembourg', count: 3, title: 'Luxembourg: 3 visits',
        },
        {
          code: 'cz', label: 'Czechia', count: 5, title: 'Czechia: 5 visits',
        },
      ],
      regions: [
        { country: 'cz', region: '10', count: 5 },
        { country: 'de', region: 'BY', count: 6 },
        { country: 'de', region: 'BE', count: 2 },
        { country: 'fr', region: 'IDF', count: 1 },
      ],
      cities: [
        {
          key: 'de|BY|Munich',
          country: 'de',
          name: 'Munich',
          lat: 48.14,
          lon: 11.58,
          count: 5,
          title: 'Munich, Germany: 5 visits',
        },
        {
          key: 'de|IDF|Paris',
          country: 'de',
          name: 'Paris',
          lat: 48.86,
          lon: 2.35,
          count: 9,
          title: 'Paris, Germany: 9 visits',
        },
        {
          key: 'fr|IDF|Paris',
          country: 'fr',
          name: 'Paris',
          lat: 48.86,
          lon: 2.35,
          count: 1,
          title: 'Paris, France: 1 visit',
        },
        {
          key: 'de||', country: 'de', name: '', lat: NaN, lon: 0, count: 1, title: 'no place',
        },
      ],
      texts: { world: 'World map', noMap: 'No map of this country.', noRegions: 'No regions here.' },
      visits: (label: string, count: number) => `${label}: ${count} visits`,
    },
  });
}

describe('LiveMap', () => {
  it('shows a country centred with its regions shaded by their visits', async () => {
    const wrapper = map('de');
    await flushPromises();

    expect(wrapper.find('svg').attributes('viewBox')).toBe('0 0 450 451');
    const regions = wrapper.findAll('path.fwld-map__land');
    expect(regions).toHaveLength(3);
    expect(regions[0].classes()).toContain('fwld-map__land--visited');
    expect(regions[0].find('title').text()).toBe('Bayern: 6 visits');
    const opacity = (i: number) => Number(regions[i].attributes('style')?.match(/: ([\d.]+)/)?.[1]);
    expect(opacity(0)).toBeGreaterThan(opacity(1));
    expect(regions[2].classes()).not.toContain('fwld-map__land--visited');
    // the neighbours for orientation, without visits
    expect(wrapper.findAll('path.fwld-map__context')).toHaveLength(1);
  });

  it('puts the country\'s cities where they are, with their names', async () => {
    const wrapper = map('de');
    await flushPromises();

    // Paris is stored for Germany here (a wrong location): outside the country, so no bubble
    const bubbles = wrapper.findAll('circle');
    expect(bubbles).toHaveLength(1);
    const [x, y] = project(COUNTRIES6.DEU.view, 11.58, 48.14);
    expect(Number(bubbles[0].attributes('cx'))).toBeCloseTo(x, 1);
    expect(Number(bubbles[0].attributes('cy'))).toBeCloseTo(y, 1);
    expect(bubbles[0].find('title').text()).toBe('Munich, Germany: 5 visits');
    expect(wrapper.find('text.fwld-map__label').text()).toBe('Munich');
  });

  it('goes back to the world map, where a click on a country opens it', async () => {
    const wrapper = map('de');
    await flushPromises();
    await wrapper.find('button.fwld-map__world').trigger('click');
    expect(wrapper.emitted('select')?.[0]).toEqual(['']);

    await wrapper.setProps({ country: '' });
    await flushPromises();
    const countries = wrapper.findAll('path.fwld-map__land');
    expect(countries).toHaveLength(3);
    expect(wrapper.find('button.fwld-map__world').exists()).toBe(false);
    await countries[1].trigger('click');
    expect(wrapper.emitted('select')?.[1]).toEqual(['fr']);
    // an area without a Matomo country code opens nothing
    await countries[2].trigger('click');
    expect(wrapper.emitted('select')).toHaveLength(2);
  });

  it('says so when Matomo has no map of the country, and tells the board', async () => {
    const wrapper = map('fr');
    await flushPromises();
    expect(wrapper.text()).toContain('No map of this country.');
    expect(wrapper.find('button.fwld-map__world').exists()).toBe(true);
    expect(wrapper.emitted('missing')?.[0]).toEqual(['fr']);
  });

  it('shades a country drawn as one area with all its visits', async () => {
    const wrapper = map('lu');
    await flushPromises();
    const area = wrapper.find('path.fwld-map__land');
    expect(area.classes()).toContain('fwld-map__land--visited');
    expect(area.find('title').text()).toBe('Luxembourg: 3 visits');
    expect(wrapper.text()).not.toContain('No regions here.');
  });

  it('shades the whole country when its map knows no region codes, and says so', async () => {
    const wrapper = map('cz');
    await flushPromises();
    const areas = wrapper.findAll('path.fwld-map__land');
    expect(areas.map((a) => a.classes().includes('fwld-map__land--visited'))).toEqual([true, true]);
    expect(areas[0].find('title').text()).toBe('Czechia: 5 visits');
    expect(wrapper.text()).toContain('No regions here.');
  });

  it('shows nothing when the world map cannot be loaded', async () => {
    (loadWorldMap as jest.Mock).mockImplementation(() => Promise.resolve(null));
    const wrapper = mount(LiveMap, { props: { title: 'Live map', url: 'x.svg' } });
    await flushPromises();
    expect(wrapper.find('section').exists()).toBe(false);
  });
});
