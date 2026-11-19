/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { mount } from '@vue/test-utils';
import LiveLog from '../src/LiveLog.vue';

const HEADS = {
  duration: 'Duration',
  country: 'Country',
  source: 'Source',
  device: 'Device',
  page: 'Current page',
  actions: 'Actions',
  marks: 'Attributes',
};
const ICONS = ['goal', 'order', 'cart', 'returning'].map((key) => ({ key, src: `${key}.svg`, label: key }));
const ROW = {
  key: 'a',
  duration: '4 min 20 s',
  country: 'Germany',
  source: 'Google',
  channel: 'Search Engines',
  device: 'Desktop',
  page: '/p/1',
  pageTitle: 'Product',
  actions: '3',
  icons: ['goal', 'returning'],
};

function log() {
  return mount(LiveLog, {
    props: {
      title: 'Latest visits', rows: [ROW], heads: HEADS, icons: ICONS,
    },
  });
}

describe('LiveLog', () => {
  it('shows every column while the panel is wide enough', async () => {
    const wrapper = log();
    await wrapper.setData({ width: 900 });
    const heads = wrapper.findAll('th').map((th) => th.text());
    expect(heads.slice(0, 6)).toEqual(['Duration', 'Country', 'Source', 'Device', 'Current page', 'Actions']);
    expect(wrapper.findAll('td.fwld-log__icon')).toHaveLength(4);
    expect(wrapper.find('section').classes()).not.toContain('fwld-log--narrow');
  });

  it('keeps duration, country, source, actions and the icons in a narrow panel', async () => {
    const wrapper = log();
    await wrapper.setData({ width: 300 });

    expect(wrapper.find('section').classes()).toContain('fwld-log--narrow');
    expect(wrapper.findAll('th')).toHaveLength(4);
    expect(wrapper.text()).not.toContain('Desktop');
    expect(wrapper.text()).not.toContain('/p/1');
    const lines = wrapper.findAll('tbody .fwld-log__line').map((line) => line.text());
    expect(lines).toEqual(['Germany', 'Google']);
    // every kind of icon keeps its own place, filled or empty
    const slots = wrapper.findAll('tbody .fwld-log__slot');
    expect(slots).toHaveLength(4);
    expect(slots.map((slot) => slot.find('img').exists())).toEqual([true, false, false, true]);
  });
});
