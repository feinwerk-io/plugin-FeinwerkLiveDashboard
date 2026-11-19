<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later

  The live map on Matomo's own maps (plugins/UserCountryMap/svg). A country shows centred with its
  regions shaded by their visits in the list window and its cities at their exact place; the world
  map shows the countries, and a click on one opens it (the board keeps which country is open).
-->
<template>
  <section v-if="world" class="fwld-panel fwld-map">
    <header class="fwld-panel__head">
      <h4 class="fwld-panel__title">{{ title }}</h4>
      <span class="fwld-panel__window">{{ windowLabel }}</span>
      <div class="fwld-panel__controls">
        <button
          v-if="country !== ''"
          type="button"
          class="fwld-map__world"
          @click="$emit('select', '')"
        >{{ texts.world }}</button>
      </div>
    </header>
    <p v-if="country !== '' && heading" class="fwld-map__heading">{{ heading }}</p>
    <p v-if="regionsUnknown" class="fwld-map__heading">{{ texts.noRegions }}</p>
    <p v-if="missing" class="fwld-panel__empty">{{ texts.noMap }}</p>
    <svg
      v-else-if="shown"
      ref="svg"
      class="fwld-map__svg"
      :class="countryMap ? 'fwld-map__svg--country' : 'fwld-map__svg--world'"
      :viewBox="`0 0 ${shown.view.width} ${shown.view.height}`"
      preserveAspectRatio="xMidYMid meet"
      role="img"
      :aria-label="heading || title"
      @click="onClick"
    >
      <path
        v-for="(area, index) in shown.context"
        :key="`context:${index}`"
        :d="area.d"
        class="fwld-map__context"
      />
      <path
        v-for="area in areas"
        :key="area.key"
        :d="area.d"
        :data-iso="area.iso"
        class="fwld-map__land"
        :class="{ 'fwld-map__land--visited': area.opacity > 0 }"
        :style="area.opacity > 0 ? { fillOpacity: area.opacity } : undefined"
      >
        <title v-if="area.title">{{ area.title }}</title>
      </path>
      <circle
        v-for="bubble in bubbles"
        :key="bubble.key"
        class="fwld-map__city"
        :cx="bubble.x"
        :cy="bubble.y"
        :r="bubble.r"
      >
        <title>{{ bubble.title }}</title>
      </circle>
      <text
        v-for="label in labels"
        :key="`label:${label.key}`"
        class="fwld-map__label"
        :x="label.x"
        :y="label.y"
        :text-anchor="label.anchor"
        :font-size="label.size"
        :stroke-width="label.halo"
      >{{ label.text }}</text>
    </svg>
    <p v-if="!missing && (texts.legend || texts.hint)" class="fwld-map__legend">
      <template v-if="countryMap">
        <span class="fwld-map__scale" aria-hidden="true"></span>
        <span>{{ texts.legend }}</span>
        <span class="fwld-map__dot" aria-hidden="true"></span>
        <span>{{ texts.legendCities }}</span>
      </template>
      <span v-else>{{ texts.hint }}</span>
    </p>
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
import {
  Box, bubbleRadius, inside, iso2, iso3, pathsBox, placeLabels, project, shade,
  unitsPerPixel as perPixel, WorldMap,
} from './geo';
import { loadWorldMap } from './worldMap';

export interface MapCountryRow {
  /** Matomo's country code (de) */
  code: string;
  label: string;
  count: number;
  title: string;
}

export interface MapRegionRow {
  country: string;
  /** the region code Matomo stores (location_region) */
  region: string;
  count: number;
}

export interface MapCityRow {
  key: string;
  /** Matomo's country code of the city */
  country: string;
  name: string;
  lat: number;
  lon: number;
  count: number;
  title: string;
}

export interface MapTexts {
  world?: string;
  noMap?: string;
  /** the country map knows no region codes of this Matomo version (Matomo 5's FIPS maps) */
  noRegions?: string;
  legend?: string;
  legendCities?: string;
  hint?: string;
}

interface Area {
  key: string;
  d: string;
  /** the country's map code on the world map; '' for regions */
  iso: string;
  opacity: number;
  title: string;
}

interface Bubble {
  key: string;
  x: number;
  y: number;
  r: number;
  title: string;
  name: string;
}

interface Label {
  key: string;
  x: number;
  anchor: 'start' | 'end';
  y: number;
  size: number;
  halo: number;
  text: string;
}

const MAX_LABELS = 8;
/** city names in screen pixels, whatever size the map is drawn at */
const LABEL_PX = 13;
/** a country shaded as a whole: its regions are unknown on the map, or it is drawn as one area */
const WHOLE_OPACITY = 0.6;

const round = (n: number): number => Math.round(n * 100) / 100;

export default defineComponent({
  props: {
    title: { type: String, required: true },
    windowLabel: { type: String, default: '' },
    /** Matomo's world map; the country maps lie beside it (DEU.svg) */
    url: { type: String, default: '' },
    /** the open country (Matomo's code, de); '' for the world map */
    country: { type: String, default: '' },
    countries: { type: Array as PropType<MapCountryRow[]>, default: () => [] },
    regions: { type: Array as PropType<MapRegionRow[]>, default: () => [] },
    cities: { type: Array as PropType<MapCityRow[]>, default: () => [] },
    texts: { type: Object as PropType<MapTexts>, default: () => ({}) },
    /** "Bayern: 6 visits" */
    visits: {
      type: Function as PropType<(label: string, count: number) => string>,
      default: (label: string, count: number) => `${label}: ${count}`,
    },
  },
  emits: ['select', 'missing'],
  data() {
    return {
      world: null as WorldMap | null,
      countryMap: null as WorldMap | null,
      /** Matomo has no map of the open country */
      missing: false,
      /** the drawn box in pixels: keeps bubbles and names the same size on every map */
      drawnWidth: 0,
      drawnHeight: 0,
      observer: null as ResizeObserver | null,
    };
  },
  computed: {
    code(): string {
      return this.country.toLowerCase();
    },
    shown(): WorldMap | null {
      return this.code === '' ? this.world : this.countryMap;
    },
    countryRow(): MapCountryRow | undefined {
      return this.countries.find((row) => row.code.toLowerCase() === this.code);
    },
    heading(): string {
      return this.countryRow ? this.countryRow.title : '';
    },
    unitsPerPixel(): number {
      if (!this.shown) {
        return 1;
      }
      const { width, height } = this.shown.view;
      return perPixel(width, height, this.drawnWidth, this.drawnHeight);
    },
    /** the world's countries or the open country's regions, shaded by their visits */
    areas(): Area[] {
      const map = this.shown;
      if (!map) {
        return [];
      }
      if (this.countryMap && map === this.countryMap) {
        const counts: Record<string, number> = {};
        this.regions.filter((r) => r.country.toLowerCase() === this.code).forEach((r) => {
          const key = r.region.toUpperCase();
          counts[key] = (counts[key] || 0) + r.count;
        });
        const max = Math.max(0, ...Object.values(counts));
        if (this.wholeCountry) {
          // one area (Matomo 6 codes small countries __ALL__) or no region codes known on this map
          return map.regions.map((region, index) => ({
            key: `region:${index}`,
            d: region.d,
            iso: '',
            opacity: WHOLE_OPACITY,
            title: this.heading,
          }));
        }
        return map.regions.map((region, index) => {
          const count = counts[region.code.toUpperCase()] || 0;
          return {
            key: `region:${index}`,
            d: region.d,
            iso: '',
            opacity: round(shade(count, max)),
            title: count > 0 ? this.visits(region.name, count) : region.name,
          };
        });
      }
      const rows: Record<string, MapCountryRow> = {};
      this.countries.forEach((row) => {
        const code = iso3(row.code);
        if (code !== '' && row.count > 0) {
          rows[code] = row;
        }
      });
      const max = Math.max(0, ...Object.values(rows).map((row) => row.count));
      return map.countries.map((country, index) => {
        const row = rows[country.iso];
        return {
          key: `country:${index}`,
          d: country.d,
          iso: country.iso,
          opacity: row ? round(shade(row.count, max)) : 0,
          title: row ? row.title : '',
        };
      });
    },
    /** visits in the open country, none in a region its map knows: shade it as a whole */
    wholeCountry(): boolean {
      const map = this.countryMap;
      if (!map || this.code === '' || !this.countryRow || this.countryRow.count <= 0) {
        return false;
      }
      const codes = new Set(this.regions
        .filter((r) => r.country.toLowerCase() === this.code && r.count > 0)
        .map((r) => r.region.toUpperCase()));
      return !map.regions.some((region) => codes.has(region.code.toUpperCase()));
    },
    /** the map has regions, but none it can match (a one-area country needs no note) */
    regionsUnknown(): boolean {
      return this.wholeCountry && !!this.countryMap && this.countryMap.regions.length > 1;
    },
    /** the box of the open country's regions: a city stored with a place outside it is left out */
    countryBox(): Box | null {
      return this.countryMap ? pathsBox(this.countryMap.regions.map((r) => r.d)) : null;
    },
    /** the open country's cities at their place; none on the world map */
    bubbles(): Bubble[] {
      const map = this.countryMap;
      const box = this.countryBox;
      if (!map || !box || this.code === '') {
        return [];
      }
      const cities = this.cities.filter((c) => c.country.toLowerCase() === this.code
        && Number.isFinite(c.lat) && Number.isFinite(c.lon));
      const max = Math.max(0, ...cities.map((c) => c.count));
      // the big ones first, so the small ones stay on top and can be pointed at
      return [...cities].sort((a, b) => b.count - a.count).map((city) => {
        const [x, y] = project(map.view, city.lon, city.lat);
        return {
          key: city.key,
          x: round(x),
          y: round(y),
          r: round(bubbleRadius(city.count, max, this.unitsPerPixel)),
          title: city.title,
          name: city.name,
        };
      }).filter((bubble) => inside(box, bubble.x, bubble.y));
    },
    labels(): Label[] {
      const upp = this.unitsPerPixel;
      const width = this.shown ? this.shown.view.width : 0;
      // the bubbles come biggest first; names that would overlap give way
      return placeLabels(this.bubbles, LABEL_PX, upp, width, MAX_LABELS).map((label) => ({
        key: label.key,
        x: round(label.x),
        y: round(label.y),
        anchor: label.anchor,
        size: round(LABEL_PX * upp),
        halo: round(3 * upp),
        text: label.text,
      }));
    },
  },
  watch: {
    country() {
      this.loadCountry();
    },
  },
  mounted() {
    loadWorldMap(this.url).then((world) => {
      this.world = world;
      this.loadCountry();
    });
  },
  beforeUnmount() {
    if (this.observer) {
      this.observer.disconnect();
    }
  },
  methods: {
    /** The open country's map; the one shown stays until the next is there (no flicker). */
    loadCountry() {
      const code = iso3(this.code);
      if (this.code === '' || code === '' || !this.world) {
        this.countryMap = null;
        this.missing = this.code !== '' && code === '' && this.world !== null;
        if (this.missing) {
          this.$emit('missing', this.code);
        }
        this.$nextTick(() => this.observe());
        return;
      }
      const requested = this.code;
      loadWorldMap(this.url.replace(/world\.svg$/, `${code}.svg`)).then((map) => {
        if (requested !== this.code) {
          return;
        }
        this.countryMap = map;
        this.missing = map === null;
        if (this.missing) {
          this.$emit('missing', requested);
        }
        this.$nextTick(() => this.observe());
      });
    },
    observe() {
      const svg = this.$refs.svg as SVGSVGElement | undefined;
      if (this.observer) {
        this.observer.disconnect();
        this.observer = null;
      }
      if (!svg || typeof ResizeObserver !== 'function') {
        return;
      }
      this.observer = new ResizeObserver((entries) => {
        this.drawnWidth = entries[0].contentRect.width;
        this.drawnHeight = entries[0].contentRect.height;
      });
      this.observer.observe(svg);
    },
    /** On the world map a click on a country opens it. */
    onClick(event: MouseEvent) {
      if (this.code !== '') {
        return;
      }
      const place = (event.target as Element).closest('[data-iso]');
      const code = place ? iso2(place.getAttribute('data-iso') || '') : '';
      if (code !== '') {
        this.$emit('select', code);
      }
    },
  },
});
</script>
