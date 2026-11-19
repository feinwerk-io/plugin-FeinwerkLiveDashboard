<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->
<template>
  <section class="fwld-pulse">
    <div class="fwld-pulse__now" :title="activeHelp">
      <span v-flash="activeNow" class="fwld-pulse__number">{{ shownNumber }}</span>
      <span class="fwld-pulse__label">{{ activeLabel }}</span>
    </div>
    <figure class="fwld-pulse__spark">
      <div class="fwld-spark__chart">
        <span class="fwld-spark__axis" aria-hidden="true">
          <span>{{ scaleTop }}</span>
          <span>0</span>
        </span>
        <svg
          class="fwld-spark"
          viewBox="0 0 300 64"
          preserveAspectRatio="none"
          role="img"
          :aria-label="sparkSummary"
        >
          <rect
            v-for="(bar, i) in bars"
            :key="i"
            class="fwld-spark__bar"
            :class="{ 'fwld-spark__bar--current': bar.current }"
            :x="i * slot + 1"
            :y="64 - barHeight(bar.height)"
            :width="slot - 2"
            :height="barHeight(bar.height)"
          >
            <title>{{ barTitles[i] }}</title>
          </rect>
        </svg>
      </div>
      <div class="fwld-spark__times" aria-hidden="true">
        <span>{{ timeLabels[0] }}</span>
        <span>{{ timeLabels[1] }}</span>
        <span>{{ timeLabels[2] }}</span>
      </div>
      <figcaption class="fwld-pulse__caption">{{ sparkLabel }}</figcaption>
    </figure>
    <dl class="fwld-kpis">
      <div v-for="kpi in kpis" :key="kpi.label" class="fwld-kpis__item" :title="kpi.title">
        <dt class="fwld-kpis__label">{{ kpi.label }}</dt>
        <dd v-flash="kpi.value" class="fwld-kpis__value">{{ kpi.value }}</dd>
        <dd v-if="kpi.note" class="fwld-kpis__note">{{ kpi.note }}</dd>
      </div>
    </dl>
  </section>
</template>

<script lang="ts">
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { defineComponent, PropType } from 'vue';
import { Bar } from './board';
import { formatNumber, pageLanguage } from './format';
import { flash, prefersReducedMotion, tweenValue } from './motion';

const COUNT_MS = 700;

export interface Kpi {
  label: string;
  value: string;
  note?: string;
  title?: string;
}

export default defineComponent({
  directives: { flash },
  props: {
    activeNow: { type: Number, required: true },
    activeLabel: { type: String, required: true },
    activeHelp: { type: String, default: '' },
    bars: { type: Array as PropType<Bar[]>, required: true },
    barTitles: { type: Array as PropType<string[]>, required: true },
    scaleTop: { type: String, required: true },
    timeLabels: { type: Array as PropType<string[]>, required: true },
    sparkLabel: { type: String, required: true },
    sparkSummary: { type: String, required: true },
    kpis: {
      type: Array as PropType<Kpi[]>,
      required: true,
    },
  },
  data() {
    return { shown: this.activeNow, frame: 0 };
  },
  watch: {
    activeNow(to: number, from: number) {
      window.cancelAnimationFrame(this.frame);
      if (prefersReducedMotion() || typeof window.requestAnimationFrame !== 'function') {
        this.shown = to;
        return;
      }
      const start = performance.now();
      const step = (time: number) => {
        const progress = (time - start) / COUNT_MS;
        this.shown = tweenValue(from, to, progress);
        if (progress < 1) {
          this.frame = window.requestAnimationFrame(step);
        }
      };
      this.frame = window.requestAnimationFrame(step);
    },
  },
  beforeUnmount() {
    window.cancelAnimationFrame(this.frame);
  },
  computed: {
    shownNumber(): string {
      return formatNumber(this.shown, pageLanguage());
    },
    slot(): number {
      return 300 / Math.max(1, this.bars.length);
    },
  },
  methods: {
    barHeight(percent: number): number {
      // a minute without hits still shows a hairline, so the time axis stays readable
      return Math.max(1, (percent / 100) * 62);
    },
  },
});
</script>
