<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later

  Visitors › Live Dashboard: the shared live snapshot of a website
  (FeinwerkLiveDashboard.getSnapshot) as a board. Every label comes as plain text from the API and
  is rendered as text.
-->
<template>
  <div
    class="fwld-board"
    :class="{
      'fwld-board--fullscreen': fullscreen,
      'fwld-board--part': variant !== 'full',
      'fwld-board--in-widget': inWidget,
    }"
  >
    <header class="fwld-board__bar">
      <h2 v-if="fullscreen" class="fwld-board__site">{{ siteName }}</h2>
      <p class="fwld-status" :class="`fwld-status--${status.kind}`" role="status">
        <span class="fwld-status__dot" aria-hidden="true" />
        <span class="fwld-status__text">{{ statusText }}</span>
        <button
          v-if="status.kind === 'offline'"
          type="button"
          class="fwld-status__retry"
          @click="retry"
        >{{ tr('Retry') }}</button>
      </p>
      <p v-if="status.reduced" class="fwld-board__notice">{{ tr('ReducedDetail') }}</p>
      <div v-if="show.windowSwitch" class="fwld-board__window">
        <span class="fwld-board__window-label">{{ tr('ListWindow') }}</span>
        <SegmentedSwitch v-model="listWindow" :options="windowOptions" :label="tr('ListWindow')" />
      </div>
      <button
        v-if="canFullscreen && show.fullscreen"
        type="button"
        class="fwld-board__fullscreen"
        :aria-pressed="fullscreen ? 'true' : 'false'"
        @click="toggleFullscreen"
      >{{ fullscreen ? tr('ExitFullScreen') : tr('FullScreen') }}</button>
    </header>

    <p v-if="status.kind === 'stale'" class="fwld-board__warning">{{ tr('Stale') }}</p>

    <PulseStrip
      v-if="show.pulse"
      :active-now="fast.activeNow || 0"
      :active-label="tr('ActiveNow')"
      :active-help="tr('ActiveNowHelp', activeMinutes)"
      :bars="sparkBars"
      :bar-titles="sparkTitles"
      :scale-top="number(sparkTop)"
      :time-labels="sparkTimeLabels"
      :spark-label="tr('VisitsPerMinute', sparkMinutes)"
      :spark-summary="sparkSummary"
      :kpis="kpis"
    />

    <div ref="grid" class="fwld-grid">
      <LiveLog
        v-if="show.log && fast.newestVisits"
        :style="{ gridColumn: variant === 'visits' ? '1 / -1' : `span ${logColumns}` }"
        :title="tr('LiveLog')"
        :window-label="tr('WindowNow')"
        :rows="logRows"
        :heads="logHeads"
        :icons="state.data?.logIcons || []"
        :empty-text="tr('EmptyNow')"
      />
      <LiveMap
        v-if="show.map && worldMap"
        :style="{ gridColumn: `span ${mapColumns}` }"
        :title="tr('Map')"
        :window-label="windowLabel"
        :url="worldMap"
        :country="mapCountry"
        :countries="mapCountries"
        :regions="mapRegions"
        :cities="mapCities"
        :texts="mapTexts"
        :visits="mapVisits"
        @select="openCountry"
        @missing="onMapMissing"
      />
      <TopList
        v-if="show.countries"
        :title="tr('Countries')"
        :window-label="windowLabel"
        :rows="list(slow.countries)"
        :list-key="`countries:${listWindow}`"
        :revision="revision('slow')"
        :estimated="visitsEstimated"
        :empty-text="tr('Empty')"
        :selectable="show.map && worldMap !== ''"
        :selectable-ids="mapCountryIds"
        :selected-id="mapCountry"
        @select="openCountry"
      />
      <TopList
        v-if="show.pagesNow"
        :title="tr('PagesNow')"
        :window-label="tr('WindowNow')"
        :rows="pagesNowRows"
        :paths="pageLabel === 'url'"
        :list-key="`pagesNow:${pageLabel}`"
        :revision="revision('fast')"
        :estimated="(fast.k || 1) > 1"
        :empty-text="tr('EmptyNow')"
      >
        <template #controls>
          <SegmentedSwitch
            v-model="pageLabel"
            :options="pageLabelOptions"
            :label="tr('PageLabel')"
          />
        </template>
      </TopList>
      <TopList
        v-if="show.topPages"
        :title="tr('TopPages')"
        :window-label="windowLabel"
        :rows="topPageRows"
        :paths="pageLabel === 'url'"
        :list-key="`topPages:${pageLabel}:${listWindow}`"
        :revision="revision('slow')"
        :estimated="(slow.kActions || 1) > 1"
        :empty-text="tr('Empty')"
      />
      <TopList
        v-if="show.channels"
        :title="tr('Channels')"
        :window-label="windowLabel"
        :rows="list(slow.channels)"
        :list-key="`channels:${listWindow}`"
        :revision="revision('slow')"
        :estimated="visitsEstimated"
        :empty-text="tr('Empty')"
      />
      <TopList
        v-if="show.campaigns"
        :title="tr('Campaigns')"
        :window-label="windowLabel"
        :rows="list(slow.campaigns)"
        :list-key="`campaigns:${listWindow}`"
        :revision="revision('slow')"
        :estimated="visitsEstimated"
        :empty-text="tr('EmptyCampaigns')"
      />
      <TopList
        v-if="show.campaignSources && slow.campaignSources"
        :title="tr('CampaignSources')"
        :window-label="windowLabel"
        :rows="list(slow.campaignSources)"
        :list-key="`campaignSources:${listWindow}`"
        :revision="revision('slow')"
        :estimated="visitsEstimated"
        :empty-text="tr('EmptyCampaigns')"
      />
      <TopList
        v-if="show.devices"
        :title="tr('Devices')"
        :window-label="windowLabel"
        :rows="list(slow.devices)"
        :list-key="`devices:${listWindow}`"
        :revision="revision('slow')"
        :estimated="visitsEstimated"
        :empty-text="tr('Empty')"
      />
      <GoalsPanel
        v-if="show.goals"
        :title="tr('Goals')"
        :window-label="windowLabel"
        :rows="goalRows"
        :empty-text="tr('EmptyGoals')"
      />
      <TopList
        v-if="show.more"
        :title="tr('MoreActions')"
        :window-label="windowLabel"
        :rows="moreRows"
        :paths="moreTab === 'downloads'"
        :list-key="`more:${moreTab}:${listWindow}`"
        :revision="revision('slow')"
        :estimated="(slow.kActions || 1) > 1"
        :empty-text="tr('Empty')"
      >
        <template #controls>
          <SegmentedSwitch v-model="moreTab" :options="moreOptions" :label="tr('MoreActions')" />
        </template>
      </TopList>
    </div>

    <FeedbackBar
      v-if="feedbackShown(feedback, inWidget)"
      class="fwld-board__feedback"
      :id-site="Number(idSite)"
      topic="board"
      :question="tr('FeedbackQuestion_board')"
      :feedback="feedback"
    />
  </div>
</template>

<script lang="ts">
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { defineComponent, PropType } from 'vue';
import { Matomo, translate } from 'CoreHome';
import {
  Bar, bars, boardStatus, BoardStatus, inWindow, LIST_WINDOWS, PageLabel, pagesNow, readOption,
  retryInSeconds, sparkFor, topPages, writeOption, logSpan, gridColumnCount, barMinutes,
  scaleMax, Sections, sectionsOf, inWidget, mapSpan, feedbackShown, sparkEnd,
} from './board';
import {
  ageText, durationText, formatMoney, formatNumber, pageLanguage, pathLabel, timeOfDay,
} from './format';
import { sharedPoller } from './livePoller';
import { PollerState } from './poller';
import {
  ByWindow, City, FastPayload, GoalRow, ListWindow, Row, SlowPayload, TodayPayload,
} from './types';
import FeedbackBar, { FeedbackSettings } from './FeedbackBar.vue';
import GoalsPanel from './GoalsPanel.vue';
import LiveMap, {
  MapCityRow, MapCountryRow, MapRegionRow, MapTexts,
} from './LiveMap.vue';
import { iso3 } from './geo';
import LiveLog, { LogRow } from './LiveLog.vue';
import PulseStrip, { Kpi } from './PulseStrip.vue';
import SegmentedSwitch from './SegmentedSwitch.vue';
import TopList from './TopList.vue';

type MoreTab = 'events' | 'downloads' | 'outlinks' | 'search';

interface BoardData {
  state: PollerState;
  /** the country open on the map; '' for the world map */
  mapCountry: string;
  /** the viewer chose it (until then the map follows the country with the most visits) */
  mapChosen: boolean;
  pageLabel: PageLabel;
  listWindow: ListWindow;
  moreTab: MoreTab;
  now: number;
  fullscreen: boolean;
  inWidget: boolean;
  unsubscribe: (() => void) | null;
  ticker: number | null;
  logColumns: number;
  mapColumns: number;
  resizeObserver: ResizeObserver | null;
}

function plainTranslate(key: string, ...values: Array<string | number>): string {
  return translate(key, ...values.map(String));
}

function storage(): Storage | { getItem(): null; setItem(): void } {
  try {
    return window.localStorage;
  } catch (e) {
    return { getItem: () => null, setItem: () => undefined };
  }
}

export default defineComponent({
  components: {
    FeedbackBar, GoalsPanel, LiveLog, LiveMap, PulseStrip, SegmentedSwitch, TopList,
  },
  props: {
    idSite: { type: [Number, String], required: true },
    siteName: { type: String, default: '' },
    currencySymbol: { type: String, default: '' },
    activeMinutes: { type: [Number, String], default: 3 },
    /** full (the page) or a dashboard widget's part: overview, pages, sources, visits */
    variant: { type: String, default: 'full' },
    /** a city is on the map from this many visits in the time window (city_min_visits) */
    cityMinVisits: { type: [Number, String], default: 3 },
    /** Matomo's world map (URL relative to Matomo), '' when the file is missing */
    worldMap: { type: String, default: '' },
    /** the feedback thumbs (only on the board's own page): enabled, supportUrl, version */
    feedback: { type: Object as PropType<FeedbackSettings | null>, default: null },
  },
  data(): BoardData {
    return {
      state: {
        data: null, error: false, loading: true, receivedAt: 0, nextAt: 0,
      },
      pageLabel: readOption(storage(), 'pageLabel', ['url', 'title'], 'url'),
      mapCountry: '',
      mapChosen: false,
      listWindow: readOption(storage(), 'window', LIST_WINDOWS, '30'),
      moreTab: readOption(
        storage(),
        'moreTab',
        ['events', 'downloads', 'outlinks', 'search'],
        'events',
      ),
      now: Date.now(),
      fullscreen: false,
      inWidget: false,
      unsubscribe: null,
      ticker: null,
      logColumns: 3,
      mapColumns: 3,
      resizeObserver: null,
    };
  },
  mounted() {
    // the date does not matter for live data: hide the period selector like Matomo's own real-time
    // pages, but not on a dashboard, where the other widgets need it
    this.inWidget = inWidget(this.$el as HTMLElement);
    if (!this.inWidget) {
      Matomo.postEvent('hidePeriodSelector');
    }
    this.unsubscribe = sharedPoller(Number(this.idSite), 0).subscribe((state) => {
      this.state = state;
    });
    this.ticker = window.setInterval(() => {
      this.now = Date.now();
    }, 1000);
    document.addEventListener('fullscreenchange', this.onFullscreenChange);
    this.measureGrid();
    if (typeof ResizeObserver === 'function') {
      this.resizeObserver = new ResizeObserver(() => this.measureGrid());
      this.resizeObserver.observe(this.$refs.grid as HTMLElement);
    }
  },
  beforeUnmount() {
    if (this.unsubscribe) {
      this.unsubscribe();
    }
    if (this.ticker !== null) {
      window.clearInterval(this.ticker);
    }
    document.removeEventListener('fullscreenchange', this.onFullscreenChange);
    if (this.resizeObserver) {
      this.resizeObserver.disconnect();
    }
  },
  watch: {
    /** the map opens on the country with the most visits, once there are visits */
    topCountry(code: string) {
      if (!this.mapChosen && this.mapCountry === '' && code !== '') {
        this.mapCountry = code;
      }
    },
    pageLabel(value: string) {
      writeOption(storage(), 'pageLabel', value);
    },
    listWindow(value: string) {
      writeOption(storage(), 'window', value);
    },
    moreTab(value: string) {
      writeOption(storage(), 'moreTab', value);
    },
  },
  computed: {
    show(): Sections {
      return sectionsOf(this.variant);
    },
    language(): string {
      return pageLanguage();
    },
    fast(): FastPayload {
      return this.state.data?.payload.fast || {};
    },
    slow(): SlowPayload {
      return this.state.data?.payload.slow || {};
    },
    today(): TodayPayload {
      return this.state.data?.payload.today || {};
    },
    status(): BoardStatus {
      return boardStatus(this.state);
    },
    statusText(): string {
      const { data } = this.state;
      switch (this.status.kind) {
        case 'loading':
          return this.tr('Loading');
        case 'offline':
          return this.tr('Offline', retryInSeconds(this.state.nextAt, this.now));
        case 'delayed':
        case 'stale':
          return this.tr('Delayed', ageText(data?.lag ?? 0, plainTranslate));
        default:
          return this.tr('Updated', timeOfDay(data?.generatedAt ?? 0, this.language));
      }
    },
    canFullscreen(): boolean {
      return typeof document !== 'undefined' && !!document.fullscreenEnabled;
    },
    windowLabel(): string {
      return this.tr('WindowMinutes', this.listWindow);
    },
    sparkValues(): number[] {
      return sparkFor(this.slow.visitSparkline || [], this.listWindow);
    },
    sparkTop(): number {
      return scaleMax(Math.max(0, ...this.sparkValues));
    },
    sparkTitles(): string[] {
      const minutes = barMinutes(this.sparkValues.length, sparkEnd(this.state.data));
      return this.sparkValues.map((value, i) => this.tr(
        i === minutes.length - 1 ? 'BarTitleRunning' : 'BarTitle',
        timeOfDay(minutes[i], this.language).substring(0, 5),
        this.number(value),
      ));
    },
    sparkTimeLabels(): string[] {
      const minutes = this.sparkMinutes;
      return [
        this.tr('MinutesAgo', minutes),
        this.tr('MinutesAgo', Math.round(minutes / 2)),
        this.tr('WindowNow'),
      ];
    },
    sparkMinutes(): number {
      return Math.max(30, Number(this.listWindow));
    },
    sparkBars(): Bar[] {
      return bars(this.sparkValues, this.sparkTop);
    },
    sparkSummary(): string {
      const values = this.sparkValues;
      const total = values.reduce((sum, v) => sum + v, 0);
      const busiest = Math.max(0, ...values);
      return this.tr(
        'VisitsPerMinuteSummary',
        this.sparkMinutes,
        this.number(total),
        this.number(busiest),
      );
    },
    visitsEstimated(): boolean {
      return (this.slow.kVisits || 1) > 1;
    },
    todayNote(): string {
      // the today counters are computed every 5 minutes (the biggest read): say how old they are
      const age = this.state.data?.ages.today;
      if (age === null || age === undefined || age <= 60 || !this.state.data) {
        return '';
      }
      const time = timeOfDay(this.state.data.generatedAt - age, this.language);
      return this.tr('AsOf', time.substring(0, 5));
    },
    kpis(): Kpi[] {
      const w = this.listWindow;
      const revenue = inWindow(this.slow.revenue, w, 0);
      const kpis: Kpi[] = [
        { label: this.tr('VisitsWindow', w), value: this.number(inWindow(this.slow.visits, w, 0)) },
        {
          label: this.tr('PageviewsWindow', w),
          value: this.number(inWindow(this.slow.pageviews, w, 0)),
        },
        {
          label: this.tr('ConversionsWindow', w),
          value: this.number(inWindow(this.slow.conversions, w, 0)),
        },
      ];
      if (revenue > 0 || (this.today.revenue || 0) > 0) {
        kpis.push({ label: this.tr('RevenueWindow', w), value: this.money(revenue) });
      }
      kpis.push({
        label: this.tr('VisitsToday'),
        value: this.number(this.today.visits),
        note: this.todayNote,
        title: this.tr('TodayHelp'),
      });
      if ((this.today.revenue || 0) > 0) {
        kpis.push({
          label: this.tr('RevenueToday'),
          value: this.money(this.today.revenue),
          note: this.todayNote,
          title: this.tr('TodayHelp'),
        });
      }
      return kpis;
    },
    logRows(): LogRow[] {
      const { data } = this.state;
      if (!data || !this.fast.newestVisits) {
        return [];
      }
      const seen: Record<string, number> = {};
      return this.fast.newestVisits.map((visit) => {
        const page = this.pageLabel === 'title' ? visit.title : pathLabel(visit.page);
        // stable while the visit is unchanged; a visit with a new action is a new row
        const base = [
          visit.time, visit.country, visit.source, visit.device, visit.page, visit.actions,
        ].join('|');
        seen[base] = (seen[base] || 0) + 1;
        return {
          key: `${base}|${seen[base]}`,
          duration: durationText(visit.duration || 0, plainTranslate),
          country: visit.countryLabel,
          source: visit.source || visit.channel,
          channel: visit.channel,
          device: visit.device,
          page,
          pageTitle: this.pageLabel === 'title' ? visit.page : visit.title,
          actions: this.number(visit.actions),
          icons: visit.icons || [],
        };
      });
    },
    mapRegions(): MapRegionRow[] {
      return inWindow(this.slow.regions, this.listWindow, [] as MapRegionRow[]);
    },
    /** the countries list opens only countries the map knows (not Matomo's "unknown") */
    mapCountryIds(): string[] {
      return this.list(this.slow.countries).map((row) => String(row.id))
        .filter((id) => iso3(id) !== '');
    },
    topCountry(): string {
      const top = this.list(this.slow.countries).find((row) => iso3(String(row.id)) !== '');
      return top ? String(top.id).toLowerCase() : '';
    },
    mapCountries(): MapCountryRow[] {
      return this.list(this.slow.countries).map((row) => ({
        code: String(row.id),
        label: row.label,
        count: row.count,
        title: this.tr('MapVisits', row.label, this.number(row.count)),
      }));
    },
    mapCities(): MapCityRow[] {
      return inWindow(this.slow.cities, this.listWindow, [] as City[]).map((city) => ({
        key: `${city.country}|${city.region}|${city.city}`,
        country: city.country,
        name: city.city,
        lat: city.lat,
        lon: city.lon,
        count: city.count,
        title: this.tr(
          'MapVisits',
          city.city ? `${city.city}, ${city.countryLabel}` : city.countryLabel,
          this.number(city.count),
        ),
      }));
    },
    mapTexts(): MapTexts {
      return {
        world: this.tr('MapWorld'),
        noMap: this.tr('MapNoMap'),
        noRegions: this.tr('MapNoRegions'),
        legend: this.tr('MapLegend'),
        legendCities: this.tr('MapLegendCities', this.cityMinVisits),
        hint: this.tr('MapHint'),
      };
    },
    logHeads(): Record<string, string> {
      return {
        duration: this.tr('LogDuration'),
        country: this.tr('LogCountry'),
        source: this.tr('LogSource'),
        device: this.tr('LogDevice'),
        page: this.tr('LogPage'),
        actions: this.tr('LogActions'),
        marks: this.tr('LogMarks'),
      };
    },
    pagesNowRows(): Row[] {
      return pagesNow(this.state.data, this.pageLabel);
    },
    topPageRows(): Row[] {
      return topPages(this.state.data, this.pageLabel, this.listWindow);
    },
    goalRows(): Array<{ key: string; label: string; count: string; money: string }> {
      const w = this.listWindow;
      const goals: GoalRow[] = inWindow(this.slow.goals, w, []);
      const rows = goals.map((goal) => ({
        key: `goal${goal.id}`,
        label: goal.label,
        count: this.number(goal.conversions),
        money: goal.revenue > 0 ? this.money(goal.revenue) : '',
      }));
      const orders = inWindow(this.slow.orders, w, 0);
      if (orders > 0) {
        rows.push({
          key: 'orders',
          label: this.tr('Orders'),
          count: this.number(orders),
          money: this.money(inWindow(this.slow.orderRevenue, w, 0)),
        });
      }
      return rows;
    },
    moreRows(): Row[] {
      switch (this.moreTab) {
        case 'downloads':
          return this.list(this.slow.downloads);
        case 'outlinks':
          return this.list(this.slow.outlinks);
        case 'search':
          return this.list(this.slow.siteSearch);
        default:
          return this.list(this.slow.eventCategories);
      }
    },
    pageLabelOptions(): Array<{ value: string; label: string }> {
      return [
        { value: 'url', label: this.tr('ByUrl') },
        { value: 'title', label: this.tr('ByTitle') },
      ];
    },
    windowOptions(): Array<{ value: string; label: string }> {
      return LIST_WINDOWS.map((w) => ({ value: w, label: this.tr('Minutes', w) }));
    },
    moreOptions(): Array<{ value: string; label: string }> {
      return [
        { value: 'events', label: this.tr('Events') },
        { value: 'downloads', label: this.tr('Downloads') },
        { value: 'outlinks', label: this.tr('Outlinks') },
        { value: 'search', label: this.tr('SiteSearch') },
      ];
    },
  },
  methods: {
    feedbackShown(settings: unknown, inAWidget: boolean): boolean {
      return feedbackShown(settings, inAWidget);
    },
    mapVisits(label: string, count: number): string {
      return this.tr('MapVisits', label, this.number(count));
    },
    /** Matomo has no map of the country: the one opened by itself gives way to the world map. */
    onMapMissing(code: string) {
      if (!this.mapChosen && code === this.mapCountry) {
        this.mapCountry = '';
        this.mapChosen = true;
      }
    },
    /** A country chosen on the world map or in the countries list ('' for the world map). */
    openCountry(code: string | number) {
      this.mapCountry = String(code).toLowerCase();
      this.mapChosen = true;
    },
    tr(key: string, ...values: Array<string | number>): string {
      return translate(`FeinwerkLiveDashboard_${key}`, ...values.map(String));
    },
    /** when a tier was computed (its data time): a list compares with its last computation */
    revision(tier: 'fast' | 'slow'): string {
      const { data } = this.state;
      const age = data?.ages[tier];
      return data && age !== null && age !== undefined ? String(data.generatedAt - age) : '';
    },
    list(map?: ByWindow<Row[]>): Row[] {
      return inWindow(map, this.listWindow, []);
    },
    number(value?: number): string {
      return value === undefined ? '0' : formatNumber(value, this.language);
    },
    money(value?: number): string {
      return formatMoney(value || 0, this.currencySymbol, this.language);
    },
    retry() {
      sharedPoller(Number(this.idSite), 0).refresh();
    },
    toggleFullscreen() {
      if (document.fullscreenElement) {
        document.exitFullscreen().catch(() => undefined);
      } else {
        (this.$el as HTMLElement).requestFullscreen().catch(() => undefined);
      }
    },
    measureGrid() {
      const grid = this.$refs.grid as HTMLElement | undefined;
      if (grid) {
        const template = window.getComputedStyle(grid).gridTemplateColumns;
        this.logColumns = logSpan(gridColumnCount(template));
        this.mapColumns = mapSpan(gridColumnCount(template));
      }
    },
    onFullscreenChange() {
      this.fullscreen = document.fullscreenElement === this.$el;
    },
  },
});
</script>
