/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { Directive } from 'vue';

/**
 * Small, calm motion for peripheral vision: a changed value lights up for about a second (CSS
 * class fwld-flash), the big number counts to its new value, new rows of the live log light up.
 * Nothing moves on the first render; with "reduce motion" the stylesheet switches it off and the
 * count jumps (prefersReducedMotion).
 */
export const FLASH_CLASS = 'fwld-flash';

function restart(el: HTMLElement): void {
  el.classList.remove(FLASH_CLASS);
  // reading the layout between remove and add restarts the CSS animation
  el.getBoundingClientRect();
  el.classList.add(FLASH_CLASS);
}

export const flash: Directive<HTMLElement, unknown> = {
  mounted(el) {
    el.addEventListener('animationend', () => el.classList.remove(FLASH_CLASS));
  },
  updated(el, binding) {
    if (binding.value !== binding.oldValue) {
      restart(el);
    }
  },
};

/** The value between from and to at progress 0 to 1, eased out, as a whole number. */
export function tweenValue(from: number, to: number, progress: number): number {
  const p = Math.min(1, Math.max(0, progress));
  const eased = 1 - (1 - p) ** 3;
  return Math.round(from + (to - from) * eased);
}

/**
 * Places each row moved since the previous list (positive: up), 'new' for a row that was not in
 * it, null for every row of the first list. An empty previous list counts as none: the board shows
 * empty lists until the first answer arrives, and after a reload nothing is new.
 */
export function rankChanges(
  previous: Map<string, number> | null,
  ids: string[],
): Array<number | 'new' | null> {
  return ids.map((id, rank) => {
    if (previous === null || previous.size === 0) {
      return null;
    }
    const before = previous.get(id);
    return before === undefined ? 'new' : before - rank;
  });
}

/** Keys of rows that are new against the previous list; none for a first or after an empty list. */
export function freshKeys(previous: Set<string> | null, keys: string[]): Set<string> {
  if (previous === null || previous.size === 0) {
    return new Set();
  }
  return new Set(keys.filter((key) => !previous.has(key)));
}

export function prefersReducedMotion(): boolean {
  return typeof window !== 'undefined' && typeof window.matchMedia === 'function'
    && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}
