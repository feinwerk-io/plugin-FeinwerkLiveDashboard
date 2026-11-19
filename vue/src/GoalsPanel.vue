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
    </header>
    <table v-if="rows.length" class="fwld-goals">
      <tbody>
        <tr v-for="row in rows" :key="row.key">
          <th scope="row" class="fwld-goals__label" :title="row.label">{{ row.label }}</th>
          <td v-flash="row.count" class="fwld-goals__count">{{ row.count }}</td>
          <td v-flash="row.money" class="fwld-goals__money">{{ row.money }}</td>
        </tr>
      </tbody>
    </table>
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
import { flash } from './motion';

export default defineComponent({
  directives: { flash },
  props: {
    title: { type: String, required: true },
    windowLabel: { type: String, default: '' },
    rows: {
      type: Array as PropType<Array<{ key: string; label: string; count: string; money: string }>>,
      required: true,
    },
    emptyText: { type: String, default: '' },
  },
});
</script>
