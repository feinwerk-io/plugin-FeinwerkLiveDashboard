/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { ISO3 } from './countryCodes';

/**
 * The live map draws on Matomo's own maps (plugins/UserCountryMap/svg): world.svg and one map per
 * country with its regions (DEU.svg). The files differ between Matomo versions, so nothing about
 * them is fixed here: projection, view and crop come from each file's metadata. The world uses the
 * Natural Earth projection, the countries Lambert's azimuthal equal-area projection.
 */
export interface MapProjection {
  id: 'naturalearth' | 'laea';
  lon0: number;
  lat0: number;
}

export interface MapView {
  width: number;
  height: number;
  padding: number;
  x: number;
  y: number;
  bboxWidth: number;
  bboxHeight: number;
  proj: MapProjection;
}

export interface MapCountry {
  iso: string;
  name: string;
  d: string;
}

export interface MapRegion {
  /** the region code Matomo stores (location_region), e.g. BY */
  code: string;
  name: string;
  d: string;
}

/** A map file: the world's countries, or a country's regions with its neighbours as context. */
export interface WorldMap {
  view: MapView;
  countries: MapCountry[];
  context: MapCountry[];
  regions: MapRegion[];
}

// coefficients of the Natural Earth projection (Savric, Jenny, Patterson, Petrovic, Hurni 2011)
const A = [0.8707, -0.131979, -0.013791, 0.003971, -0.001529];
const B = [1.007226, 0.015085, -0.044475, 0.028874, -0.005916];

/** Projected like the map file: 180 units per radian, centred on 500, 270, y pointing down. */
function naturalEarth(lon: number, lat: number): [number, number] {
  const lambda = (lon * Math.PI) / 180;
  const phi = (-lat * Math.PI) / 180;
  const p2 = phi * phi;
  const p4 = p2 * p2;
  const x = 180 * lambda * (A[0] + p2 * (A[1] + p2 * (A[2] + p4 * p2 * (A[3] + p2 * A[4]))));
  const y = 180 * phi * (B[0] + p2 * (B[1] + p4 * (B[2] + B[3] * p2 + B[4] * p4)));
  return [x + 500, y + 270];
}

/** Lambert azimuthal equal-area around lon0, lat0, as Matomo's country maps (radius 1000). */
function laea(lon: number, lat: number, lon0: number, lat0: number): [number, number] {
  const r = 1000;
  const phi = (lat * Math.PI) / 180;
  const dLambda = ((lon - lon0) * Math.PI) / 180;
  const phi0 = (lat0 * Math.PI) / 180;
  const k = Math.sqrt(
    2 / (1 + Math.sin(phi0) * Math.sin(phi) + Math.cos(phi0) * Math.cos(phi) * Math.cos(dLambda)),
  ) * 0.5 * Math.SQRT2;
  const x = r * k * Math.cos(phi) * Math.sin(dLambda);
  const y = -r * k * (Math.cos(phi0) * Math.sin(phi)
    - Math.sin(phi0) * Math.cos(phi) * Math.cos(dLambda));
  return [r + x, r + y];
}

/** A place in the map file's own coordinates (its viewBox). */
export function project(view: MapView, lon: number, lat: number): [number, number] {
  const { proj } = view;
  const [px, py] = proj.id === 'laea'
    ? laea(lon, lat, proj.lon0, proj.lat0)
    : naturalEarth(lon, lat);
  const scale = Math.min(
    (view.width - 2 * view.padding) / view.bboxWidth,
    (view.height - 2 * view.padding) / view.bboxHeight,
  );
  const left = 0.5 * (view.width - view.bboxWidth * scale);
  const top = 0.5 * (view.height - view.bboxHeight * scale);
  return [(px - view.x) * scale + left, (py - view.y) * scale + top];
}

/** The map's code of a Matomo country code; '' for Matomo's special codes (unknown, Europe...). */
export function iso3(code: string): string {
  return ISO3[code.toLowerCase()] || '';
}

/** Matomo's country code of a map code; '' for areas without one (Somaliland, N. Cyprus). */
export function iso2(code: string): string {
  const found = Object.keys(ISO3).find((key) => ISO3[key] === code.toUpperCase());
  return found || '';
}

function numberOf(el: Element, name: string): number {
  const value = el.getAttribute(name);
  return value === null || value === '' ? NaN : Number(value);
}

/** The paths of one group of the file (countries, context, regions). */
function groupPaths(doc: Document, id: string): Element[] {
  const group = Array.from(doc.getElementsByTagName('g')).find((g) => g.getAttribute('id') === id);
  return group ? Array.from(group.getElementsByTagName('path')) : [];
}

function attr(el: Element, name: string): string {
  return el.getAttribute(name) || '';
}

/** Projection, view and paths of a map file; null when the file cannot be used. */
export function parseWorldMap(svg: string): WorldMap | null {
  if (!svg) {
    return null;
  }
  let doc: Document;
  try {
    doc = new DOMParser().parseFromString(svg, 'image/svg+xml');
  } catch (e) {
    return null;
  }
  if (doc.getElementsByTagName('parsererror').length) {
    return null;
  }
  const viewEl = doc.getElementsByTagName('view')[0];
  const bboxEl = doc.getElementsByTagName('bbox')[0];
  const projEl = doc.getElementsByTagName('proj')[0];
  const projId = projEl ? projEl.getAttribute('id') : '';
  if (!viewEl || !bboxEl || !projEl || (projId !== 'naturalearth' && projId !== 'laea')) {
    return null;
  }
  const proj: MapProjection = {
    id: projId,
    lon0: Number(projEl.getAttribute('lon0') || 0),
    lat0: Number(projEl.getAttribute('lat0') || 0),
  };
  if (!Number.isFinite(proj.lon0) || !Number.isFinite(proj.lat0)
    || (proj.id === 'naturalearth' && proj.lon0 !== 0)) {
    return null;
  }
  const padding = numberOf(viewEl, 'padding');
  const view: MapView = {
    width: numberOf(viewEl, 'w'),
    height: numberOf(viewEl, 'h'),
    padding: Number.isFinite(padding) ? padding : 0,
    x: numberOf(bboxEl, 'x'),
    y: numberOf(bboxEl, 'y'),
    bboxWidth: numberOf(bboxEl, 'w'),
    bboxHeight: numberOf(bboxEl, 'h'),
    proj,
  };
  const numbers = [view.width, view.height, view.padding, view.x, view.y, view.bboxWidth,
    view.bboxHeight];
  if (!numbers.every(Number.isFinite) || view.bboxWidth <= 0 || view.bboxHeight <= 0) {
    return null;
  }
  const country = (path: Element): MapCountry => ({
    iso: attr(path, 'data-iso'), name: attr(path, 'data-name'), d: attr(path, 'd'),
  });
  const countries = groupPaths(doc, 'countries').map(country).filter((c) => c.d !== '');
  const context = groupPaths(doc, 'context').map(country).filter((c) => c.d !== '');
  // Matomo 6 names the region code in data-region, Matomo 5 in data-p
  const regions = groupPaths(doc, 'regions').map((path) => ({
    code: attr(path, 'data-region') || attr(path, 'data-p'),
    name: attr(path, 'data-name'),
    d: attr(path, 'd'),
  })).filter((region) => region.d !== '');
  return countries.length || regions.length ? {
    view, countries, context, regions,
  } : null;
}

/** Fill of a country: its share of the busiest one; never invisible when it has visits. */
export function shade(count: number, max: number): number {
  if (count <= 0 || max <= 0) {
    return 0;
  }
  return 0.15 + 0.75 * Math.sqrt(Math.min(1, count / max));
}

/** A city's bubble: 3 to 12 screen pixels, area by visits, in map units at the drawn size. */
export function bubbleRadius(count: number, max: number, perPixel: number): number {
  const share = max > 0 ? Math.min(1, Math.max(0, count) / max) : 0;
  return (3 + 9 * Math.sqrt(share)) * perPixel;
}

/** A rectangle in map units. */
export interface Box {
  x: number;
  y: number;
  w: number;
  h: number;
}

/** The box around some paths (a country's regions); null without points. */
export function pathsBox(paths: string[]): Box | null {
  let x1 = Infinity;
  let y1 = Infinity;
  let x2 = -Infinity;
  let y2 = -Infinity;
  paths.forEach((d) => {
    const n = (d.match(/-?\d+(?:\.\d+)?/g) || []).map(Number);
    for (let i = 0; i + 1 < n.length; i += 2) {
      x1 = Math.min(x1, n[i]);
      x2 = Math.max(x2, n[i]);
      y1 = Math.min(y1, n[i + 1]);
      y2 = Math.max(y2, n[i + 1]);
    }
  });
  return Number.isFinite(x1) ? {
    x: x1, y: y1, w: x2 - x1, h: y2 - y1,
  } : null;
}

export function inside(box: Box, x: number, y: number): boolean {
  return x >= box.x && x <= box.x + box.w && y >= box.y && y <= box.y + box.h;
}

/**
 * Map units per screen pixel of a drawing fitted into its box (preserveAspectRatio meet): the side
 * that limits the drawing decides. 1 before the box is measured.
 */
export function unitsPerPixel(
  viewWidth: number, viewHeight: number, boxWidth: number, boxHeight: number,
): number {
  if (boxWidth <= 0 || boxHeight <= 0) {
    return 1;
  }
  return Math.max(viewWidth / boxWidth, viewHeight / boxHeight);
}

/**
 * Where a city's name goes: right of its bubble, or left of it (text-anchor end) where it would
 * leave the map; when it fits on neither side, the side with more room. The width is estimated
 * (0.6 of the font size per letter): enough for that.
 */
export function labelAt(
  x: number, r: number, text: string, size: number, perPixel: number, mapWidth: number,
): { x: number; anchor: 'start' | 'end'; width: number } {
  const gap = 4 * perPixel;
  const width = text.length * size * 0.6 * perPixel;
  const right = mapWidth - (x + r + gap);
  const left = x - r - gap;
  return width > right && left > right
    ? { x: x - r - gap, anchor: 'end', width }
    : { x: x + r + gap, anchor: 'start', width };
}

export interface PlacedLabel {
  key: string;
  x: number;
  y: number;
  anchor: 'start' | 'end';
  text: string;
}

/**
 * Names for the biggest cities first (the list comes sorted by visits); a name that would cover one
 * already placed is left out, so names never overlap. At most max names.
 */
export function placeLabels(
  bubbles: Array<{ key: string; x: number; y: number; r: number; name: string }>,
  size: number,
  perPixel: number,
  mapWidth: number,
  max: number,
): PlacedLabel[] {
  const boxes: Box[] = [];
  const placed: PlacedLabel[] = [];
  bubbles.filter((b) => b.name !== '').forEach((b) => {
    if (placed.length >= max) {
      return;
    }
    const at = labelAt(b.x, b.r, b.name, size, perPixel, mapWidth);
    const height = size * perPixel;
    const y = b.y + 0.35 * height;
    const box = {
      x: at.anchor === 'start' ? at.x : at.x - at.width,
      y: y - height * 0.8,
      w: at.width,
      h: height,
    };
    const overlaps = boxes.some((o) => box.x < o.x + o.w && o.x < box.x + box.w
      && box.y < o.y + o.h && o.y < box.y + box.h);
    if (!overlaps) {
      boxes.push(box);
      placed.push({
        key: b.key, x: at.x, y, anchor: at.anchor, text: b.name,
      });
    }
  });
  return placed;
}
