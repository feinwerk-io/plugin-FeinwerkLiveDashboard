<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->
<template>
  <section class="fwld-panel">
    <header class="fwld-panel__head">
      <h4 class="fwld-panel__title">{{ title }}</h4>
      <span class="fwld-panel__window">{{ windowLabel }}</span>
      <span v-if="estimated" class="fwld-panel__note" :title="estimatedHelp">
        {{ estimatedLabel }}
      </span>
      <div class="fwld-panel__controls"><slot name="controls" /></div>
    </header>
    <ol v-if="shown.length" class="fwld-list">
      <li
        v-for="(row, i) in shown"
        :key="String(row.id) + row.label"
        class="fwld-list__row"
        :class="{ 'fwld-list__row--selected': selectable && String(row.id) === selectedId }"
      >
        <button
          v-if="selectable && (selectableIds === null || selectableIds.includes(String(row.id)))"
          type="button"
          class="fwld-list__label fwld-list__pick"
          :title="row.label"
          :aria-pressed="String(row.id) === selectedId ? 'true' : 'false'"
          @click="$emit('select', row.id)"
        >{{ display(row.label) }}</button>
        <span v-else class="fwld-list__label" :title="row.label">{{ display(row.label) }}</span>
        <span
          class="fwld-list__rank"
          :class="rankClass(changes[i])"
          :title="rankTitle(changes[i])"
        >{{ rankText(changes[i]) }}</span>
        <span v-flash="row.count" class="fwld-list__count">{{ number(row.count) }}</span>
        <span class="fwld-list__bar" aria-hidden="true">
          <span class="fwld-list__fill" :style="{ width: `${barWidth(row.count)}%` }" />
        </span>
      </li>
    </ol>
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
import { translate } from 'CoreHome';
import {
  formatNumber, pageLanguage, pathLabel, share,
} from './format';
import { flash, rankChanges } from './motion';
import { Row } from './types';

export default defineComponent({
  directives: { flash },
  emits: ['select'],
  props: {
    title: { type: String, required: true },
    windowLabel: { type: String, default: '' },
    rows: { type: Array as PropType<Row[]>, required: true },
    max: { type: Number, default: 8 },
    estimated: { type: Boolean, default: false },
    emptyText: { type: String, default: '' },
    paths: { type: Boolean, default: false },
    /** rows can be chosen (the countries list opens a country on the map) */
    selectable: { type: Boolean, default: false },
    selectedId: { type: String, default: '' },
    /** only these rows can be chosen (null: all) */
    selectableIds: { type: Array as PropType<string[] | null>, default: null },
    /** what the list shows (list, window, URL or title): a new one starts the comparison anew */
    listKey: { type: String, default: '' },
    /** changes when the list was computed again */
    revision: { type: String, default: '' },
  },
  data() {
    return {
      previous: null as Map<string, number> | null,
      changes: [] as Array<number | 'new' | null>,
      lastKey: '',
    };
  },
  watch: {
    orderKey: {
      immediate: true,
      handler() {
        const ids = this.rows.map((row) => String(row.id));
        if (this.listKey !== this.lastKey) {
          this.previous = null;
          this.lastKey = this.listKey;
        }
        this.changes = rankChanges(this.previous, ids);
        this.previous = new Map(ids.map((id, rank) => [id, rank]));
      },
    },
  },
  computed: {
    orderKey(): string {
      return `${this.listKey}\n${this.revision}\n${this.rows.map((row) => row.id).join('\n')}`;
    },
    shown(): Row[] {
      return this.rows.slice(0, this.max);
    },
    top(): number {
      return this.shown.reduce((max, row) => Math.max(max, row.count), 0);
    },
    estimatedLabel(): string {
      return translate('FeinwerkLiveDashboard_Estimated');
    },
    estimatedHelp(): string {
      return translate('FeinwerkLiveDashboard_EstimatedHelp');
    },
  },
  methods: {
    number(value: number): string {
      return formatNumber(value, pageLanguage());
    },
    rankText(change: number | 'new' | null | undefined): string {
      if (change === 'new') {
        return translate('FeinwerkLiveDashboard_RankNew');
      }
      if (typeof change !== 'number' || change === 0) {
        return '';
      }
      return change > 0 ? `▲ ${change}` : `▼ ${-change}`;
    },
    rankClass(change: number | 'new' | null | undefined): string {
      if (change === 'new') {
        return 'fwld-list__rank--new';
      }
      if (typeof change !== 'number' || change === 0) {
        return '';
      }
      return change > 0 ? 'fwld-list__rank--up' : 'fwld-list__rank--down';
    },
    rankTitle(change: number | 'new' | null | undefined): string {
      if (change === 'new') {
        return translate('FeinwerkLiveDashboard_RankNewTitle');
      }
      if (typeof change !== 'number' || change === 0) {
        return '';
      }
      const key = change > 0 ? 'FeinwerkLiveDashboard_RankUp' : 'FeinwerkLiveDashboard_RankDown';
      return translate(key, String(Math.abs(change)));
    },
    display(label: string): string {
      return this.paths ? pathLabel(label) : label;
    },
    barWidth(count: number): number {
      return share(count, this.top);
    },
  },
});
</script>
