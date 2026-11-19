/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Facts of Matomo's world map (plugins/UserCountryMap/svg/world.svg), taken on 2026-11-18 from
 * Matomo 5.0.0 (same file up to 5.14) and Matomo 6.0.0-rc1: the view metadata, the bounding boxes
 * of some countries' paths (x1, y1, x2, y2) and every data-iso value.
 */
import { MapView } from '../../src/geo';

export const MATOMO5 = {
  view: {
    width: 1000.0,
    height: 525.010727376,
    padding: 0.03,
    x: 95.31,
    y: -3.13,
    bboxWidth: 884.64,
    bboxHeight: 463.56,
    proj: { id: 'naturalearth' as const, lon0: 0, lat0: 0 },
  },
  boxes: {
    DEU: [473.46, 112.9, 497.4, 138.59],
    AUS: [793.38, 346.4, 914.57, 463.94],
    BRA: [229.77, 289.51, 349.99, 429.07],
    USA: [33.79, 60.67, 271.16, 217.84],
    JPN: [837.01, 145.3, 865.15, 196.53],
    ZAF: [506.34, 387.38, 555.59, 432.81],
  } as Record<string, number[]>,
  isos: [
    'AFG', 'AGO', 'ALB', 'AND', 'ARE', 'ARG', 'ARM', 'ATF', 'AUS', 'AUT', 'AZE', 'BDI', 'BEL',
    'BEN', 'BFA', 'BGD', 'BGR', 'BIH', 'BLR', 'BLZ', 'BOL', 'BRA', 'BRN', 'BTN', 'BWA', 'CAF',
    'CAN', 'CHE', 'CHL', 'CHN', 'CIV', 'CMR', 'COD', 'COG', 'COL', 'CRI', 'CUB', 'CYN', 'CYP',
    'CZE', 'DEU', 'DJI', 'DNK', 'DOM', 'DZA', 'ECU', 'EGY', 'ERI', 'ESP', 'EST', 'ETH', 'FIN',
    'FLK', 'FRA', 'GAB', 'GBR', 'GEO', 'GHA', 'GIN', 'GMB', 'GNB', 'GNQ', 'GRC', 'GRL', 'GTM',
    'GUF', 'GUY', 'HKG', 'HND', 'HRV', 'HTI', 'HUN', 'IDN', 'IND', 'IRL', 'IRN', 'IRQ', 'ISL',
    'ISR', 'ITA', 'JAM', 'JOR', 'JPN', 'KAS', 'KAZ', 'KEN', 'KGZ', 'KHM', 'KOR', 'KOS', 'KWT',
    'LAO', 'LBN', 'LBR', 'LBY', 'LIE', 'LKA', 'LSO', 'LTU', 'LUX', 'LVA', 'MAC', 'MAF', 'MAR',
    'MCO', 'MDA', 'MDG', 'MEX', 'MKD', 'MLI', 'MMR', 'MNE', 'MNG', 'MOZ', 'MRT', 'MWI', 'MYS',
    'NAM', 'NCL', 'NER', 'NGA', 'NIC', 'NLD', 'NOR', 'NPL', 'NZL', 'OMN', 'PAK', 'PAN', 'PER',
    'PHL', 'PNG', 'POL', 'PRK', 'PRT', 'PRY', 'PSE', 'QAT', 'ROU', 'RUS', 'RWA', 'SAH', 'SAU',
    'SDN', 'SEN', 'SLE', 'SLV', 'SOL', 'SOM', 'SRB', 'SSD', 'SUR', 'SVK', 'SVN', 'SWE', 'SWZ',
    'SXM', 'SYR', 'TCD', 'TGO', 'THA', 'TJK', 'TKM', 'TLS', 'TTO', 'TUN', 'TUR', 'TWN', 'TZA',
    'UGA', 'UKR', 'URY', 'USA', 'UZB', 'VEN', 'VNM', 'YEM', 'ZAF', 'ZMB', 'ZWE',
  ],
};

export const MATOMO6 = {
  view: {
    width: 1000.0,
    height: 443.0,
    padding: 0.0,
    x: -5.89,
    y: 13.28,
    bboxWidth: 1013.97,
    bboxHeight: 449.01,
    proj: { id: 'naturalearth' as const, lon0: 0, lat0: 0 },
  },
  boxes: {
    DEU: [512.7, 82.1, 534.1, 105.4],
    AUS: [791.7, 284.6, 900.5, 423.4],
    BRA: [299.8, 236.9, 405.3, 358.9],
    USA: [76.3, 36.7, 919.2, 193.6],
    JPN: [823.3, 110.8, 869.6, 177.2],
    ZAF: [541.6, 322.6, 589.9, 399.9],
  } as Record<string, number[]>,
  isos: [
    'ABW', 'AFG', 'AGO', 'AIA', 'ALA', 'ALB', 'AND', 'ARE', 'ARG', 'ARM', 'ASM', 'ATF', 'ATG',
    'AUS', 'AUT', 'AZE', 'BDI', 'BEL', 'BEN', 'BES', 'BFA', 'BGD', 'BGR', 'BHR', 'BHS', 'BIH',
    'BLM', 'BLR', 'BLZ', 'BMU', 'BOL', 'BRA', 'BRB', 'BRN', 'BTN', 'BVT', 'BWA', 'CAF', 'CAN',
    'CCK', 'CHE', 'CHL', 'CHN', 'CIV', 'CMR', 'COD', 'COG', 'COK', 'COL', 'COM', 'CPV', 'CRI',
    'CUB', 'CUW', 'CXR', 'CYM', 'CYN', 'CYP', 'CZE', 'DEU', 'DJI', 'DMA', 'DNK', 'DOM', 'DZA',
    'ECU', 'EGY', 'ERI', 'ESH', 'ESP', 'EST', 'ETH', 'FIN', 'FJI', 'FLK', 'FRA', 'FRO', 'FSM',
    'GAB', 'GBR', 'GEO', 'GGY', 'GHA', 'GIB', 'GIN', 'GLP', 'GMB', 'GNB', 'GNQ', 'GRC', 'GRD',
    'GRL', 'GTM', 'GUF', 'GUM', 'GUY', 'HKG', 'HMD', 'HND', 'HRV', 'HTI', 'HUN', 'IDN', 'IMN',
    'IND', 'IOA', 'IOT', 'IRL', 'IRN', 'IRQ', 'ISL', 'ISR', 'ITA', 'JAM', 'JEY', 'JOR', 'JPN',
    'KAS', 'KAZ', 'KEN', 'KGZ', 'KHM', 'KIR', 'KNA', 'KOR', 'KOS', 'KWT', 'LAO', 'LBN', 'LBR',
    'LBY', 'LCA', 'LIE', 'LKA', 'LSO', 'LTU', 'LUX', 'LVA', 'MAC', 'MAF', 'MAR', 'MCO', 'MDA',
    'MDG', 'MDV', 'MEX', 'MHL', 'MKD', 'MLI', 'MLT', 'MMR', 'MNE', 'MNG', 'MNP', 'MOZ', 'MRT',
    'MSR', 'MTQ', 'MUS', 'MWI', 'MYS', 'MYT', 'NAM', 'NCL', 'NER', 'NFK', 'NGA', 'NIC', 'NIU',
    'NLD', 'NOR', 'NPL', 'NRU', 'NZL', 'OMN', 'PAK', 'PAN', 'PCN', 'PER', 'PHL', 'PLW', 'PNG',
    'POL', 'PRI', 'PRK', 'PRT', 'PRY', 'PSE', 'PYF', 'QAT', 'REU', 'ROU', 'RUS', 'RWA', 'SAU',
    'SDN', 'SEN', 'SGP', 'SGS', 'SHN', 'SJM', 'SLB', 'SLE', 'SLV', 'SMR', 'SOL', 'SOM', 'SPM',
    'SRB', 'SSD', 'STP', 'SUR', 'SVK', 'SVN', 'SWE', 'SWZ', 'SXM', 'SYC', 'SYR', 'TCA', 'TCD',
    'TGO', 'THA', 'TJK', 'TKL', 'TKM', 'TLS', 'TON', 'TTO', 'TUN', 'TUR', 'TUV', 'TWN', 'TZA',
    'UGA', 'UKR', 'URY', 'USA', 'UZB', 'VCT', 'VEN', 'VGB', 'VIR', 'VNM', 'VUT', 'WLF', 'WSM',
    'YEM', 'ZAF', 'ZMB', 'ZWE',
  ],
};

/** Country maps (plugins/UserCountryMap/svg/<ISO3>.svg): view, some regions, region codes. */
interface CountryFixture {
  view: MapView;
  boxes: Record<string, number[]>;
  codes: string[];
}

export const COUNTRIES5: Record<string, CountryFixture> = {
  DEU: {
    view: {
      width: 450.0,
      height: 451.0,
      padding: 0.06,
      x: 982.53,
      y: 950.78,
      bboxWidth: 79.29,
      bboxHeight: 104.32,
      proj: { id: 'laea', lon0: 7.55593198638, lat0: 51.423631497 },
    },
    boxes: {
      BE: [309.0, 139.0, 329.0, 155.0],
      BY: [178.0, 256.0, 349.0, 431.0],
      HH: [198.0, 89.0, 217.0, 105.0],
    },
    codes: [
      'BB', 'BE', 'BW', 'BY', 'HB', 'HE', 'HH', 'MV', 'NI', 'NW', 'RP', 'SH', 'SL', 'SN', 'ST',
      'TH',
    ],
  },
  USA: {
    view: {
      width: 450.0,
      height: 376.0,
      padding: 0.06,
      x: 731.67,
      y: 825.72,
      bboxWidth: 567.54,
      bboxHeight: 374.4,
      proj: { id: 'laea', lon0: -98.606, lat0: 39.622 },
    },
    boxes: {
      NY: [348.0, 99.0, 405.0, 143.0],
      CA: [24.0, 127.0, 88.0, 233.0],
      TX: [146.0, 206.0, 255.0, 311.0],
    },
    codes: [
      'AL', 'AR', 'AZ', 'CA', 'CO', 'CT', 'DC', 'DE', 'FL', 'GA', 'IA', 'ID', 'IL', 'IN', 'KS',
      'KY', 'LA', 'MA', 'MD', 'ME', 'MI', 'MN', 'MO', 'MS', 'MT', 'NC', 'ND', 'NE', 'NH', 'NJ',
      'NM', 'NV', 'NY', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC', 'SD', 'TN', 'TX', 'UT', 'VA', 'VT',
      'WA', 'WI', 'WV', 'WY',
    ],
  },
};
export const COUNTRIES6: Record<string, CountryFixture> = {
  DEU: {
    view: {
      width: 450.0,
      height: 451.0,
      padding: 0.0,
      x: 946.4,
      y: 945.18,
      bboxWidth: 107.92,
      bboxHeight: 107.92,
      proj: { id: 'laea', lon0: 10.38149384343091, lat0: 51.106363486328306 },
    },
    boxes: {
      BE: [309.0, 146.0, 329.0, 163.0],
      BY: [177.0, 257.0, 340.0, 426.0],
      HH: [204.0, 94.0, 222.0, 110.0],
    },
    codes: [
      'BB', 'BE', 'BW', 'BY', 'HB', 'HE', 'HH', 'MV', 'NI', 'NW', 'RP', 'SH', 'SL', 'SN', 'ST',
      'TH',
    ],
  },
  USA: {
    view: {
      width: 528.0,
      height: 301.0,
      padding: 0.0,
      x: 633.2,
      y: 823.71,
      bboxWidth: 666.05,
      bboxHeight: 378.44,
      proj: { id: 'laea', lon0: -98.606, lat0: 39.622 },
    },
    boxes: {
      NY: [426.0, 62.0, 484.0, 107.0],
      CA: [102.0, 90.0, 166.0, 196.0],
      TX: [224.0, 169.0, 334.0, 274.0],
    },
    codes: [
      'AK', 'AL', 'AR', 'AZ', 'CA', 'CO', 'CT', 'DC', 'DE', 'FL', 'GA', 'HI', 'IA', 'ID', 'IL',
      'IN', 'KS', 'KY', 'LA', 'MA', 'MD', 'ME', 'MI', 'MN', 'MO', 'MS', 'MT', 'NC', 'ND', 'NE',
      'NH', 'NJ', 'NM', 'NV', 'NY', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC', 'SD', 'TN', 'TX', 'UT',
      'VA', 'VT', 'WA', 'WI', 'WV', 'WY',
    ],
  },
};
