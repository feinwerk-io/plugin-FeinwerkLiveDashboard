/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import {
  ageText, durationText, formatMoney, formatNumber, pageLanguage, pathLabel, share, timeOfDay,
} from '../src/format';

const t = (key: string, ...values: Array<string | number>) => `${key}(${values.join(',')})`;

describe('format', () => {
  it('formats numbers in the page language', () => {
    expect(formatNumber(1234567, 'de')).toBe('1.234.567');
    expect(formatNumber(1234567, 'en')).toBe('1,234,567');
    expect(formatNumber(0, 'de')).toBe('0');
  });

  it('formats money with two decimals and the site currency symbol', () => {
    expect(formatMoney(54.9, '€', 'de')).toBe('54,90 €');
    expect(formatMoney(1234.5, '$', 'en')).toBe('$1,234.50');
  });

  it('tells the age of data in seconds, minutes or hours', () => {
    expect(ageText(null, t)).toBe('');
    expect(ageText(0, t)).toBe('FeinwerkLiveDashboard_AgeSeconds(0)');
    expect(ageText(59, t)).toBe('FeinwerkLiveDashboard_AgeSeconds(59)');
    expect(ageText(60, t)).toBe('FeinwerkLiveDashboard_AgeMinutes(1)');
    expect(ageText(3599, t)).toBe('FeinwerkLiveDashboard_AgeMinutes(59)');
    expect(ageText(7200, t)).toBe('FeinwerkLiveDashboard_AgeHours(2)');
  });

  it('tells how long a visit has lasted, to the second while it is short', () => {
    expect(durationText(0, t)).toBe('FeinwerkLiveDashboard_DurationSeconds(0)');
    expect(durationText(45, t)).toBe('FeinwerkLiveDashboard_DurationSeconds(45)');
    expect(durationText(260, t)).toBe('FeinwerkLiveDashboard_DurationMinutes(4,20)');
    expect(durationText(3900, t)).toBe('FeinwerkLiveDashboard_DurationHours(1,5)');
    expect(durationText(-3, t)).toBe('FeinwerkLiveDashboard_DurationSeconds(0)');
  });

  it('writes the time of day of a Unix time in the browser time zone', () => {
    expect(timeOfDay(1795000000, 'de', 'UTC')).toBe('11:06:40');
  });

  it("takes Matomo's interface language before the page's lang attribute", () => {
    const win = window as unknown as { piwik?: { language?: string } };
    document.documentElement.lang = 'en';
    win.piwik = { language: 'de' };
    expect(pageLanguage()).toBe('de');
    win.piwik = {};
    expect(pageLanguage()).toBe('en');
    document.documentElement.lang = '';
    expect(pageLanguage()).toBe('en');
    delete win.piwik;
  });

  it('shows page URLs as their path, everything else unchanged', () => {
    expect(pathLabel('shop.example/p/1?a=b')).toBe('/p/1?a=b');
    expect(pathLabel('shop.example')).toBe('/');
    expect(pathLabel('shop.example/')).toBe('/');
    expect(pathLabel('Home page')).toBe('Home page');
    expect(pathLabel('(no page view)')).toBe('(no page view)');
    expect(pathLabel('')).toBe('');
  });

  it('gives bar shares against the largest count', () => {
    expect(share(5, 10)).toBe(50);
    expect(share(10, 10)).toBe(100);
    expect(share(0, 0)).toBe(0);
    expect(share(1, 1000)).toBe(1);
  });
});
