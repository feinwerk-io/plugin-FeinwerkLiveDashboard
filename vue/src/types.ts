/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/** A row of a list as the API formats it: plain text label, never HTML. */
export interface Row {
  id: number | string;
  label: string;
  count: number;
}

export interface GoalRow {
  id: number;
  label: string;
  conversions: number;
  revenue: number;
}

/** A visit of the live log: aggregated attributes only, no IP, visitor ID or city. */
export interface NewestVisit {
  time: number;
  country: string;
  countryLabel: string;
  channel: string;
  source: string;
  device: string;
  page: string;
  title: string;
  actions: number;
  /** keys of the icon places this visit fills (own ones and those of other plugins) */
  icons: string[];
  /** seconds from the first to the last action of the visit */
  duration?: number;
}

/** A city of the map: rounded coordinates, visits in the list window. */
export interface City {
  country: string;
  countryLabel: string;
  region: string;
  city: string;
  lat: number;
  lon: number;
  count: number;
}

/** A place of the live log's icon column: Matomo's own image or one of another plugin. */
export interface LogIcon {
  key: string;
  src: string;
  label: string;
}

export interface FastPayload {
  lag?: number;
  activeNow?: number;
  visits5?: number;
  actions5?: number;
  conversions5?: number;
  revenue5?: number;
  pagesNow?: { urls: Row[]; titles: Row[] };
  channelsNow?: Row[];
  countriesNow?: Row[];
  /** only while the website's visits log is on */
  newestVisits?: NewestVisit[];
  k?: number;
  partial?: boolean;
}

/** The list windows a viewer can choose, in minutes. */
export type ListWindow = '3' | '10' | '30' | '60';

/** A value per list window. */
export type ByWindow<T> = Partial<Record<ListWindow, T>>;

export interface SlowPayload {
  /** end of the counted minutes (Unix time): earlier than the computation while the reader lags */
  end?: number;
  visits?: ByWindow<number>;
  convertedVisits?: ByWindow<number>;
  channels?: ByWindow<Row[]>;
  campaigns?: ByWindow<Row[]>;
  /** only with the plugin MarketingCampaignsReporting (campaign source, mtm_source) */
  campaignSources?: ByWindow<Row[]>;
  countries?: ByWindow<Row[]>;
  devices?: ByWindow<Row[]>;
  /** cities with at least city_min_visits visits each; empty in reduced detail (level 2 and up) */
  cities?: ByWindow<City[]>;
  /** visits per region (all visits with a known region, no floor) */
  regions?: ByWindow<Array<{ country: string; region: string; count: number }>>;
  actions?: ByWindow<number>;
  pageviews?: ByWindow<number>;
  sparkline?: number[];
  /** new visits per minute (by their first action), oldest first */
  visitSparkline?: number[];
  topPages?: ByWindow<Row[]>;
  topTitles?: ByWindow<Row[]>;
  downloads?: ByWindow<Row[]>;
  outlinks?: ByWindow<Row[]>;
  siteSearch?: ByWindow<Row[]>;
  eventCategories?: ByWindow<Row[]>;
  eventActions?: ByWindow<Row[]>;
  conversions?: ByWindow<number>;
  revenue?: ByWindow<number>;
  orders?: ByWindow<number>;
  orderRevenue?: ByWindow<number>;
  goals?: ByWindow<GoalRow[]>;
  kVisits?: number;
  kActions?: number;
  partial?: boolean;
}

export interface TodayPayload {
  since?: number;
  visits?: number;
  actions?: number;
  conversions?: number;
  revenue?: number;
  partial?: boolean;
}

export interface SnapshotAnswer {
  payload: { fast?: FastPayload; slow?: SlowPayload; today?: TodayPayload };
  ages: { fast: number | null; slow: number | null; today: number | null };
  level: number;
  version: string;
  nextPollSeconds: number;
  notModified: boolean;
  /** measured lag of the reader database in seconds */
  lag?: number;
  delayed: boolean;
  stale: boolean;
  generatedAt: number;
  computedMs: Record<string, number>;
  logIcons?: LogIcon[];
}

export type Translate = (key: string, ...values: Array<string | number>) => string;
