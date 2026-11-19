/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { mount } from '@vue/test-utils';
import SegmentedSwitch from '../src/SegmentedSwitch.vue';

const OPTIONS = [
  { value: 'events', label: 'Events' },
  { value: 'downloads', label: 'Downloads' },
];

describe('SegmentedSwitch', () => {
  it('keeps the full label in the title, so a label cut off in a narrow panel stays readable', () => {
    const wrapper = mount(SegmentedSwitch, {
      props: { options: OPTIONS, modelValue: 'events', label: 'Tabs' },
    });
    const buttons = wrapper.findAll('button');
    expect(buttons.map((b) => b.attributes('title'))).toEqual(['Events', 'Downloads']);
    expect(buttons.map((b) => b.attributes('aria-pressed'))).toEqual(['true', 'false']);
  });

  it('emits the chosen value', async () => {
    const wrapper = mount(SegmentedSwitch, {
      props: { options: OPTIONS, modelValue: 'events', label: 'Tabs' },
    });
    await wrapper.findAll('button')[1].trigger('click');
    expect(wrapper.emitted('update:modelValue')).toEqual([['downloads']]);
  });
});
