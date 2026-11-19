/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { defineComponent, h, withDirectives } from 'vue';
import { mount } from '@vue/test-utils';
import {
  flash, freshKeys, rankChanges, tweenValue,
} from '../src/motion';

const Probe = defineComponent({
  props: { value: { type: String, required: true } },
  setup(props) {
    return () => withDirectives(h('span', { class: 'probe' }, props.value), [[flash, props.value]]);
  },
});

describe('motion', () => {
  it('lights up a value when it changes, not when it first shows', async () => {
    const wrapper = mount(Probe, { props: { value: '12' } });
    expect(wrapper.classes()).not.toContain('fwld-flash');

    await wrapper.setProps({ value: '12' });
    expect(wrapper.classes()).not.toContain('fwld-flash');

    await wrapper.setProps({ value: '13' });
    expect(wrapper.classes()).toContain('fwld-flash');

    wrapper.element.dispatchEvent(new Event('animationend'));
    expect(wrapper.classes()).not.toContain('fwld-flash');
  });

  it('counts between two numbers with an easing that ends on the target', () => {
    expect(tweenValue(100, 140, 0)).toBe(100);
    expect(tweenValue(100, 140, 1)).toBe(140);
    expect(tweenValue(100, 140, 2)).toBe(140);
    const half = tweenValue(100, 140, 0.5);
    expect(half).toBeGreaterThan(120);
    expect(half).toBeLessThan(140);
    expect(Number.isInteger(half)).toBe(true);
    expect(tweenValue(140, 100, 0.5)).toBeLessThan(120);
  });

  it('tells how many places each row moved since the last list', () => {
    expect(rankChanges(null, ['a', 'b'])).toEqual([null, null]);
    const previous = new Map([['a', 0], ['b', 1], ['c', 2], ['d', 3]]);
    // b up one, a down one, d up one, c gone, e new
    expect(rankChanges(previous, ['b', 'a', 'd', 'e'])).toEqual([1, -1, 1, 'new']);
    expect(rankChanges(previous, ['a', 'b'])).toEqual([0, 0]);
  });

  it('treats an empty previous list like none: after a reload nothing is new', () => {
    // the board renders its lists empty until the first answer arrives
    expect(rankChanges(new Map(), ['a', 'b'])).toEqual([null, null]);
    expect(freshKeys(new Set(), ['a', 'b'])).toEqual(new Set());
  });

  it('marks only rows that were not there before, and none on the first list', () => {
    expect(freshKeys(null, ['a', 'b'])).toEqual(new Set());
    expect(freshKeys(new Set(['a', 'b']), ['c', 'a'])).toEqual(new Set(['c']));
    expect(freshKeys(new Set(['a']), [])).toEqual(new Set());
  });
});
