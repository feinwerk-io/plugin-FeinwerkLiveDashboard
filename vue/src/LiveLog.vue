<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later

  The live log: the newest visits with their main attributes. Shown only when the API sends it
  (the website's visits log is on and no data rounding applies); every value is rendered as text.
-->
<template>
  <section class="fwld-panel fwld-log" :class="{ 'fwld-log--narrow': narrow }">
    <header class="fwld-panel__head">
      <h4 class="fwld-panel__title">{{ title }}</h4>
      <span class="fwld-panel__window">{{ windowLabel }}</span>
    </header>
    <div v-if="rows.length" class="fwld-log__scroll">
      <table class="fwld-log__table">
        <!-- fixed widths, so the columns stay where they are when the rows change -->
        <colgroup v-if="narrow">
          <col class="fwld-log__col-time">
          <col>
          <col class="fwld-log__col-num">
          <col :style="{ width: `${markWidth}px` }">
        </colgroup>
        <colgroup v-else>
          <col class="fwld-log__col-time">
          <col class="fwld-log__col-country">
          <col class="fwld-log__col-source">
          <col class="fwld-log__col-device">
          <col>
          <col class="fwld-log__col-num">
          <col v-for="icon in icons" :key="icon.key" class="fwld-log__col-icon">
        </colgroup>
        <thead v-if="narrow">
          <tr>
            <th scope="col">{{ heads.duration }}</th>
            <th scope="col">
              <span class="fwld-log__line">{{ heads.country }}</span>
              <span class="fwld-log__line">{{ heads.source }}</span>
            </th>
            <th scope="col" class="fwld-log__num">{{ heads.actions }}</th>
            <th scope="col"><span class="fwld-log__hidden">{{ heads.marks }}</span></th>
          </tr>
        </thead>
        <thead v-else>
          <tr>
            <th scope="col">{{ heads.duration }}</th>
            <th scope="col">{{ heads.country }}</th>
            <th scope="col">{{ heads.source }}</th>
            <th scope="col">{{ heads.device }}</th>
            <th scope="col">{{ heads.page }}</th>
            <th scope="col" class="fwld-log__num">{{ heads.actions }}</th>
            <th v-for="icon in icons" :key="icon.key" scope="col">
              <span class="fwld-log__hidden">{{ icon.label }}</span>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="row in rows"
            :key="row.key"
            :class="{ 'fwld-flash': fresh.has(row.key) }"
          >
            <td class="fwld-log__time">{{ row.duration }}</td>
            <template v-if="narrow">
              <td :title="`${row.country}, ${row.channel}`">
                <span class="fwld-log__line">{{ row.country }}</span>
                <span class="fwld-log__line fwld-log__sub">{{ row.source }}</span>
              </td>
              <td class="fwld-log__num">{{ row.actions }}</td>
              <td class="fwld-log__marks">
                <!-- two rows of fixed places: each kind of icon keeps its own -->
                <span class="fwld-log__slots">
                  <span v-for="icon in icons" :key="icon.key" class="fwld-log__slot">
                    <img
                      v-if="row.icons.includes(icon.key)"
                      :src="icon.src"
                      width="16"
                      height="16"
                      :alt="icon.label"
                      :title="icon.label"
                    >
                  </span>
                </span>
              </td>
            </template>
            <template v-else>
              <td :title="row.country">{{ row.country }}</td>
              <td :title="row.channel">{{ row.source }}</td>
              <td :title="row.device">{{ row.device }}</td>
              <td class="fwld-log__page" :title="row.pageTitle">{{ row.page }}</td>
              <td class="fwld-log__num">{{ row.actions }}</td>
              <td v-for="icon in icons" :key="icon.key" class="fwld-log__icon">
                <img
                  v-if="row.icons.includes(icon.key)"
                  :src="icon.src"
                  width="16"
                  height="16"
                  :alt="icon.label"
                  :title="icon.label"
                >
              </td>
            </template>
          </tr>
        </tbody>
      </table>
    </div>
    <p v-else class="fwld-panel__empty">{{ emptyText }}</p>
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
import { freshKeys } from './motion';
import { logNarrow, markColumns } from './board';
import { LogIcon } from './types';

export interface LogRow {
  key: string;
  /** how long the visit has lasted, formatted */
  duration: string;
  country: string;
  source: string;
  channel: string;
  device: string;
  page: string;
  pageTitle: string;
  actions: string;
  icons: string[];
}

export default defineComponent({
  props: {
    title: { type: String, required: true },
    windowLabel: { type: String, default: '' },
    rows: { type: Array as PropType<LogRow[]>, required: true },
    heads: { type: Object as PropType<Record<string, string>>, required: true },
    icons: { type: Array as PropType<LogIcon[]>, default: () => [] },
    emptyText: { type: String, default: '' },
  },
  data() {
    return {
      previous: null as Set<string> | null,
      fresh: new Set<string>(),
      // the panel's content width, measured; 0 until then
      width: 0,
      observer: null as ResizeObserver | null,
    };
  },
  computed: {
    narrow(): boolean {
      return logNarrow(this.width);
    },
    markWidth(): number {
      // 16 px icons with 2 px between them, and 4 px to the panel's edge
      return markColumns(this.icons.length) * 18 + 4;
    },
    // one string, so the watcher runs when the visits change, not when their age ticks
    rowKeys(): string {
      return this.rows.map((row) => row.key).join('\n');
    },
  },
  mounted() {
    if (typeof ResizeObserver === 'function') {
      this.observer = new ResizeObserver((entries) => {
        this.width = Math.round(entries[0].contentRect.width);
      });
      this.observer.observe(this.$el as HTMLElement);
    }
  },
  beforeUnmount() {
    if (this.observer) {
      this.observer.disconnect();
    }
  },
  watch: {
    rowKeys: {
      immediate: true,
      handler(joined: string) {
        const keys = joined === '' ? [] : joined.split('\n');
        this.fresh = freshKeys(this.previous, keys);
        this.previous = new Set(keys);
      },
    },
  },
});
</script>
