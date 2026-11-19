/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { Translate } from './types';

/** Matomo's interface language (piwik.language), else the page's lang attribute. */
export function pageLanguage(): string {
  const matomo = typeof window !== 'undefined'
    ? (window as unknown as { piwik?: { language?: string } }).piwik
    : undefined;
  return matomo?.language
    || (typeof document !== 'undefined' && document.documentElement.lang)
    || 'en';
}

export function formatNumber(value: number, language: string): string {
  try {
    return new Intl.NumberFormat(language, { maximumFractionDigits: 0 }).format(value);
  } catch (e) {
    return String(Math.round(value));
  }
}

/**
 * Money with two decimals and the website's currency symbol where the language puts a currency
 * (Matomo gives the symbol, not the ISO code): the position comes from formatting a dollar amount
 * and swapping its symbol.
 */
export function formatMoney(value: number, symbol: string, language: string): string {
  try {
    const parts = new Intl.NumberFormat(language, {
      style: 'currency', currency: 'USD', currencyDisplay: 'narrowSymbol',
    }).formatToParts(value);
    return parts.map((p) => (p.type === 'currency' ? symbol : p.value))
      .join('')
      .replace(/\u00a0/g, ' ');
  } catch (e) {
    return `${value.toFixed(2)} ${symbol}`;
  }
}

/** "12 s ago", "3 min ago", "2 h ago" (translated); empty without an age. */
/** How long a visit has lasted: 45 s, 4 min 20 s, 1 h 5 min. */
export function durationText(seconds: number, translate: Translate): string {
  const s = Math.max(0, Math.floor(seconds));
  if (s < 60) {
    return translate('FeinwerkLiveDashboard_DurationSeconds', s);
  }
  if (s < 3600) {
    return translate('FeinwerkLiveDashboard_DurationMinutes', Math.floor(s / 60), s % 60);
  }
  return translate(
    'FeinwerkLiveDashboard_DurationHours',
    Math.floor(s / 3600),
    Math.floor((s % 3600) / 60),
  );
}

export function ageText(seconds: number | null, translate: Translate): string {
  if (seconds === null) {
    return '';
  }
  if (seconds < 60) {
    return translate('FeinwerkLiveDashboard_AgeSeconds', seconds);
  }
  if (seconds < 3600) {
    return translate('FeinwerkLiveDashboard_AgeMinutes', Math.floor(seconds / 60));
  }
  return translate('FeinwerkLiveDashboard_AgeHours', Math.floor(seconds / 3600));
}

export function timeOfDay(unixSeconds: number, language: string, timeZone?: string): string {
  try {
    return new Intl.DateTimeFormat(language, {
      hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false, timeZone,
    }).format(new Date(unixSeconds * 1000));
  } catch (e) {
    return new Date(unixSeconds * 1000).toISOString().substring(11, 19);
  }
}

/**
 * A page URL as Matomo stores it (host and path, no protocol) shown as its path; the full URL stays
 * in the tooltip. Anything that is no URL (a title, a placeholder) stays as it is.
 */
export function pathLabel(label: string): string {
  const match = /^[a-z0-9.-]+\.[a-z]{2,}(?::\d+)?(\/.*)?$/i.exec(label);
  if (!match) {
    return label;
  }
  return match[1] || '/';
}

/** Bar width in percent of the largest count; a non-zero count shows at least 1 %. */
export function share(count: number, max: number): number {
  if (count <= 0 || max <= 0) {
    return 0;
  }
  return Math.max(1, Math.round((count / max) * 100));
}
