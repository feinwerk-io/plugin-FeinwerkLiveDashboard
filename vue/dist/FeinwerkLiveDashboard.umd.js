(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", ], factory);
	else if(typeof exports === 'object')
		exports["FeinwerkLiveDashboard"] = factory(require("CoreHome"), require("vue"));
	else
		root["FeinwerkLiveDashboard"] = factory(root["CoreHome"], root["Vue"]);
})((typeof self !== 'undefined' ? self : this), function(__WEBPACK_EXTERNAL_MODULE__19dc__, __WEBPACK_EXTERNAL_MODULE__8bbf__) {
return /******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "plugins/FeinwerkLiveDashboard/vue/dist/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "fae3");
/******/ })
/************************************************************************/
/******/ ({

/***/ "19dc":
/***/ (function(module, exports) {

module.exports = __WEBPACK_EXTERNAL_MODULE__19dc__;

/***/ }),

/***/ "8bbf":
/***/ (function(module, exports) {

module.exports = __WEBPACK_EXTERNAL_MODULE__8bbf__;

/***/ }),

/***/ "fae3":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// EXPORTS
__webpack_require__.d(__webpack_exports__, "LiveBoard", function() { return /* reexport */ LiveBoard; });

// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js
// This file is imported into lib/wc client bundles.

if (typeof window !== 'undefined') {
  var currentScript = window.document.currentScript
  if (false) { var getCurrentScript; }

  var src = currentScript && currentScript.src.match(/(.+\/)[^/]+\.js(\?.*)?$/)
  if (src) {
    __webpack_require__.p = src[1] // eslint-disable-line
  }
}

// Indicate to webpack that this file can be concatenated
/* harmony default export */ var setPublicPath = (null);

// EXTERNAL MODULE: external {"commonjs":"vue","commonjs2":"vue","root":"Vue"}
var external_commonjs_vue_commonjs2_vue_root_Vue_ = __webpack_require__("8bbf");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkLiveDashboard/vue/src/LiveBoard.vue?vue&type=template&id=5c22a0b6

const _hoisted_1 = {
  class: "fwld-board__bar"
};
const _hoisted_2 = {
  key: 0,
  class: "fwld-board__site"
};
const _hoisted_3 = {
  class: "fwld-board__state"
};
const _hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "fwld-status__dot",
  "aria-hidden": "true"
}, null, -1);
const _hoisted_5 = {
  class: "fwld-status__text"
};
const _hoisted_6 = ["title"];
const _hoisted_7 = {
  key: 1,
  class: "fwld-board__notice"
};
const _hoisted_8 = {
  key: 2,
  class: "fwld-board__window"
};
const _hoisted_9 = {
  class: "fwld-board__window-label"
};
const _hoisted_10 = ["aria-pressed", "aria-label", "title"];
const _hoisted_11 = {
  width: "16",
  height: "16",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  "stroke-width": "2",
  "stroke-linecap": "round",
  "stroke-linejoin": "round",
  "aria-hidden": "true"
};
const _hoisted_12 = {
  key: 0,
  d: "M9 4v5H4M15 4v5h5M9 20v-5H4M15 20v-5h5"
};
const _hoisted_13 = {
  key: 1,
  d: "M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5"
};
const _hoisted_14 = {
  key: 0,
  class: "fwld-board__warning"
};
const _hoisted_15 = {
  ref: "grid",
  class: "fwld-grid"
};
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _ctx$state$data;
  const _component_SegmentedSwitch = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SegmentedSwitch");
  const _component_PulseStrip = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PulseStrip");
  const _component_LiveLog = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("LiveLog");
  const _component_LiveMap = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("LiveMap");
  const _component_TopList = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("TopList");
  const _component_GoalsPanel = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("GoalsPanel");
  const _component_FeedbackBar = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("FeedbackBar");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["fwld-board", {
      'fwld-board--fullscreen': _ctx.fullscreen,
      'fwld-board--part': _ctx.variant !== 'full',
      'fwld-board--in-widget': _ctx.inWidget
    }])
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("header", _hoisted_1, [_ctx.fullscreen ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h2", _hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.siteName), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["fwld-status", `fwld-status--${_ctx.status.kind}`]),
    role: "status"
  }, [_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.statusText), 1), _ctx.status.kind === 'offline' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
    key: 0,
    type: "button",
    class: "fwld-status__retry",
    onClick: _cache[0] || (_cache[0] = (...args) => _ctx.retry && _ctx.retry(...args))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.tr('Retry')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 2), _ctx.techText ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    key: 0,
    class: "fwld-status__tech",
    title: _ctx.tr('TechHelp')
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.techText), 9, _hoisted_6)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), _ctx.status.reduced ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", _hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.tr('ReducedDetail')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.show.windowSwitch ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.tr('ListWindow')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SegmentedSwitch, {
    modelValue: _ctx.listWindow,
    "onUpdate:modelValue": _cache[1] || (_cache[1] = $event => _ctx.listWindow = $event),
    options: _ctx.windowOptions,
    label: _ctx.tr('ListWindow')
  }, null, 8, ["modelValue", "options", "label"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.canFullscreen && _ctx.show.fullscreen ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
    key: 3,
    type: "button",
    class: "fwld-board__fullscreen",
    "aria-pressed": _ctx.fullscreen ? 'true' : 'false',
    "aria-label": _ctx.fullscreen ? _ctx.tr('ExitFullScreen') : _ctx.tr('FullScreen'),
    title: _ctx.fullscreen ? _ctx.tr('ExitFullScreen') : _ctx.tr('FullScreen'),
    onClick: _cache[2] || (_cache[2] = (...args) => _ctx.toggleFullscreen && _ctx.toggleFullscreen(...args))
  }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("svg", _hoisted_11, [_ctx.fullscreen ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("path", _hoisted_12)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("path", _hoisted_13))]))], 8, _hoisted_10)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), _ctx.status.kind === 'stale' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", _hoisted_14, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.tr('Stale')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.show.pulse ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_PulseStrip, {
    key: 1,
    "active-now": _ctx.fast.activeNow || 0,
    "active-label": _ctx.tr('ActiveNow'),
    "active-help": _ctx.tr('ActiveNowHelp', _ctx.activeMinutes),
    bars: _ctx.sparkBars,
    "bar-titles": _ctx.sparkTitles,
    "scale-top": _ctx.number(_ctx.sparkTop),
    "time-labels": _ctx.sparkTimeLabels,
    "spark-label": _ctx.tr('VisitsPerMinute', _ctx.sparkMinutes),
    "spark-summary": _ctx.sparkSummary,
    kpis: _ctx.kpis
  }, null, 8, ["active-now", "active-label", "active-help", "bars", "bar-titles", "scale-top", "time-labels", "spark-label", "spark-summary", "kpis"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_15, [_ctx.show.log && _ctx.fast.newestVisits ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_LiveLog, {
    key: 0,
    style: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeStyle"])({
      gridColumn: _ctx.variant === 'visits' ? '1 / -1' : `span ${_ctx.logColumns}`
    }),
    title: _ctx.tr('LiveLog'),
    "window-label": _ctx.tr('WindowNow'),
    rows: _ctx.logRows,
    heads: _ctx.logHeads,
    icons: ((_ctx$state$data = _ctx.state.data) === null || _ctx$state$data === void 0 ? void 0 : _ctx$state$data.logIcons) || [],
    "empty-text": _ctx.tr('EmptyNow')
  }, null, 8, ["style", "title", "window-label", "rows", "heads", "icons", "empty-text"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.show.map && _ctx.worldMap ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_LiveMap, {
    key: 1,
    style: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeStyle"])({
      gridColumn: `span ${_ctx.mapColumns}`
    }),
    title: _ctx.tr('Map'),
    "window-label": _ctx.windowLabel,
    url: _ctx.worldMap,
    country: _ctx.mapCountry,
    countries: _ctx.mapCountries,
    regions: _ctx.mapRegions,
    cities: _ctx.mapCities,
    texts: _ctx.mapTexts,
    visits: _ctx.mapVisits,
    onSelect: _ctx.openCountry,
    onMissing: _ctx.onMapMissing
  }, null, 8, ["style", "title", "window-label", "url", "country", "countries", "regions", "cities", "texts", "visits", "onSelect", "onMissing"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.show.countries ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_TopList, {
    key: 2,
    title: _ctx.tr('Countries'),
    "window-label": _ctx.windowLabel,
    rows: _ctx.list(_ctx.slow.countries),
    "list-key": `countries:${_ctx.listWindow}`,
    revision: _ctx.revision('slow'),
    estimated: _ctx.visitsEstimated,
    "empty-text": _ctx.tr('Empty'),
    selectable: _ctx.show.map && _ctx.worldMap !== '',
    "selectable-ids": _ctx.mapCountryIds,
    "selected-id": _ctx.mapCountry,
    onSelect: _ctx.openCountry
  }, null, 8, ["title", "window-label", "rows", "list-key", "revision", "estimated", "empty-text", "selectable", "selectable-ids", "selected-id", "onSelect"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.show.pagesNow ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_TopList, {
    key: 3,
    title: _ctx.tr('PagesNow'),
    "window-label": _ctx.tr('WindowNow'),
    rows: _ctx.pagesNowRows,
    paths: _ctx.pageLabel === 'url',
    "list-key": `pagesNow:${_ctx.pageLabel}`,
    revision: _ctx.revision('fast'),
    estimated: (_ctx.fast.k || 1) > 1,
    "empty-text": _ctx.tr('EmptyNow')
  }, {
    controls: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SegmentedSwitch, {
      modelValue: _ctx.pageLabel,
      "onUpdate:modelValue": _cache[3] || (_cache[3] = $event => _ctx.pageLabel = $event),
      options: _ctx.pageLabelOptions,
      label: _ctx.tr('PageLabel')
    }, null, 8, ["modelValue", "options", "label"])]),
    _: 1
  }, 8, ["title", "window-label", "rows", "paths", "list-key", "revision", "estimated", "empty-text"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.show.topPages ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_TopList, {
    key: 4,
    title: _ctx.tr('TopPages'),
    "window-label": _ctx.windowLabel,
    rows: _ctx.topPageRows,
    paths: _ctx.pageLabel === 'url',
    "list-key": `topPages:${_ctx.pageLabel}:${_ctx.listWindow}`,
    revision: _ctx.revision('slow'),
    estimated: (_ctx.slow.kActions || 1) > 1,
    "empty-text": _ctx.tr('Empty')
  }, null, 8, ["title", "window-label", "rows", "paths", "list-key", "revision", "estimated", "empty-text"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.show.channels ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_TopList, {
    key: 5,
    title: _ctx.tr('Channels'),
    "window-label": _ctx.windowLabel,
    rows: _ctx.list(_ctx.slow.channels),
    "list-key": `channels:${_ctx.listWindow}`,
    revision: _ctx.revision('slow'),
    estimated: _ctx.visitsEstimated,
    "empty-text": _ctx.tr('Empty')
  }, null, 8, ["title", "window-label", "rows", "list-key", "revision", "estimated", "empty-text"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.show.campaigns ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_TopList, {
    key: 6,
    title: _ctx.tr('Campaigns'),
    "window-label": _ctx.windowLabel,
    rows: _ctx.list(_ctx.slow.campaigns),
    "list-key": `campaigns:${_ctx.listWindow}`,
    revision: _ctx.revision('slow'),
    estimated: _ctx.visitsEstimated,
    "empty-text": _ctx.tr('EmptyCampaigns')
  }, null, 8, ["title", "window-label", "rows", "list-key", "revision", "estimated", "empty-text"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.show.campaignSources && _ctx.slow.campaignSources ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_TopList, {
    key: 7,
    title: _ctx.tr('CampaignSources'),
    "window-label": _ctx.windowLabel,
    rows: _ctx.list(_ctx.slow.campaignSources),
    "list-key": `campaignSources:${_ctx.listWindow}`,
    revision: _ctx.revision('slow'),
    estimated: _ctx.visitsEstimated,
    "empty-text": _ctx.tr('EmptyCampaigns')
  }, null, 8, ["title", "window-label", "rows", "list-key", "revision", "estimated", "empty-text"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.show.devices ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_TopList, {
    key: 8,
    title: _ctx.tr('Devices'),
    "window-label": _ctx.windowLabel,
    rows: _ctx.list(_ctx.slow.devices),
    "list-key": `devices:${_ctx.listWindow}`,
    revision: _ctx.revision('slow'),
    estimated: _ctx.visitsEstimated,
    "empty-text": _ctx.tr('Empty')
  }, null, 8, ["title", "window-label", "rows", "list-key", "revision", "estimated", "empty-text"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.show.goals ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_GoalsPanel, {
    key: 9,
    title: _ctx.tr('Goals'),
    "window-label": _ctx.windowLabel,
    rows: _ctx.goalRows,
    "empty-text": _ctx.tr('EmptyGoals')
  }, null, 8, ["title", "window-label", "rows", "empty-text"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.show.more ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_TopList, {
    key: 10,
    title: _ctx.tr('MoreActions'),
    "window-label": _ctx.windowLabel,
    rows: _ctx.moreRows,
    paths: _ctx.moreTab === 'downloads',
    "list-key": `more:${_ctx.moreTab}:${_ctx.listWindow}`,
    revision: _ctx.revision('slow'),
    estimated: (_ctx.slow.kActions || 1) > 1,
    "empty-text": _ctx.tr('Empty')
  }, {
    controls: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SegmentedSwitch, {
      modelValue: _ctx.moreTab,
      "onUpdate:modelValue": _cache[4] || (_cache[4] = $event => _ctx.moreTab = $event),
      options: _ctx.moreOptions,
      label: _ctx.tr('MoreActions')
    }, null, 8, ["modelValue", "options", "label"])]),
    _: 1
  }, 8, ["title", "window-label", "rows", "paths", "list-key", "revision", "estimated", "empty-text"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 512), _ctx.feedbackShown(_ctx.feedback, _ctx.inWidget) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_FeedbackBar, {
    key: 2,
    class: "fwld-board__feedback",
    "id-site": Number(_ctx.idSite),
    topic: "board",
    question: _ctx.tr('FeedbackQuestion_board'),
    feedback: _ctx.feedback
  }, null, 8, ["id-site", "question", "feedback"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 2);
}
// CONCATENATED MODULE: ./plugins/FeinwerkLiveDashboard/vue/src/LiveBoard.vue?vue&type=template&id=5c22a0b6

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// CONCATENATED MODULE: ./plugins/FeinwerkLiveDashboard/vue/src/board.ts
const LIST_WINDOWS = ['3', '10', '30', '60'];
/** The value of a list window, or the fallback while that window has none yet. */
function inWindow(map, window, fallback) {
  const value = map === null || map === void 0 ? void 0 : map[window];
  return value === undefined ? fallback : value;
}
/** The minutes the sparkline shows: the chosen window, but at least the last 30 minutes. */
function sparkFor(values, window) {
  const minutes = Math.max(30, Number(window));
  return values.slice(Math.max(0, values.length - minutes));
}
function pagesNow(data, label) {
  var _data$payload$fast;
  const pages = data === null || data === void 0 || (_data$payload$fast = data.payload.fast) === null || _data$payload$fast === void 0 ? void 0 : _data$payload$fast.pagesNow;
  if (!pages) {
    return [];
  }
  return (label === 'title' ? pages.titles : pages.urls) || [];
}
function topPages(data, label, window) {
  const slow = data === null || data === void 0 ? void 0 : data.payload.slow;
  return inWindow(label === 'title' ? slow === null || slow === void 0 ? void 0 : slow.topTitles : slow === null || slow === void 0 ? void 0 : slow.topPages, window, []);
}
/**
 * What the status line says: the connection first, then how fresh the data is, and whether detail
 * is reduced.
 */
function boardStatus(state) {
  var _state$data$level, _state$data;
  const reduced = ((_state$data$level = (_state$data = state.data) === null || _state$data === void 0 ? void 0 : _state$data.level) !== null && _state$data$level !== void 0 ? _state$data$level : 0) > 0;
  if (state.error) {
    return {
      kind: 'offline',
      reduced
    };
  }
  if (!state.data) {
    return {
      kind: 'loading',
      reduced
    };
  }
  if (state.data.stale) {
    return {
      kind: 'stale',
      reduced
    };
  }
  return {
    kind: state.data.delayed ? 'delayed' : 'live',
    reduced
  };
}
/**
 * Sparkline bars in percent of the top of the scale (default: the busiest minute); the last one is
 * the running minute.
 */
function bars(values, top) {
  const max = top !== null && top !== void 0 ? top : Math.max(0, ...values);
  return values.map((value, i) => ({
    height: max > 0 ? Math.round(value / max * 100) : 0,
    value,
    current: i === values.length - 1
  }));
}
/**
 * Seconds since a visit's last action: the server's time of the answer plus the browser time since
 * it arrived.
 */
function visitAge(visitTime, generatedAt, receivedAtMs, nowMs) {
  return Math.max(0, Math.floor(generatedAt + (nowMs - receivedAtMs) / 1000 - visitTime));
}
/**
 * Columns of the live log in the panel grid: at most 3 (Peter, 2026-11-18), never more than the
 * grid has.
 */
function logSpan(gridColumns) {
  return Math.max(1, Math.min(3, gridColumns));
}
/**
 * The map's width in grid columns: one column stays free beside it for the countries list, and the
 * map takes at most 3 (its height is limited, a wider map only adds empty sides).
 */
function mapSpan(gridColumns) {
  return gridColumns >= 3 ? Math.min(3, gridColumns - 1) : Math.max(1, gridColumns);
}
/** The number of columns of a CSS grid from its computed grid-template-columns ("320px ..."). */
function gridColumnCount(template) {
  const parts = template.trim().split(/\s+/).filter(part => /^[\d.]+px$/.test(part));
  return Math.max(1, parts.length);
}
/**
 * The Unix time of the minute each of `count` bars stands for; the last bar is the running minute.
 */
function barMinutes(count, endUnix) {
  const last = Math.floor(endUnix / 60) * 60;
  return Array.from({
    length: count
  }, (_, i) => last - (count - 1 - i) * 60);
}
/** The top of the bar scale: the busiest minute rounded up to a readable number. */
function scaleMax(max) {
  if (max <= 10) {
    return Math.max(0, Math.ceil(max));
  }
  const power = 10 ** Math.floor(Math.log10(max));
  const step = [1, 1.2, 1.5, 2, 2.5, 3, 4, 5, 6, 8, 10].find(s => s * power >= max) || 10;
  return Math.round(step * power);
}
const NONE = {
  pulse: false,
  log: false,
  pagesNow: false,
  topPages: false,
  channels: false,
  campaigns: false,
  campaignSources: false,
  countries: false,
  devices: false,
  goals: false,
  map: false,
  more: false,
  windowSwitch: false,
  fullscreen: false
};
/**
 * What a board variant shows: the page everything; the dashboard widgets (Peter, 2026-11-18: "in 3
 * oder 4 verschiedenen Varianten") a part each. Unknown variants show everything.
 */
function sectionsOf(variant) {
  switch (variant) {
    case 'overview':
      return Object.assign(Object.assign({}, NONE), {}, {
        pulse: true,
        windowSwitch: true
      });
    case 'pages':
      return Object.assign(Object.assign({}, NONE), {}, {
        pagesNow: true,
        topPages: true,
        windowSwitch: true
      });
    case 'sources':
      return Object.assign(Object.assign({}, NONE), {}, {
        channels: true,
        campaigns: true,
        campaignSources: true,
        goals: true,
        windowSwitch: true
      });
    case 'visits':
      return Object.assign(Object.assign({}, NONE), {}, {
        log: true
      });
    case 'map':
      return Object.assign(Object.assign({}, NONE), {}, {
        map: true,
        countries: true,
        windowSwitch: true
      });
    default:
      return Object.fromEntries(Object.keys(NONE).map(key => [key, true]));
  }
}
/** The full live log table needs this width; a narrower panel gets the narrow layout. */
const LOG_FULL_WIDTH = 560;
/** Narrow live log (phones, narrow dashboard columns); unmeasured (0) stays full. */
function logNarrow(width) {
  return width > 0 && width < LOG_FULL_WIDTH;
}
/** The narrow live log puts its icon places into two rows: the columns that takes. */
function markColumns(places) {
  return Math.ceil(Math.max(0, places) / 2);
}
/**
 * Whether the board sits in a widget (dashboard, Widgetize, the widget preview) rather than on its
 * own report page: Matomo wraps each widget outside report pages in an element of class "widget".
 */
function inWidget(el) {
  return !!el && el.closest('.widget') !== null;
}
/** The feedback thumbs: only on the board's own page, never in a dashboard widget (kept quiet). */
function feedbackShown(settings, inAWidget) {
  return !!settings && !inAWidget;
}
/** The end of the minute bars: as the server counted them, else when the minutes were computed. */
function sparkEnd(data) {
  var _data$payload$slow, _data$ages$slow;
  if (!data) {
    return 0;
  }
  if (typeof ((_data$payload$slow = data.payload.slow) === null || _data$payload$slow === void 0 ? void 0 : _data$payload$slow.end) === 'number') {
    return data.payload.slow.end;
  }
  return data.generatedAt - ((_data$ages$slow = data.ages.slow) !== null && _data$ages$slow !== void 0 ? _data$ages$slow : 0);
}
function retryInSeconds(nextAt, now) {
  return Math.max(0, Math.ceil((nextAt - now) / 1000));
}
/**
 * A viewer option (localStorage key fwld.NAME); a missing, unknown or unreadable value gives the
 * fallback.
 */
function readOption(storage, name, allowed, fallback) {
  try {
    var _allowed$find;
    const value = storage.getItem(`fwld.${name}`);
    return (_allowed$find = allowed.find(candidate => candidate === value)) !== null && _allowed$find !== void 0 ? _allowed$find : fallback;
  } catch (e) {
    return fallback;
  }
}
function writeOption(storage, name, value) {
  try {
    storage.setItem(`fwld.${name}`, value);
  } catch (e) {
    // private windows and blocked storage: the option lasts for this page only
  }
}
/**
 * The small technical info after the status (Peter, 2026-11-19, switchable in the plugin
 * settings): how long the last calculations of the newest numbers and of the lists took, and
 * whether the lists are estimated from a sample (the largest factor: 1 of k rows read). '' before
 * the first timed calculation.
 */
function techLine(fast, slow, tr) {
  if (fast.ms === undefined && slow.ms === undefined) {
    return '';
  }
  let times;
  if (fast.ms !== undefined && slow.ms !== undefined) {
    times = tr('TechTimes', fast.ms, slow.ms);
  } else {
    var _fast$ms;
    times = tr('TechTime', (_fast$ms = fast.ms) !== null && _fast$ms !== void 0 ? _fast$ms : slow.ms);
  }
  const k = Math.max(1, fast.k || 1, slow.kVisits || 1, slow.kActions || 1);
  return `${times} · ${k > 1 ? tr('TechSample', k) : tr('TechSampleNone')}`;
}
// CONCATENATED MODULE: ./plugins/FeinwerkLiveDashboard/vue/src/format.ts
/** Matomo's interface language (piwik.language), else the page's lang attribute. */
function pageLanguage() {
  const matomo = typeof window !== 'undefined' ? window.piwik : undefined;
  return (matomo === null || matomo === void 0 ? void 0 : matomo.language) || typeof document !== 'undefined' && document.documentElement.lang || 'en';
}
function formatNumber(value, language) {
  try {
    return new Intl.NumberFormat(language, {
      maximumFractionDigits: 0
    }).format(value);
  } catch (e) {
    return String(Math.round(value));
  }
}
/**
 * Money with two decimals and the website's currency symbol where the language puts a currency
 * (Matomo gives the symbol, not the ISO code): the position comes from formatting a dollar amount
 * and swapping its symbol.
 */
function formatMoney(value, symbol, language) {
  try {
    const parts = new Intl.NumberFormat(language, {
      style: 'currency',
      currency: 'USD',
      currencyDisplay: 'narrowSymbol'
    }).formatToParts(value);
    return parts.map(p => p.type === 'currency' ? symbol : p.value).join('').replace(/\u00a0/g, ' ');
  } catch (e) {
    return `${value.toFixed(2)} ${symbol}`;
  }
}
/** "12 s ago", "3 min ago", "2 h ago" (translated); empty without an age. */
/** How long a visit has lasted: 45 s, 4 min 20 s, 1 h 5 min. */
function durationText(seconds, translate) {
  const s = Math.max(0, Math.floor(seconds));
  if (s < 60) {
    return translate('FeinwerkLiveDashboard_DurationSeconds', s);
  }
  if (s < 3600) {
    return translate('FeinwerkLiveDashboard_DurationMinutes', Math.floor(s / 60), s % 60);
  }
  return translate('FeinwerkLiveDashboard_DurationHours', Math.floor(s / 3600), Math.floor(s % 3600 / 60));
}
function ageText(seconds, translate) {
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
function timeOfDay(unixSeconds, language, timeZone) {
  try {
    return new Intl.DateTimeFormat(language, {
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
      hour12: false,
      timeZone
    }).format(new Date(unixSeconds * 1000));
  } catch (e) {
    return new Date(unixSeconds * 1000).toISOString().substring(11, 19);
  }
}
/**
 * A page URL as Matomo stores it (host and path, no protocol) shown as its path; the full URL stays
 * in the tooltip. Anything that is no URL (a title, a placeholder) stays as it is.
 */
function pathLabel(label) {
  const match = /^[a-z0-9.-]+\.[a-z]{2,}(?::\d+)?(\/.*)?$/i.exec(label);
  if (!match) {
    return label;
  }
  return match[1] || '/';
}
/** Bar width in percent of the largest count; a non-zero count shows at least 1 %. */
function share(count, max) {
  if (count <= 0 || max <= 0) {
    return 0;
  }
  return Math.max(1, Math.round(count / max * 100));
}
// CONCATENATED MODULE: ./plugins/FeinwerkLiveDashboard/vue/src/poller.ts
const FIRST_BACKOFF_MS = 10000;
const MAX_BACKOFF_MS = 300000;
function createPoller(deps) {
  const listeners = new Set();
  let state = {
    data: null,
    error: false,
    loading: false,
    receivedAt: 0,
    nextAt: 0
  };
  let timer = null;
  let inFlight = false;
  let failures = 0;
  let generation = 0;
  let removeVisible = null;
  let waitingForVisible = false;
  function emit(next) {
    state = Object.assign(Object.assign({}, state), next);
    listeners.forEach(listener => listener(state));
  }
  function clearTimer() {
    if (timer !== null) {
      deps.clearTimer(timer);
      timer = null;
    }
  }
  function poll() {
    var _state$data$version, _state$data;
    if (inFlight || listeners.size === 0) {
      return;
    }
    if (deps.isHidden()) {
      waitingForVisible = true;
      return;
    }
    clearTimer();
    inFlight = true;
    const mine = generation;
    emit({
      loading: true
    });
    deps.fetch((_state$data$version = (_state$data = state.data) === null || _state$data === void 0 ? void 0 : _state$data.version) !== null && _state$data$version !== void 0 ? _state$data$version : '').then(answer => {
      if (mine !== generation) {
        return;
      }
      inFlight = false;
      failures = 0;
      const data = answer.notModified && state.data ? Object.assign(Object.assign({}, answer), {}, {
        payload: state.data.payload
      }) : answer;
      const wait = Math.max(1, answer.nextPollSeconds) * 1000;
      // eslint-disable-next-line no-use-before-define
      schedule(wait);
      emit({
        data,
        error: false,
        loading: false,
        receivedAt: deps.clock(),
        nextAt: deps.clock() + wait
      });
    }, () => {
      if (mine !== generation) {
        return;
      }
      inFlight = false;
      failures += 1;
      const wait = Math.min(MAX_BACKOFF_MS, FIRST_BACKOFF_MS * 2 ** (failures - 1));
      // eslint-disable-next-line no-use-before-define
      schedule(wait);
      emit({
        error: true,
        loading: false,
        nextAt: deps.clock() + wait
      });
    });
  }
  function schedule(ms) {
    clearTimer();
    timer = deps.setTimer(() => {
      timer = null;
      poll();
    }, ms);
  }
  function start() {
    generation += 1;
    removeVisible = deps.onVisible(() => {
      if (waitingForVisible) {
        waitingForVisible = false;
        poll();
      }
    });
    poll();
  }
  function stop() {
    generation += 1;
    clearTimer();
    inFlight = false;
    waitingForVisible = false;
    if (removeVisible) {
      removeVisible();
      removeVisible = null;
    }
  }
  return {
    subscribe(listener) {
      listeners.add(listener);
      if (listeners.size === 1) {
        start();
      } else {
        listener(state);
      }
      return () => {
        if (listeners.delete(listener) && listeners.size === 0) {
          stop();
        }
      };
    },
    refresh() {
      if (!inFlight) {
        poll();
      }
    },
    active() {
      return listeners.size > 0;
    }
  };
}
// CONCATENATED MODULE: ./plugins/FeinwerkLiveDashboard/vue/src/livePoller.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/**
 * The poller of a website and live view, shared by every board and widget of the page
 * (window.fwldPollers), so five live widgets on a dashboard still make one request per interval.
 */
function sharedPoller(idSite, view = 0) {
  const key = `${idSite}:${view}`;
  window.fwldPollers = window.fwldPollers || {};
  if (!window.fwldPollers[key]) {
    window.fwldPollers[key] = createPoller({
      fetch: since => external_CoreHome_["AjaxHelper"].post({
        module: 'API',
        method: 'FeinwerkLiveDashboard.getSnapshot',
        idSite,
        view
      }, {
        since
      }, {
        createErrorNotification: false
      }),
      setTimer: (fn, ms) => window.setTimeout(fn, ms),
      clearTimer: handle => window.clearTimeout(handle),
      isHidden: () => document.visibilityState === 'hidden',
      onVisible: fn => {
        const listener = () => {
          if (document.visibilityState !== 'hidden') {
            fn();
          }
        };
        document.addEventListener('visibilitychange', listener);
        return () => document.removeEventListener('visibilitychange', listener);
      },
      clock: () => Date.now()
    });
  }
  return window.fwldPollers[key];
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkLiveDashboard/vue/src/FeedbackBar.vue?vue&type=template&id=2c1a64b6

const FeedbackBarvue_type_template_id_2c1a64b6_hoisted_1 = ["title"];
const FeedbackBarvue_type_template_id_2c1a64b6_hoisted_2 = ["title", "aria-label", "aria-pressed", "disabled", "onClick"];
const FeedbackBarvue_type_template_id_2c1a64b6_hoisted_3 = ["d"];
const FeedbackBarvue_type_template_id_2c1a64b6_hoisted_4 = {
  class: "fwld-feedback__thanks",
  role: "status"
};
const FeedbackBarvue_type_template_id_2c1a64b6_hoisted_5 = ["href", "title"];
const FeedbackBarvue_type_template_id_2c1a64b6_hoisted_6 = {
  viewBox: "0 0 24 24",
  width: "14",
  height: "14",
  "aria-hidden": "true",
  focusable: "false"
};
const FeedbackBarvue_type_template_id_2c1a64b6_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("circle", {
  cx: "12",
  cy: "12",
  r: "9"
}, null, -1);
const FeedbackBarvue_type_template_id_2c1a64b6_hoisted_8 = ["d"];
function FeedbackBarvue_type_template_id_2c1a64b6_render(_ctx, _cache, $props, $setup, $data, $options) {
  return _ctx.feedback.enabled || _ctx.feedback.supportUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    key: 0,
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["fwld-feedback", {
      'is-voted': _ctx.vote !== ''
    }])
  }, [_ctx.feedback.enabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 0
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: "fwld-feedback__question",
    title: _ctx.question
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.question), 9, FeedbackBarvue_type_template_id_2c1a64b6_hoisted_1), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.choices, choice => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
      key: choice,
      type: "button",
      class: "fwld-feedback__thumb",
      title: _ctx.label(choice),
      "aria-label": _ctx.label(choice),
      "aria-pressed": _ctx.vote === choice ? 'true' : 'false',
      disabled: _ctx.vote !== '',
      onClick: $event => _ctx.send(choice)
    }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("svg", {
      viewBox: "0 0 24 24",
      width: "14",
      height: "14",
      "aria-hidden": "true",
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(`fwld-feedback__icon--${choice}`)
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("path", {
      d: _ctx.thumbPath
    }, null, 8, FeedbackBarvue_type_template_id_2c1a64b6_hoisted_3)], 2))], 8, FeedbackBarvue_type_template_id_2c1a64b6_hoisted_2);
  }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", FeedbackBarvue_type_template_id_2c1a64b6_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.thanks), 1)], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.feedback.supportUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 1,
    class: "fwld-feedback__support",
    href: _ctx.feedback.supportUrl,
    target: "_blank",
    rel: "noopener noreferrer",
    title: _ctx.tr('SupportTitle')
  }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("svg", FeedbackBarvue_type_template_id_2c1a64b6_hoisted_6, [FeedbackBarvue_type_template_id_2c1a64b6_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("path", {
    d: _ctx.helpPath
  }, null, 8, FeedbackBarvue_type_template_id_2c1a64b6_hoisted_8)])), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.tr('Support')), 1)], 8, FeedbackBarvue_type_template_id_2c1a64b6_hoisted_5)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 2)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true);
}
// CONCATENATED MODULE: ./plugins/FeinwerkLiveDashboard/vue/src/FeedbackBar.vue?vue&type=template&id=2c1a64b6

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkLiveDashboard/vue/src/FeedbackBar.vue?vue&type=script&lang=ts


const PLUGIN = 'FeinwerkLiveDashboard';
/** a thumb outline (24 x 24); the down thumb is the same, rotated */
const THUMB_PATH = 'M3 10h4v10H3zM7 10l4-7c1.3 0 2.2 1.1 2 2.4L12.4 9H19a2 2 0 0 1 2 2.3l-1.2 7A2 2 0 0 1 17.8 20H7';
/** localStorage: this browser's vote per question and plugin version (a convenience only) */
function storageKey(topic) {
  return `${PLUGIN}.feedback.${topic}`;
}
function storedVote(topic, version) {
  try {
    const stored = JSON.parse(window.localStorage.getItem(storageKey(topic)) || '{}');
    return stored.version === version && (stored.vote === 'up' || stored.vote === 'down') ? stored.vote : '';
  } catch (e) {
    return '';
  }
}
/* harmony default export */ var FeedbackBarvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    idSite: {
      type: Number,
      required: true
    },
    /** the store "topic": one of the plugin's PluginFeedback::TOPICS */
    topic: {
      type: String,
      required: true
    },
    /** the question, already translated */
    question: {
      type: String,
      required: true
    },
    feedback: {
      type: Object,
      default: () => ({
        enabled: false,
        supportUrl: '',
        version: ''
      })
    }
  },
  data() {
    return {
      vote: storedVote(this.topic, this.feedback.version)
    };
  },
  computed: {
    choices() {
      return ['up', 'down'];
    },
    thumbPath() {
      return THUMB_PATH;
    },
    helpPath() {
      return 'M9.6 9.3a2.5 2.5 0 0 1 4.8.9c0 1.7-2.4 2.2-2.4 3.6M12 17h.01';
    },
    thanks() {
      if (this.vote === '') {
        return '';
      }
      return this.vote === 'up' ? this.tr('FeedbackThanks') : this.tr('FeedbackThanksDown');
    }
  },
  methods: {
    tr(key) {
      return Object(external_CoreHome_["translate"])(`${PLUGIN}_${key}`);
    },
    label(choice) {
      return choice === 'up' ? this.tr('FeedbackYes') : this.tr('FeedbackNo');
    },
    send(vote) {
      this.vote = vote;
      try {
        const stored = {
          version: this.feedback.version,
          vote
        };
        window.localStorage.setItem(storageKey(this.topic), JSON.stringify(stored));
      } catch (e) {
        // private mode or blocked storage: the thanks is shown anyway
      }
      // Best effort: the vote is a hint for Feinwerk, a failed request needs no error message.
      external_CoreHome_["AjaxHelper"].post({
        method: `${PLUGIN}.sendFeedback`,
        idSite: this.idSite
      }, {
        topic: this.topic,
        vote
      }, {
        createErrorNotification: false
      }).catch(() => undefined);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkLiveDashboard/vue/src/FeedbackBar.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkLiveDashboard/vue/src/FeedbackBar.vue



FeedbackBarvue_type_script_lang_ts.render = FeedbackBarvue_type_template_id_2c1a64b6_render

/* harmony default export */ var FeedbackBar = (FeedbackBarvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkLiveDashboard/vue/src/GoalsPanel.vue?vue&type=template&id=5928c41c

const GoalsPanelvue_type_template_id_5928c41c_hoisted_1 = {
  class: "fwld-panel"
};
const GoalsPanelvue_type_template_id_5928c41c_hoisted_2 = {
  class: "fwld-panel__head"
};
const GoalsPanelvue_type_template_id_5928c41c_hoisted_3 = {
  class: "fwld-panel__title"
};
const GoalsPanelvue_type_template_id_5928c41c_hoisted_4 = {
  class: "fwld-panel__window"
};
const GoalsPanelvue_type_template_id_5928c41c_hoisted_5 = {
  key: 0,
  class: "fwld-goals"
};
const GoalsPanelvue_type_template_id_5928c41c_hoisted_6 = ["title"];
const GoalsPanelvue_type_template_id_5928c41c_hoisted_7 = {
  class: "fwld-goals__count"
};
const GoalsPanelvue_type_template_id_5928c41c_hoisted_8 = {
  class: "fwld-goals__money"
};
const GoalsPanelvue_type_template_id_5928c41c_hoisted_9 = {
  key: 1,
  class: "fwld-panel__empty"
};
function GoalsPanelvue_type_template_id_5928c41c_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _directive_flash = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("flash");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("section", GoalsPanelvue_type_template_id_5928c41c_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("header", GoalsPanelvue_type_template_id_5928c41c_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h4", GoalsPanelvue_type_template_id_5928c41c_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.title), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", GoalsPanelvue_type_template_id_5928c41c_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.windowLabel), 1)]), _ctx.rows.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("table", GoalsPanelvue_type_template_id_5928c41c_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tbody", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.rows, row => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", {
      key: row.key
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", {
      scope: "row",
      class: "fwld-goals__label",
      title: row.label
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(row.label), 9, GoalsPanelvue_type_template_id_5928c41c_hoisted_6), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", GoalsPanelvue_type_template_id_5928c41c_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(row.count), 1)])), [[_directive_flash, row.count]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", GoalsPanelvue_type_template_id_5928c41c_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(row.money), 1)])), [[_directive_flash, row.money]])]);
  }), 128))])])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", GoalsPanelvue_type_template_id_5928c41c_hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.emptyText), 1))]);
}
// CONCATENATED MODULE: ./plugins/FeinwerkLiveDashboard/vue/src/GoalsPanel.vue?vue&type=template&id=5928c41c

// CONCATENATED MODULE: ./plugins/FeinwerkLiveDashboard/vue/src/motion.ts
/**
 * Small, calm motion for peripheral vision: a changed value lights up for about a second (CSS
 * class fwld-flash), the big number counts to its new value, new rows of the live log light up.
 * Nothing moves on the first render; with "reduce motion" the stylesheet switches it off and the
 * count jumps (prefersReducedMotion).
 */
const FLASH_CLASS = 'fwld-flash';
function restart(el) {
  el.classList.remove(FLASH_CLASS);
  // reading the layout between remove and add restarts the CSS animation
  el.getBoundingClientRect();
  el.classList.add(FLASH_CLASS);
}
const flash = {
  mounted(el) {
    el.addEventListener('animationend', () => el.classList.remove(FLASH_CLASS));
  },
  updated(el, binding) {
    if (binding.value !== binding.oldValue) {
      restart(el);
    }
  }
};
/** The value between from and to at progress 0 to 1, eased out, as a whole number. */
function tweenValue(from, to, progress) {
  const p = Math.min(1, Math.max(0, progress));
  const eased = 1 - (1 - p) ** 3;
  return Math.round(from + (to - from) * eased);
}
/**
 * Places each row moved since the previous list (positive: up), 'new' for a row that was not in
 * it, null for every row of the first list. An empty previous list counts as none: the board shows
 * empty lists until the first answer arrives, and after a reload nothing is new.
 */
function rankChanges(previous, ids) {
  return ids.map((id, rank) => {
    if (previous === null || previous.size === 0) {
      return null;
    }
    const before = previous.get(id);
    return before === undefined ? 'new' : before - rank;
  });
}
/** Keys of rows that are new against the previous list; none for a first or after an empty list. */
function freshKeys(previous, keys) {
  if (previous === null || previous.size === 0) {
    return new Set();
  }
  return new Set(keys.filter(key => !previous.has(key)));
}
function prefersReducedMotion() {
  return typeof window !== 'undefined' && typeof window.matchMedia === 'function' && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkLiveDashboard/vue/src/GoalsPanel.vue?vue&type=script&lang=ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var GoalsPanelvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  directives: {
    flash: flash
  },
  props: {
    title: {
      type: String,
      required: true
    },
    windowLabel: {
      type: String,
      default: ''
    },
    rows: {
      type: Array,
      required: true
    },
    emptyText: {
      type: String,
      default: ''
    }
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkLiveDashboard/vue/src/GoalsPanel.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkLiveDashboard/vue/src/GoalsPanel.vue



GoalsPanelvue_type_script_lang_ts.render = GoalsPanelvue_type_template_id_5928c41c_render

/* harmony default export */ var GoalsPanel = (GoalsPanelvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkLiveDashboard/vue/src/LiveMap.vue?vue&type=template&id=35f365c8

const LiveMapvue_type_template_id_35f365c8_hoisted_1 = {
  key: 0,
  class: "fwld-panel fwld-map"
};
const LiveMapvue_type_template_id_35f365c8_hoisted_2 = {
  class: "fwld-panel__head"
};
const LiveMapvue_type_template_id_35f365c8_hoisted_3 = {
  class: "fwld-panel__title"
};
const LiveMapvue_type_template_id_35f365c8_hoisted_4 = {
  class: "fwld-panel__window"
};
const LiveMapvue_type_template_id_35f365c8_hoisted_5 = {
  class: "fwld-panel__controls"
};
const LiveMapvue_type_template_id_35f365c8_hoisted_6 = {
  key: 0,
  class: "fwld-map__heading"
};
const LiveMapvue_type_template_id_35f365c8_hoisted_7 = {
  key: 1,
  class: "fwld-map__heading"
};
const LiveMapvue_type_template_id_35f365c8_hoisted_8 = {
  key: 2,
  class: "fwld-panel__empty"
};
const LiveMapvue_type_template_id_35f365c8_hoisted_9 = ["viewBox", "aria-label"];
const LiveMapvue_type_template_id_35f365c8_hoisted_10 = ["d"];
const LiveMapvue_type_template_id_35f365c8_hoisted_11 = ["d", "data-iso"];
const LiveMapvue_type_template_id_35f365c8_hoisted_12 = {
  key: 0
};
const LiveMapvue_type_template_id_35f365c8_hoisted_13 = ["cx", "cy", "r"];
const LiveMapvue_type_template_id_35f365c8_hoisted_14 = ["x", "y", "text-anchor", "font-size", "stroke-width"];
const LiveMapvue_type_template_id_35f365c8_hoisted_15 = {
  key: 4,
  class: "fwld-map__legend"
};
const _hoisted_16 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "fwld-map__scale",
  "aria-hidden": "true"
}, null, -1);
const _hoisted_17 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "fwld-map__dot",
  "aria-hidden": "true"
}, null, -1);
const _hoisted_18 = {
  key: 1
};
function LiveMapvue_type_template_id_35f365c8_render(_ctx, _cache, $props, $setup, $data, $options) {
  return _ctx.world ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("section", LiveMapvue_type_template_id_35f365c8_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("header", LiveMapvue_type_template_id_35f365c8_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h4", LiveMapvue_type_template_id_35f365c8_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.title), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", LiveMapvue_type_template_id_35f365c8_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.windowLabel), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", LiveMapvue_type_template_id_35f365c8_hoisted_5, [_ctx.country !== '' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
    key: 0,
    type: "button",
    class: "fwld-map__world",
    onClick: _cache[0] || (_cache[0] = $event => _ctx.$emit('select', ''))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.texts.world), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])]), _ctx.country !== '' && _ctx.heading ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", LiveMapvue_type_template_id_35f365c8_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.heading), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.regionsUnknown ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", LiveMapvue_type_template_id_35f365c8_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.texts.noRegions), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.missing ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", LiveMapvue_type_template_id_35f365c8_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.texts.noMap), 1)) : _ctx.shown ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("svg", {
    key: 3,
    ref: "svg",
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["fwld-map__svg", _ctx.countryMap ? 'fwld-map__svg--country' : 'fwld-map__svg--world']),
    viewBox: `0 0 ${_ctx.shown.view.width} ${_ctx.shown.view.height}`,
    preserveAspectRatio: "xMidYMid meet",
    role: "img",
    "aria-label": _ctx.heading || _ctx.title,
    onClick: _cache[1] || (_cache[1] = (...args) => _ctx.onClick && _ctx.onClick(...args))
  }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.shown.context, (area, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("path", {
      key: `context:${index}`,
      d: area.d,
      class: "fwld-map__context"
    }, null, 8, LiveMapvue_type_template_id_35f365c8_hoisted_10);
  }), 128)), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.areas, area => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("path", {
      key: area.key,
      d: area.d,
      "data-iso": area.iso,
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["fwld-map__land", {
        'fwld-map__land--visited': area.opacity > 0
      }]),
      style: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeStyle"])(area.opacity > 0 ? {
        fillOpacity: area.opacity
      } : undefined)
    }, [area.title ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("title", LiveMapvue_type_template_id_35f365c8_hoisted_12, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(area.title), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 14, LiveMapvue_type_template_id_35f365c8_hoisted_11);
  }), 128)), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.bubbles, bubble => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("circle", {
      key: bubble.key,
      class: "fwld-map__city",
      cx: bubble.x,
      cy: bubble.y,
      r: bubble.r
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("title", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(bubble.title), 1)], 8, LiveMapvue_type_template_id_35f365c8_hoisted_13);
  }), 128)), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.labels, label => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("text", {
      key: `label:${label.key}`,
      class: "fwld-map__label",
      x: label.x,
      y: label.y,
      "text-anchor": label.anchor,
      "font-size": label.size,
      "stroke-width": label.halo
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(label.text), 9, LiveMapvue_type_template_id_35f365c8_hoisted_14);
  }), 128))], 10, LiveMapvue_type_template_id_35f365c8_hoisted_9)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.missing && (_ctx.texts.legend || _ctx.texts.hint) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", LiveMapvue_type_template_id_35f365c8_hoisted_15, [_ctx.countryMap ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 0
  }, [_hoisted_16, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.texts.legend), 1), _hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.texts.legendCities), 1)], 64)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_18, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.texts.hint), 1))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true);
}
// CONCATENATED MODULE: ./plugins/FeinwerkLiveDashboard/vue/src/LiveMap.vue?vue&type=template&id=35f365c8

// CONCATENATED MODULE: ./plugins/FeinwerkLiveDashboard/vue/src/countryCodes.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
/**
 * ISO 3166-1 country codes: Matomo's two letter codes (lowercase) to the three letter codes of
 * the world map (data-iso). Kosovo has no ISO code; Matomo uses "xk", the map "KOS".
 */
const ISO3 = {
  ad: 'AND',
  ae: 'ARE',
  af: 'AFG',
  ag: 'ATG',
  ai: 'AIA',
  al: 'ALB',
  am: 'ARM',
  ao: 'AGO',
  aq: 'ATA',
  ar: 'ARG',
  as: 'ASM',
  at: 'AUT',
  au: 'AUS',
  aw: 'ABW',
  ax: 'ALA',
  az: 'AZE',
  ba: 'BIH',
  bb: 'BRB',
  bd: 'BGD',
  be: 'BEL',
  bf: 'BFA',
  bg: 'BGR',
  bh: 'BHR',
  bi: 'BDI',
  bj: 'BEN',
  bl: 'BLM',
  bm: 'BMU',
  bn: 'BRN',
  bo: 'BOL',
  bq: 'BES',
  br: 'BRA',
  bs: 'BHS',
  bt: 'BTN',
  bv: 'BVT',
  bw: 'BWA',
  by: 'BLR',
  bz: 'BLZ',
  ca: 'CAN',
  cc: 'CCK',
  cd: 'COD',
  cf: 'CAF',
  cg: 'COG',
  ch: 'CHE',
  ci: 'CIV',
  ck: 'COK',
  cl: 'CHL',
  cm: 'CMR',
  cn: 'CHN',
  co: 'COL',
  cr: 'CRI',
  cu: 'CUB',
  cv: 'CPV',
  cw: 'CUW',
  cx: 'CXR',
  cy: 'CYP',
  cz: 'CZE',
  de: 'DEU',
  dj: 'DJI',
  dk: 'DNK',
  dm: 'DMA',
  do: 'DOM',
  dz: 'DZA',
  ec: 'ECU',
  ee: 'EST',
  eg: 'EGY',
  eh: 'ESH',
  er: 'ERI',
  es: 'ESP',
  et: 'ETH',
  fi: 'FIN',
  fj: 'FJI',
  fk: 'FLK',
  fm: 'FSM',
  fo: 'FRO',
  fr: 'FRA',
  ga: 'GAB',
  gb: 'GBR',
  gd: 'GRD',
  ge: 'GEO',
  gf: 'GUF',
  gg: 'GGY',
  gh: 'GHA',
  gi: 'GIB',
  gl: 'GRL',
  gm: 'GMB',
  gn: 'GIN',
  gp: 'GLP',
  gq: 'GNQ',
  gr: 'GRC',
  gs: 'SGS',
  gt: 'GTM',
  gu: 'GUM',
  gw: 'GNB',
  gy: 'GUY',
  hk: 'HKG',
  hm: 'HMD',
  hn: 'HND',
  hr: 'HRV',
  ht: 'HTI',
  hu: 'HUN',
  id: 'IDN',
  ie: 'IRL',
  il: 'ISR',
  im: 'IMN',
  in: 'IND',
  io: 'IOT',
  iq: 'IRQ',
  ir: 'IRN',
  is: 'ISL',
  it: 'ITA',
  je: 'JEY',
  jm: 'JAM',
  jo: 'JOR',
  jp: 'JPN',
  ke: 'KEN',
  kg: 'KGZ',
  kh: 'KHM',
  ki: 'KIR',
  km: 'COM',
  kn: 'KNA',
  kp: 'PRK',
  kr: 'KOR',
  kw: 'KWT',
  ky: 'CYM',
  kz: 'KAZ',
  la: 'LAO',
  lb: 'LBN',
  lc: 'LCA',
  li: 'LIE',
  lk: 'LKA',
  lr: 'LBR',
  ls: 'LSO',
  lt: 'LTU',
  lu: 'LUX',
  lv: 'LVA',
  ly: 'LBY',
  ma: 'MAR',
  mc: 'MCO',
  md: 'MDA',
  me: 'MNE',
  mf: 'MAF',
  mg: 'MDG',
  mh: 'MHL',
  mk: 'MKD',
  ml: 'MLI',
  mm: 'MMR',
  mn: 'MNG',
  mo: 'MAC',
  mp: 'MNP',
  mq: 'MTQ',
  mr: 'MRT',
  ms: 'MSR',
  mt: 'MLT',
  mu: 'MUS',
  mv: 'MDV',
  mw: 'MWI',
  mx: 'MEX',
  my: 'MYS',
  mz: 'MOZ',
  na: 'NAM',
  nc: 'NCL',
  ne: 'NER',
  nf: 'NFK',
  ng: 'NGA',
  ni: 'NIC',
  nl: 'NLD',
  no: 'NOR',
  np: 'NPL',
  nr: 'NRU',
  nu: 'NIU',
  nz: 'NZL',
  om: 'OMN',
  pa: 'PAN',
  pe: 'PER',
  pf: 'PYF',
  pg: 'PNG',
  ph: 'PHL',
  pk: 'PAK',
  pl: 'POL',
  pm: 'SPM',
  pn: 'PCN',
  pr: 'PRI',
  ps: 'PSE',
  pt: 'PRT',
  pw: 'PLW',
  py: 'PRY',
  qa: 'QAT',
  re: 'REU',
  ro: 'ROU',
  rs: 'SRB',
  ru: 'RUS',
  rw: 'RWA',
  sa: 'SAU',
  sb: 'SLB',
  sc: 'SYC',
  sd: 'SDN',
  se: 'SWE',
  sg: 'SGP',
  sh: 'SHN',
  si: 'SVN',
  sj: 'SJM',
  sk: 'SVK',
  sl: 'SLE',
  sm: 'SMR',
  sn: 'SEN',
  so: 'SOM',
  sr: 'SUR',
  ss: 'SSD',
  st: 'STP',
  sv: 'SLV',
  sx: 'SXM',
  sy: 'SYR',
  sz: 'SWZ',
  tc: 'TCA',
  td: 'TCD',
  tf: 'ATF',
  tg: 'TGO',
  th: 'THA',
  tj: 'TJK',
  tk: 'TKL',
  tl: 'TLS',
  tm: 'TKM',
  tn: 'TUN',
  to: 'TON',
  tr: 'TUR',
  tt: 'TTO',
  tv: 'TUV',
  tw: 'TWN',
  tz: 'TZA',
  ua: 'UKR',
  ug: 'UGA',
  um: 'UMI',
  us: 'USA',
  uy: 'URY',
  uz: 'UZB',
  va: 'VAT',
  vc: 'VCT',
  ve: 'VEN',
  vg: 'VGB',
  vi: 'VIR',
  vn: 'VNM',
  vu: 'VUT',
  wf: 'WLF',
  ws: 'WSM',
  xk: 'KOS',
  ye: 'YEM',
  yt: 'MYT',
  za: 'ZAF',
  zm: 'ZMB',
  zw: 'ZWE'
};
// CONCATENATED MODULE: ./plugins/FeinwerkLiveDashboard/vue/src/geo.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// coefficients of the Natural Earth projection (Savric, Jenny, Patterson, Petrovic, Hurni 2011)
const A = [0.8707, -0.131979, -0.013791, 0.003971, -0.001529];
const B = [1.007226, 0.015085, -0.044475, 0.028874, -0.005916];
/** Projected like the map file: 180 units per radian, centred on 500, 270, y pointing down. */
function naturalEarth(lon, lat) {
  const lambda = lon * Math.PI / 180;
  const phi = -lat * Math.PI / 180;
  const p2 = phi * phi;
  const p4 = p2 * p2;
  const x = 180 * lambda * (A[0] + p2 * (A[1] + p2 * (A[2] + p4 * p2 * (A[3] + p2 * A[4]))));
  const y = 180 * phi * (B[0] + p2 * (B[1] + p4 * (B[2] + B[3] * p2 + B[4] * p4)));
  return [x + 500, y + 270];
}
/** Lambert azimuthal equal-area around lon0, lat0, as Matomo's country maps (radius 1000). */
function laea(lon, lat, lon0, lat0) {
  const r = 1000;
  const phi = lat * Math.PI / 180;
  const dLambda = (lon - lon0) * Math.PI / 180;
  const phi0 = lat0 * Math.PI / 180;
  const k = Math.sqrt(2 / (1 + Math.sin(phi0) * Math.sin(phi) + Math.cos(phi0) * Math.cos(phi) * Math.cos(dLambda))) * 0.5 * Math.SQRT2;
  const x = r * k * Math.cos(phi) * Math.sin(dLambda);
  const y = -r * k * (Math.cos(phi0) * Math.sin(phi) - Math.sin(phi0) * Math.cos(phi) * Math.cos(dLambda));
  return [r + x, r + y];
}
/** A place in the map file's own coordinates (its viewBox). */
function project(view, lon, lat) {
  const {
    proj
  } = view;
  const [px, py] = proj.id === 'laea' ? laea(lon, lat, proj.lon0, proj.lat0) : naturalEarth(lon, lat);
  const scale = Math.min((view.width - 2 * view.padding) / view.bboxWidth, (view.height - 2 * view.padding) / view.bboxHeight);
  const left = 0.5 * (view.width - view.bboxWidth * scale);
  const top = 0.5 * (view.height - view.bboxHeight * scale);
  return [(px - view.x) * scale + left, (py - view.y) * scale + top];
}
/** The map's code of a Matomo country code; '' for Matomo's special codes (unknown, Europe...). */
function iso3(code) {
  return ISO3[code.toLowerCase()] || '';
}
/** Matomo's country code of a map code; '' for areas without one (Somaliland, N. Cyprus). */
function iso2(code) {
  const found = Object.keys(ISO3).find(key => ISO3[key] === code.toUpperCase());
  return found || '';
}
function numberOf(el, name) {
  const value = el.getAttribute(name);
  return value === null || value === '' ? NaN : Number(value);
}
/** The paths of one group of the file (countries, context, regions). */
function groupPaths(doc, id) {
  const group = Array.from(doc.getElementsByTagName('g')).find(g => g.getAttribute('id') === id);
  return group ? Array.from(group.getElementsByTagName('path')) : [];
}
function attr(el, name) {
  return el.getAttribute(name) || '';
}
/** Projection, view and paths of a map file; null when the file cannot be used. */
function parseWorldMap(svg) {
  if (!svg) {
    return null;
  }
  let doc;
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
  if (!viewEl || !bboxEl || !projEl || projId !== 'naturalearth' && projId !== 'laea') {
    return null;
  }
  const proj = {
    id: projId,
    lon0: Number(projEl.getAttribute('lon0') || 0),
    lat0: Number(projEl.getAttribute('lat0') || 0)
  };
  if (!Number.isFinite(proj.lon0) || !Number.isFinite(proj.lat0) || proj.id === 'naturalearth' && proj.lon0 !== 0) {
    return null;
  }
  const padding = numberOf(viewEl, 'padding');
  const view = {
    width: numberOf(viewEl, 'w'),
    height: numberOf(viewEl, 'h'),
    padding: Number.isFinite(padding) ? padding : 0,
    x: numberOf(bboxEl, 'x'),
    y: numberOf(bboxEl, 'y'),
    bboxWidth: numberOf(bboxEl, 'w'),
    bboxHeight: numberOf(bboxEl, 'h'),
    proj
  };
  const numbers = [view.width, view.height, view.padding, view.x, view.y, view.bboxWidth, view.bboxHeight];
  if (!numbers.every(Number.isFinite) || view.bboxWidth <= 0 || view.bboxHeight <= 0) {
    return null;
  }
  const country = path => ({
    iso: attr(path, 'data-iso'),
    name: attr(path, 'data-name'),
    d: attr(path, 'd')
  });
  const countries = groupPaths(doc, 'countries').map(country).filter(c => c.d !== '');
  const context = groupPaths(doc, 'context').map(country).filter(c => c.d !== '');
  // Matomo 6 names the region code in data-region, Matomo 5 in data-p
  const regions = groupPaths(doc, 'regions').map(path => ({
    code: attr(path, 'data-region') || attr(path, 'data-p'),
    name: attr(path, 'data-name'),
    d: attr(path, 'd')
  })).filter(region => region.d !== '');
  return countries.length || regions.length ? {
    view,
    countries,
    context,
    regions
  } : null;
}
/** Fill of a country: its share of the busiest one; never invisible when it has visits. */
function shade(count, max) {
  if (count <= 0 || max <= 0) {
    return 0;
  }
  return 0.15 + 0.75 * Math.sqrt(Math.min(1, count / max));
}
/** A city's bubble: 3 to 12 screen pixels, area by visits, in map units at the drawn size. */
function bubbleRadius(count, max, perPixel) {
  const share = max > 0 ? Math.min(1, Math.max(0, count) / max) : 0;
  return (3 + 9 * Math.sqrt(share)) * perPixel;
}
/** The box around some paths (a country's regions); null without points. */
function pathsBox(paths) {
  let x1 = Infinity;
  let y1 = Infinity;
  let x2 = -Infinity;
  let y2 = -Infinity;
  paths.forEach(d => {
    const n = (d.match(/-?\d+(?:\.\d+)?/g) || []).map(Number);
    for (let i = 0; i + 1 < n.length; i += 2) {
      x1 = Math.min(x1, n[i]);
      x2 = Math.max(x2, n[i]);
      y1 = Math.min(y1, n[i + 1]);
      y2 = Math.max(y2, n[i + 1]);
    }
  });
  return Number.isFinite(x1) ? {
    x: x1,
    y: y1,
    w: x2 - x1,
    h: y2 - y1
  } : null;
}
function inside(box, x, y) {
  return x >= box.x && x <= box.x + box.w && y >= box.y && y <= box.y + box.h;
}
/**
 * Map units per screen pixel of a drawing fitted into its box (preserveAspectRatio meet): the side
 * that limits the drawing decides. 1 before the box is measured.
 */
function unitsPerPixel(viewWidth, viewHeight, boxWidth, boxHeight) {
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
function labelAt(x, r, text, size, perPixel, mapWidth) {
  const gap = 4 * perPixel;
  const width = text.length * size * 0.6 * perPixel;
  const right = mapWidth - (x + r + gap);
  const left = x - r - gap;
  return width > right && left > right ? {
    x: x - r - gap,
    anchor: 'end',
    width
  } : {
    x: x + r + gap,
    anchor: 'start',
    width
  };
}
/**
 * Names for the biggest cities first (the list comes sorted by visits); a name that would cover one
 * already placed is left out, so names never overlap. At most max names.
 */
function placeLabels(bubbles, size, perPixel, mapWidth, max) {
  const boxes = [];
  const placed = [];
  bubbles.filter(b => b.name !== '').forEach(b => {
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
      h: height
    };
    const overlaps = boxes.some(o => box.x < o.x + o.w && o.x < box.x + box.w && box.y < o.y + o.h && o.y < box.y + box.h);
    if (!overlaps) {
      boxes.push(box);
      placed.push({
        key: b.key,
        x: at.x,
        y,
        anchor: at.anchor,
        text: b.name
      });
    }
  });
  return placed;
}
// CONCATENATED MODULE: ./plugins/FeinwerkLiveDashboard/vue/src/worldMap.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// one request per page, however many boards and widgets show the map
const loads = {};
function getText(url) {
  return fetch(url, {
    credentials: 'same-origin'
  }).then(r => r.ok ? r.text() : '');
}
/** Matomo's world map, parsed; null without a URL, on a failed request or an unusable file. */
function loadWorldMap(url, get = getText) {
  if (!url) {
    return Promise.resolve(null);
  }
  if (!loads[url]) {
    loads[url] = get(url).then(parseWorldMap).catch(() => null);
  }
  return loads[url];
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkLiveDashboard/vue/src/LiveMap.vue?vue&type=script&lang=ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */



const MAX_LABELS = 8;
/** city names in screen pixels, whatever size the map is drawn at */
const LABEL_PX = 13;
/** a country shaded as a whole: its regions are unknown on the map, or it is drawn as one area */
const WHOLE_OPACITY = 0.6;
const round = n => Math.round(n * 100) / 100;
/* harmony default export */ var LiveMapvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    title: {
      type: String,
      required: true
    },
    windowLabel: {
      type: String,
      default: ''
    },
    /** Matomo's world map; the country maps lie beside it (DEU.svg) */
    url: {
      type: String,
      default: ''
    },
    /** the open country (Matomo's code, de); '' for the world map */
    country: {
      type: String,
      default: ''
    },
    countries: {
      type: Array,
      default: () => []
    },
    regions: {
      type: Array,
      default: () => []
    },
    cities: {
      type: Array,
      default: () => []
    },
    texts: {
      type: Object,
      default: () => ({})
    },
    /** "Bayern: 6 visits" */
    visits: {
      type: Function,
      default: (label, count) => `${label}: ${count}`
    }
  },
  emits: ['select', 'missing'],
  data() {
    return {
      world: null,
      countryMap: null,
      /** Matomo has no map of the open country */
      missing: false,
      /** the drawn box in pixels: keeps bubbles and names the same size on every map */
      drawnWidth: 0,
      drawnHeight: 0,
      observer: null
    };
  },
  computed: {
    code() {
      return this.country.toLowerCase();
    },
    shown() {
      return this.code === '' ? this.world : this.countryMap;
    },
    countryRow() {
      return this.countries.find(row => row.code.toLowerCase() === this.code);
    },
    heading() {
      return this.countryRow ? this.countryRow.title : '';
    },
    unitsPerPixel() {
      if (!this.shown) {
        return 1;
      }
      const {
        width,
        height
      } = this.shown.view;
      return unitsPerPixel(width, height, this.drawnWidth, this.drawnHeight);
    },
    /** the world's countries or the open country's regions, shaded by their visits */
    areas() {
      const map = this.shown;
      if (!map) {
        return [];
      }
      if (this.countryMap && map === this.countryMap) {
        const counts = {};
        this.regions.filter(r => r.country.toLowerCase() === this.code).forEach(r => {
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
            title: this.heading
          }));
        }
        return map.regions.map((region, index) => {
          const count = counts[region.code.toUpperCase()] || 0;
          return {
            key: `region:${index}`,
            d: region.d,
            iso: '',
            opacity: round(shade(count, max)),
            title: count > 0 ? this.visits(region.name, count) : region.name
          };
        });
      }
      const rows = {};
      this.countries.forEach(row => {
        const code = iso3(row.code);
        if (code !== '' && row.count > 0) {
          rows[code] = row;
        }
      });
      const max = Math.max(0, ...Object.values(rows).map(row => row.count));
      return map.countries.map((country, index) => {
        const row = rows[country.iso];
        return {
          key: `country:${index}`,
          d: country.d,
          iso: country.iso,
          opacity: row ? round(shade(row.count, max)) : 0,
          title: row ? row.title : ''
        };
      });
    },
    /** visits in the open country, none in a region its map knows: shade it as a whole */
    wholeCountry() {
      const map = this.countryMap;
      if (!map || this.code === '' || !this.countryRow || this.countryRow.count <= 0) {
        return false;
      }
      const codes = new Set(this.regions.filter(r => r.country.toLowerCase() === this.code && r.count > 0).map(r => r.region.toUpperCase()));
      return !map.regions.some(region => codes.has(region.code.toUpperCase()));
    },
    /** the map has regions, but none it can match (a one-area country needs no note) */
    regionsUnknown() {
      return this.wholeCountry && !!this.countryMap && this.countryMap.regions.length > 1;
    },
    /** the box of the open country's regions: a city stored with a place outside it is left out */
    countryBox() {
      return this.countryMap ? pathsBox(this.countryMap.regions.map(r => r.d)) : null;
    },
    /** the open country's cities at their place; none on the world map */
    bubbles() {
      const map = this.countryMap;
      const box = this.countryBox;
      if (!map || !box || this.code === '') {
        return [];
      }
      const cities = this.cities.filter(c => c.country.toLowerCase() === this.code && Number.isFinite(c.lat) && Number.isFinite(c.lon));
      const max = Math.max(0, ...cities.map(c => c.count));
      // the big ones first, so the small ones stay on top and can be pointed at
      return [...cities].sort((a, b) => b.count - a.count).map(city => {
        const [x, y] = project(map.view, city.lon, city.lat);
        return {
          key: city.key,
          x: round(x),
          y: round(y),
          r: round(bubbleRadius(city.count, max, this.unitsPerPixel)),
          title: city.title,
          name: city.name
        };
      }).filter(bubble => inside(box, bubble.x, bubble.y));
    },
    labels() {
      const upp = this.unitsPerPixel;
      const width = this.shown ? this.shown.view.width : 0;
      // the bubbles come biggest first; names that would overlap give way
      return placeLabels(this.bubbles, LABEL_PX, upp, width, MAX_LABELS).map(label => ({
        key: label.key,
        x: round(label.x),
        y: round(label.y),
        anchor: label.anchor,
        size: round(LABEL_PX * upp),
        halo: round(3 * upp),
        text: label.text
      }));
    }
  },
  watch: {
    country() {
      this.loadCountry();
    }
  },
  mounted() {
    loadWorldMap(this.url).then(world => {
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
      loadWorldMap(this.url.replace(/world\.svg$/, `${code}.svg`)).then(map => {
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
      const svg = this.$refs.svg;
      if (this.observer) {
        this.observer.disconnect();
        this.observer = null;
      }
      if (!svg || typeof ResizeObserver !== 'function') {
        return;
      }
      this.observer = new ResizeObserver(entries => {
        this.drawnWidth = entries[0].contentRect.width;
        this.drawnHeight = entries[0].contentRect.height;
      });
      this.observer.observe(svg);
    },
    /** On the world map a click on a country opens it. */
    onClick(event) {
      if (this.code !== '') {
        return;
      }
      const place = event.target.closest('[data-iso]');
      const code = place ? iso2(place.getAttribute('data-iso') || '') : '';
      if (code !== '') {
        this.$emit('select', code);
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkLiveDashboard/vue/src/LiveMap.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkLiveDashboard/vue/src/LiveMap.vue



LiveMapvue_type_script_lang_ts.render = LiveMapvue_type_template_id_35f365c8_render

/* harmony default export */ var LiveMap = (LiveMapvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkLiveDashboard/vue/src/LiveLog.vue?vue&type=template&id=4d82fb55

const LiveLogvue_type_template_id_4d82fb55_hoisted_1 = {
  class: "fwld-panel__head"
};
const LiveLogvue_type_template_id_4d82fb55_hoisted_2 = {
  class: "fwld-panel__title"
};
const LiveLogvue_type_template_id_4d82fb55_hoisted_3 = {
  class: "fwld-panel__window"
};
const LiveLogvue_type_template_id_4d82fb55_hoisted_4 = {
  key: 0,
  class: "fwld-log__scroll"
};
const LiveLogvue_type_template_id_4d82fb55_hoisted_5 = {
  class: "fwld-log__table"
};
const LiveLogvue_type_template_id_4d82fb55_hoisted_6 = {
  key: 0
};
const LiveLogvue_type_template_id_4d82fb55_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("col", {
  class: "fwld-log__col-time"
}, null, -1);
const LiveLogvue_type_template_id_4d82fb55_hoisted_8 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("col", null, null, -1);
const LiveLogvue_type_template_id_4d82fb55_hoisted_9 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("col", {
  class: "fwld-log__col-num"
}, null, -1);
const LiveLogvue_type_template_id_4d82fb55_hoisted_10 = {
  key: 1
};
const LiveLogvue_type_template_id_4d82fb55_hoisted_11 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("col", {
  class: "fwld-log__col-time"
}, null, -1);
const LiveLogvue_type_template_id_4d82fb55_hoisted_12 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("col", {
  class: "fwld-log__col-country"
}, null, -1);
const LiveLogvue_type_template_id_4d82fb55_hoisted_13 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("col", {
  class: "fwld-log__col-source"
}, null, -1);
const LiveLogvue_type_template_id_4d82fb55_hoisted_14 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("col", {
  class: "fwld-log__col-device"
}, null, -1);
const LiveLogvue_type_template_id_4d82fb55_hoisted_15 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("col", null, null, -1);
const LiveLogvue_type_template_id_4d82fb55_hoisted_16 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("col", {
  class: "fwld-log__col-num"
}, null, -1);
const LiveLogvue_type_template_id_4d82fb55_hoisted_17 = {
  key: 2
};
const LiveLogvue_type_template_id_4d82fb55_hoisted_18 = {
  scope: "col"
};
const _hoisted_19 = {
  scope: "col"
};
const _hoisted_20 = {
  class: "fwld-log__line"
};
const _hoisted_21 = {
  class: "fwld-log__line"
};
const _hoisted_22 = {
  scope: "col",
  class: "fwld-log__num"
};
const _hoisted_23 = {
  scope: "col"
};
const _hoisted_24 = {
  class: "fwld-log__hidden"
};
const _hoisted_25 = {
  key: 3
};
const _hoisted_26 = {
  scope: "col"
};
const _hoisted_27 = {
  scope: "col"
};
const _hoisted_28 = {
  scope: "col"
};
const _hoisted_29 = {
  scope: "col"
};
const _hoisted_30 = {
  scope: "col"
};
const _hoisted_31 = {
  scope: "col",
  class: "fwld-log__num"
};
const _hoisted_32 = {
  class: "fwld-log__hidden"
};
const _hoisted_33 = {
  class: "fwld-log__time"
};
const _hoisted_34 = ["title"];
const _hoisted_35 = {
  class: "fwld-log__line"
};
const _hoisted_36 = {
  class: "fwld-log__line fwld-log__sub"
};
const _hoisted_37 = {
  class: "fwld-log__num"
};
const _hoisted_38 = {
  class: "fwld-log__marks"
};
const _hoisted_39 = {
  class: "fwld-log__slots"
};
const _hoisted_40 = ["src", "alt", "title"];
const _hoisted_41 = ["title"];
const _hoisted_42 = ["title"];
const _hoisted_43 = ["title"];
const _hoisted_44 = ["title"];
const _hoisted_45 = {
  class: "fwld-log__num"
};
const _hoisted_46 = ["src", "alt", "title"];
const _hoisted_47 = {
  key: 1,
  class: "fwld-panel__empty"
};
function LiveLogvue_type_template_id_4d82fb55_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("section", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["fwld-panel fwld-log", {
      'fwld-log--narrow': _ctx.narrow
    }])
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("header", LiveLogvue_type_template_id_4d82fb55_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h4", LiveLogvue_type_template_id_4d82fb55_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.title), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", LiveLogvue_type_template_id_4d82fb55_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.windowLabel), 1)]), _ctx.rows.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", LiveLogvue_type_template_id_4d82fb55_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("table", LiveLogvue_type_template_id_4d82fb55_hoisted_5, [_ctx.narrow ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("colgroup", LiveLogvue_type_template_id_4d82fb55_hoisted_6, [LiveLogvue_type_template_id_4d82fb55_hoisted_7, LiveLogvue_type_template_id_4d82fb55_hoisted_8, LiveLogvue_type_template_id_4d82fb55_hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("col", {
    style: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeStyle"])({
      width: `${_ctx.markWidth}px`
    })
  }, null, 4)])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("colgroup", LiveLogvue_type_template_id_4d82fb55_hoisted_10, [LiveLogvue_type_template_id_4d82fb55_hoisted_11, LiveLogvue_type_template_id_4d82fb55_hoisted_12, LiveLogvue_type_template_id_4d82fb55_hoisted_13, LiveLogvue_type_template_id_4d82fb55_hoisted_14, LiveLogvue_type_template_id_4d82fb55_hoisted_15, LiveLogvue_type_template_id_4d82fb55_hoisted_16, (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.icons, icon => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("col", {
      key: icon.key,
      class: "fwld-log__col-icon"
    });
  }), 128))])), _ctx.narrow ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("thead", LiveLogvue_type_template_id_4d82fb55_hoisted_17, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", LiveLogvue_type_template_id_4d82fb55_hoisted_18, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.heads.duration), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", _hoisted_19, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_20, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.heads.country), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_21, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.heads.source), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", _hoisted_22, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.heads.actions), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", _hoisted_23, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_24, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.heads.marks), 1)])])])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("thead", _hoisted_25, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", _hoisted_26, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.heads.duration), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", _hoisted_27, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.heads.country), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", _hoisted_28, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.heads.source), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", _hoisted_29, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.heads.device), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", _hoisted_30, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.heads.page), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", _hoisted_31, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.heads.actions), 1), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.icons, icon => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("th", {
      key: icon.key,
      scope: "col"
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_32, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(icon.label), 1)]);
  }), 128))])])), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tbody", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.rows, row => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", {
      key: row.key,
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
        'fwld-flash': _ctx.fresh.has(row.key)
      })
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_33, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(row.duration), 1), _ctx.narrow ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
      key: 0
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", {
      title: `${row.country}, ${row.channel}`
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_35, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(row.country), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_36, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(row.source), 1)], 8, _hoisted_34), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_37, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(row.actions), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_38, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_39, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.icons, icon => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
        key: icon.key,
        class: "fwld-log__slot"
      }, [row.icons.includes(icon.key) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("img", {
        key: 0,
        src: icon.src,
        width: "16",
        height: "16",
        alt: icon.label,
        title: icon.label
      }, null, 8, _hoisted_40)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
    }), 128))])])], 64)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
      key: 1
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", {
      title: row.country
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(row.country), 9, _hoisted_41), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", {
      title: row.channel
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(row.source), 9, _hoisted_42), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", {
      title: row.device
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(row.device), 9, _hoisted_43), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", {
      class: "fwld-log__page",
      title: row.pageTitle
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(row.page), 9, _hoisted_44), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_45, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(row.actions), 1), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.icons, icon => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", {
        key: icon.key,
        class: "fwld-log__icon"
      }, [row.icons.includes(icon.key) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("img", {
        key: 0,
        src: icon.src,
        width: "16",
        height: "16",
        alt: icon.label,
        title: icon.label
      }, null, 8, _hoisted_46)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
    }), 128))], 64))], 2);
  }), 128))])])])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", _hoisted_47, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.emptyText), 1))], 2);
}
// CONCATENATED MODULE: ./plugins/FeinwerkLiveDashboard/vue/src/LiveLog.vue?vue&type=template&id=4d82fb55

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkLiveDashboard/vue/src/LiveLog.vue?vue&type=script&lang=ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */



/* harmony default export */ var LiveLogvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    title: {
      type: String,
      required: true
    },
    windowLabel: {
      type: String,
      default: ''
    },
    rows: {
      type: Array,
      required: true
    },
    heads: {
      type: Object,
      required: true
    },
    icons: {
      type: Array,
      default: () => []
    },
    emptyText: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      previous: null,
      fresh: new Set(),
      // the panel's content width, measured; 0 until then
      width: 0,
      observer: null
    };
  },
  computed: {
    narrow() {
      return logNarrow(this.width);
    },
    markWidth() {
      // 16 px icons with 2 px between them, and 4 px to the panel's edge
      return markColumns(this.icons.length) * 18 + 4;
    },
    // one string, so the watcher runs when the visits change, not when their age ticks
    rowKeys() {
      return this.rows.map(row => row.key).join('\n');
    }
  },
  mounted() {
    if (typeof ResizeObserver === 'function') {
      this.observer = new ResizeObserver(entries => {
        this.width = Math.round(entries[0].contentRect.width);
      });
      this.observer.observe(this.$el);
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
      handler(joined) {
        const keys = joined === '' ? [] : joined.split('\n');
        this.fresh = freshKeys(this.previous, keys);
        this.previous = new Set(keys);
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkLiveDashboard/vue/src/LiveLog.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkLiveDashboard/vue/src/LiveLog.vue



LiveLogvue_type_script_lang_ts.render = LiveLogvue_type_template_id_4d82fb55_render

/* harmony default export */ var LiveLog = (LiveLogvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkLiveDashboard/vue/src/PulseStrip.vue?vue&type=template&id=0ad7640a

const PulseStripvue_type_template_id_0ad7640a_hoisted_1 = {
  class: "fwld-pulse"
};
const PulseStripvue_type_template_id_0ad7640a_hoisted_2 = ["title"];
const PulseStripvue_type_template_id_0ad7640a_hoisted_3 = {
  class: "fwld-pulse__number"
};
const PulseStripvue_type_template_id_0ad7640a_hoisted_4 = {
  class: "fwld-pulse__label"
};
const PulseStripvue_type_template_id_0ad7640a_hoisted_5 = {
  class: "fwld-pulse__spark"
};
const PulseStripvue_type_template_id_0ad7640a_hoisted_6 = {
  class: "fwld-spark__chart"
};
const PulseStripvue_type_template_id_0ad7640a_hoisted_7 = {
  class: "fwld-spark__axis",
  "aria-hidden": "true"
};
const PulseStripvue_type_template_id_0ad7640a_hoisted_8 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, "0", -1);
const PulseStripvue_type_template_id_0ad7640a_hoisted_9 = ["aria-label"];
const PulseStripvue_type_template_id_0ad7640a_hoisted_10 = ["x", "y", "width", "height"];
const PulseStripvue_type_template_id_0ad7640a_hoisted_11 = {
  class: "fwld-spark__times",
  "aria-hidden": "true"
};
const PulseStripvue_type_template_id_0ad7640a_hoisted_12 = {
  class: "fwld-pulse__caption"
};
const PulseStripvue_type_template_id_0ad7640a_hoisted_13 = {
  class: "fwld-kpis"
};
const PulseStripvue_type_template_id_0ad7640a_hoisted_14 = ["title"];
const PulseStripvue_type_template_id_0ad7640a_hoisted_15 = {
  class: "fwld-kpis__label"
};
const PulseStripvue_type_template_id_0ad7640a_hoisted_16 = {
  class: "fwld-kpis__value"
};
const PulseStripvue_type_template_id_0ad7640a_hoisted_17 = {
  key: 0,
  class: "fwld-kpis__note"
};
function PulseStripvue_type_template_id_0ad7640a_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _directive_flash = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("flash");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("section", PulseStripvue_type_template_id_0ad7640a_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: "fwld-pulse__now",
    title: _ctx.activeHelp
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", PulseStripvue_type_template_id_0ad7640a_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.shownNumber), 1)])), [[_directive_flash, _ctx.activeNow]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", PulseStripvue_type_template_id_0ad7640a_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.activeLabel), 1)], 8, PulseStripvue_type_template_id_0ad7640a_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("figure", PulseStripvue_type_template_id_0ad7640a_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PulseStripvue_type_template_id_0ad7640a_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", PulseStripvue_type_template_id_0ad7640a_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.scaleTop), 1), PulseStripvue_type_template_id_0ad7640a_hoisted_8]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("svg", {
    class: "fwld-spark",
    viewBox: "0 0 300 64",
    preserveAspectRatio: "none",
    role: "img",
    "aria-label": _ctx.sparkSummary
  }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.bars, (bar, i) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("rect", {
      key: i,
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["fwld-spark__bar", {
        'fwld-spark__bar--current': bar.current
      }]),
      x: i * _ctx.slot + 1,
      y: 64 - _ctx.barHeight(bar.height),
      width: _ctx.slot - 2,
      height: _ctx.barHeight(bar.height)
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("title", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.barTitles[i]), 1)], 10, PulseStripvue_type_template_id_0ad7640a_hoisted_10);
  }), 128))], 8, PulseStripvue_type_template_id_0ad7640a_hoisted_9))]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PulseStripvue_type_template_id_0ad7640a_hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.timeLabels[0]), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.timeLabels[1]), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.timeLabels[2]), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("figcaption", PulseStripvue_type_template_id_0ad7640a_hoisted_12, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.sparkLabel), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dl", PulseStripvue_type_template_id_0ad7640a_hoisted_13, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.kpis, kpi => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      key: kpi.label,
      class: "fwld-kpis__item",
      title: kpi.title
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dt", PulseStripvue_type_template_id_0ad7640a_hoisted_15, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(kpi.label), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("dd", PulseStripvue_type_template_id_0ad7640a_hoisted_16, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(kpi.value), 1)])), [[_directive_flash, kpi.value]]), kpi.note ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("dd", PulseStripvue_type_template_id_0ad7640a_hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(kpi.note), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 8, PulseStripvue_type_template_id_0ad7640a_hoisted_14);
  }), 128))])]);
}
// CONCATENATED MODULE: ./plugins/FeinwerkLiveDashboard/vue/src/PulseStrip.vue?vue&type=template&id=0ad7640a

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkLiveDashboard/vue/src/PulseStrip.vue?vue&type=script&lang=ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */



const COUNT_MS = 700;
/* harmony default export */ var PulseStripvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  directives: {
    flash: flash
  },
  props: {
    activeNow: {
      type: Number,
      required: true
    },
    activeLabel: {
      type: String,
      required: true
    },
    activeHelp: {
      type: String,
      default: ''
    },
    bars: {
      type: Array,
      required: true
    },
    barTitles: {
      type: Array,
      required: true
    },
    scaleTop: {
      type: String,
      required: true
    },
    timeLabels: {
      type: Array,
      required: true
    },
    sparkLabel: {
      type: String,
      required: true
    },
    sparkSummary: {
      type: String,
      required: true
    },
    kpis: {
      type: Array,
      required: true
    }
  },
  data() {
    return {
      shown: this.activeNow,
      frame: 0
    };
  },
  watch: {
    activeNow(to, from) {
      window.cancelAnimationFrame(this.frame);
      if (prefersReducedMotion() || typeof window.requestAnimationFrame !== 'function') {
        this.shown = to;
        return;
      }
      const start = performance.now();
      const step = time => {
        const progress = (time - start) / COUNT_MS;
        this.shown = tweenValue(from, to, progress);
        if (progress < 1) {
          this.frame = window.requestAnimationFrame(step);
        }
      };
      this.frame = window.requestAnimationFrame(step);
    }
  },
  beforeUnmount() {
    window.cancelAnimationFrame(this.frame);
  },
  computed: {
    shownNumber() {
      return formatNumber(this.shown, pageLanguage());
    },
    slot() {
      return 300 / Math.max(1, this.bars.length);
    }
  },
  methods: {
    barHeight(percent) {
      // a minute without hits still shows a hairline, so the time axis stays readable
      return Math.max(1, percent / 100 * 62);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkLiveDashboard/vue/src/PulseStrip.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkLiveDashboard/vue/src/PulseStrip.vue



PulseStripvue_type_script_lang_ts.render = PulseStripvue_type_template_id_0ad7640a_render

/* harmony default export */ var PulseStrip = (PulseStripvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkLiveDashboard/vue/src/SegmentedSwitch.vue?vue&type=template&id=2593da22

const SegmentedSwitchvue_type_template_id_2593da22_hoisted_1 = ["aria-label"];
const SegmentedSwitchvue_type_template_id_2593da22_hoisted_2 = ["aria-pressed", "title", "onClick"];
function SegmentedSwitchvue_type_template_id_2593da22_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: "fwld-switch",
    role: "group",
    "aria-label": _ctx.label
  }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.options, option => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
      key: option.value,
      type: "button",
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["fwld-switch__option", {
        'fwld-switch__option--active': option.value === _ctx.modelValue
      }]),
      "aria-pressed": option.value === _ctx.modelValue ? 'true' : 'false',
      title: option.label,
      onClick: $event => _ctx.$emit('update:modelValue', option.value)
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(option.label), 11, SegmentedSwitchvue_type_template_id_2593da22_hoisted_2);
  }), 128))], 8, SegmentedSwitchvue_type_template_id_2593da22_hoisted_1);
}
// CONCATENATED MODULE: ./plugins/FeinwerkLiveDashboard/vue/src/SegmentedSwitch.vue?vue&type=template&id=2593da22

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkLiveDashboard/vue/src/SegmentedSwitch.vue?vue&type=script&lang=ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/* harmony default export */ var SegmentedSwitchvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    options: {
      type: Array,
      required: true
    },
    modelValue: {
      type: String,
      required: true
    },
    label: {
      type: String,
      required: true
    }
  },
  emits: ['update:modelValue']
}));
// CONCATENATED MODULE: ./plugins/FeinwerkLiveDashboard/vue/src/SegmentedSwitch.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkLiveDashboard/vue/src/SegmentedSwitch.vue



SegmentedSwitchvue_type_script_lang_ts.render = SegmentedSwitchvue_type_template_id_2593da22_render

/* harmony default export */ var SegmentedSwitch = (SegmentedSwitchvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkLiveDashboard/vue/src/TopList.vue?vue&type=template&id=5d768659

const TopListvue_type_template_id_5d768659_hoisted_1 = {
  class: "fwld-panel"
};
const TopListvue_type_template_id_5d768659_hoisted_2 = {
  class: "fwld-panel__head"
};
const TopListvue_type_template_id_5d768659_hoisted_3 = {
  class: "fwld-panel__title"
};
const TopListvue_type_template_id_5d768659_hoisted_4 = {
  class: "fwld-panel__window"
};
const TopListvue_type_template_id_5d768659_hoisted_5 = ["title"];
const TopListvue_type_template_id_5d768659_hoisted_6 = {
  class: "fwld-panel__controls"
};
const TopListvue_type_template_id_5d768659_hoisted_7 = {
  key: 0,
  class: "fwld-list"
};
const TopListvue_type_template_id_5d768659_hoisted_8 = ["title", "aria-pressed", "onClick"];
const TopListvue_type_template_id_5d768659_hoisted_9 = ["title"];
const TopListvue_type_template_id_5d768659_hoisted_10 = ["title"];
const TopListvue_type_template_id_5d768659_hoisted_11 = {
  class: "fwld-list__count"
};
const TopListvue_type_template_id_5d768659_hoisted_12 = {
  class: "fwld-list__bar",
  "aria-hidden": "true"
};
const TopListvue_type_template_id_5d768659_hoisted_13 = {
  key: 1,
  class: "fwld-panel__empty"
};
function TopListvue_type_template_id_5d768659_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _directive_flash = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("flash");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("section", TopListvue_type_template_id_5d768659_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("header", TopListvue_type_template_id_5d768659_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h4", TopListvue_type_template_id_5d768659_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.title), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", TopListvue_type_template_id_5d768659_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.windowLabel), 1), _ctx.estimated ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
    key: 0,
    class: "fwld-panel__note",
    title: _ctx.estimatedHelp
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.estimatedLabel), 9, TopListvue_type_template_id_5d768659_hoisted_5)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", TopListvue_type_template_id_5d768659_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "controls")])]), _ctx.shown.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("ol", TopListvue_type_template_id_5d768659_hoisted_7, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.shown, (row, i) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      key: String(row.id) + row.label,
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["fwld-list__row", {
        'fwld-list__row--selected': _ctx.selectable && String(row.id) === _ctx.selectedId
      }])
    }, [_ctx.selectable && (_ctx.selectableIds === null || _ctx.selectableIds.includes(String(row.id))) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
      key: 0,
      type: "button",
      class: "fwld-list__label fwld-list__pick",
      title: row.label,
      "aria-pressed": String(row.id) === _ctx.selectedId ? 'true' : 'false',
      onClick: $event => _ctx.$emit('select', row.id)
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.display(row.label)), 9, TopListvue_type_template_id_5d768659_hoisted_8)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
      key: 1,
      class: "fwld-list__label",
      title: row.label
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.display(row.label)), 9, TopListvue_type_template_id_5d768659_hoisted_9)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["fwld-list__rank", _ctx.rankClass(_ctx.changes[i])]),
      title: _ctx.rankTitle(_ctx.changes[i])
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.rankText(_ctx.changes[i])), 11, TopListvue_type_template_id_5d768659_hoisted_10), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", TopListvue_type_template_id_5d768659_hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.number(row.count)), 1)])), [[_directive_flash, row.count]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", TopListvue_type_template_id_5d768659_hoisted_12, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: "fwld-list__fill",
      style: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeStyle"])({
        width: `${_ctx.barWidth(row.count)}%`
      })
    }, null, 4)])], 2);
  }), 128))])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", TopListvue_type_template_id_5d768659_hoisted_13, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.emptyText), 1))]);
}
// CONCATENATED MODULE: ./plugins/FeinwerkLiveDashboard/vue/src/TopList.vue?vue&type=template&id=5d768659

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkLiveDashboard/vue/src/TopList.vue?vue&type=script&lang=ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */




/* harmony default export */ var TopListvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  directives: {
    flash: flash
  },
  emits: ['select'],
  props: {
    title: {
      type: String,
      required: true
    },
    windowLabel: {
      type: String,
      default: ''
    },
    rows: {
      type: Array,
      required: true
    },
    max: {
      type: Number,
      default: 8
    },
    estimated: {
      type: Boolean,
      default: false
    },
    emptyText: {
      type: String,
      default: ''
    },
    paths: {
      type: Boolean,
      default: false
    },
    /** rows can be chosen (the countries list opens a country on the map) */
    selectable: {
      type: Boolean,
      default: false
    },
    selectedId: {
      type: String,
      default: ''
    },
    /** only these rows can be chosen (null: all) */
    selectableIds: {
      type: Array,
      default: null
    },
    /** what the list shows (list, window, URL or title): a new one starts the comparison anew */
    listKey: {
      type: String,
      default: ''
    },
    /** changes when the list was computed again */
    revision: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      previous: null,
      changes: [],
      lastKey: ''
    };
  },
  watch: {
    orderKey: {
      immediate: true,
      handler() {
        const ids = this.rows.map(row => String(row.id));
        if (this.listKey !== this.lastKey) {
          this.previous = null;
          this.lastKey = this.listKey;
        }
        this.changes = rankChanges(this.previous, ids);
        this.previous = new Map(ids.map((id, rank) => [id, rank]));
      }
    }
  },
  computed: {
    orderKey() {
      return `${this.listKey}\n${this.revision}\n${this.rows.map(row => row.id).join('\n')}`;
    },
    shown() {
      return this.rows.slice(0, this.max);
    },
    top() {
      return this.shown.reduce((max, row) => Math.max(max, row.count), 0);
    },
    estimatedLabel() {
      return Object(external_CoreHome_["translate"])('FeinwerkLiveDashboard_Estimated');
    },
    estimatedHelp() {
      return Object(external_CoreHome_["translate"])('FeinwerkLiveDashboard_EstimatedHelp');
    }
  },
  methods: {
    number(value) {
      return formatNumber(value, pageLanguage());
    },
    rankText(change) {
      if (change === 'new') {
        return Object(external_CoreHome_["translate"])('FeinwerkLiveDashboard_RankNew');
      }
      if (typeof change !== 'number' || change === 0) {
        return '';
      }
      return change > 0 ? `▲ ${change}` : `▼ ${-change}`;
    },
    rankClass(change) {
      if (change === 'new') {
        return 'fwld-list__rank--new';
      }
      if (typeof change !== 'number' || change === 0) {
        return '';
      }
      return change > 0 ? 'fwld-list__rank--up' : 'fwld-list__rank--down';
    },
    rankTitle(change) {
      if (change === 'new') {
        return Object(external_CoreHome_["translate"])('FeinwerkLiveDashboard_RankNewTitle');
      }
      if (typeof change !== 'number' || change === 0) {
        return '';
      }
      const key = change > 0 ? 'FeinwerkLiveDashboard_RankUp' : 'FeinwerkLiveDashboard_RankDown';
      return Object(external_CoreHome_["translate"])(key, String(Math.abs(change)));
    },
    display(label) {
      return this.paths ? pathLabel(label) : label;
    },
    barWidth(count) {
      return share(count, this.top);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkLiveDashboard/vue/src/TopList.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkLiveDashboard/vue/src/TopList.vue



TopListvue_type_script_lang_ts.render = TopListvue_type_template_id_5d768659_render

/* harmony default export */ var TopList = (TopListvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkLiveDashboard/vue/src/LiveBoard.vue?vue&type=script&lang=ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */













function plainTranslate(key, ...values) {
  return Object(external_CoreHome_["translate"])(key, ...values.map(String));
}
function storage() {
  try {
    return window.localStorage;
  } catch (e) {
    return {
      getItem: () => null,
      setItem: () => undefined
    };
  }
}
/* harmony default export */ var LiveBoardvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    FeedbackBar: FeedbackBar,
    GoalsPanel: GoalsPanel,
    LiveLog: LiveLog,
    LiveMap: LiveMap,
    PulseStrip: PulseStrip,
    SegmentedSwitch: SegmentedSwitch,
    TopList: TopList
  },
  props: {
    idSite: {
      type: [Number, String],
      required: true
    },
    siteName: {
      type: String,
      default: ''
    },
    currencySymbol: {
      type: String,
      default: ''
    },
    activeMinutes: {
      type: [Number, String],
      default: 3
    },
    /** full (the page) or a dashboard widget's part: overview, pages, sources, visits */
    variant: {
      type: String,
      default: 'full'
    },
    /** a city is on the map from this many visits in the time window (city_min_visits) */
    cityMinVisits: {
      type: [Number, String],
      default: 3
    },
    /** Matomo's world map (URL relative to Matomo), '' when the file is missing */
    worldMap: {
      type: String,
      default: ''
    },
    /** the feedback thumbs (only on the board's own page): enabled, supportUrl, version */
    feedback: {
      type: Object,
      default: null
    },
    /** the small technical info after the status (plugin setting, on by default) */
    techInfo: {
      type: [Boolean, String],
      default: false
    }
  },
  data() {
    return {
      state: {
        data: null,
        error: false,
        loading: true,
        receivedAt: 0,
        nextAt: 0
      },
      pageLabel: readOption(storage(), 'pageLabel', ['url', 'title'], 'url'),
      mapCountry: '',
      mapChosen: false,
      listWindow: readOption(storage(), 'window', LIST_WINDOWS, '30'),
      moreTab: readOption(storage(), 'moreTab', ['events', 'downloads', 'outlinks', 'search'], 'events'),
      now: Date.now(),
      fullscreen: false,
      inWidget: false,
      unsubscribe: null,
      ticker: null,
      logColumns: 3,
      mapColumns: 3,
      resizeObserver: null
    };
  },
  mounted() {
    // the date does not matter for live data: hide the period selector like Matomo's own real-time
    // pages, but not on a dashboard, where the other widgets need it
    this.inWidget = inWidget(this.$el);
    if (!this.inWidget) {
      external_CoreHome_["Matomo"].postEvent('hidePeriodSelector');
    }
    this.unsubscribe = sharedPoller(Number(this.idSite), 0).subscribe(state => {
      this.state = state;
    });
    this.ticker = window.setInterval(() => {
      this.now = Date.now();
    }, 1000);
    document.addEventListener('fullscreenchange', this.onFullscreenChange);
    this.measureGrid();
    if (typeof ResizeObserver === 'function') {
      this.resizeObserver = new ResizeObserver(() => this.measureGrid());
      this.resizeObserver.observe(this.$refs.grid);
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
    topCountry(code) {
      if (!this.mapChosen && this.mapCountry === '' && code !== '') {
        this.mapCountry = code;
      }
    },
    pageLabel(value) {
      writeOption(storage(), 'pageLabel', value);
    },
    listWindow(value) {
      writeOption(storage(), 'window', value);
    },
    moreTab(value) {
      writeOption(storage(), 'moreTab', value);
    }
  },
  computed: {
    show() {
      return sectionsOf(this.variant);
    },
    language() {
      return pageLanguage();
    },
    fast() {
      var _this$state$data;
      return ((_this$state$data = this.state.data) === null || _this$state$data === void 0 ? void 0 : _this$state$data.payload.fast) || {};
    },
    slow() {
      var _this$state$data2;
      return ((_this$state$data2 = this.state.data) === null || _this$state$data2 === void 0 ? void 0 : _this$state$data2.payload.slow) || {};
    },
    today() {
      var _this$state$data3;
      return ((_this$state$data3 = this.state.data) === null || _this$state$data3 === void 0 ? void 0 : _this$state$data3.payload.today) || {};
    },
    status() {
      return boardStatus(this.state);
    },
    techText() {
      if (!this.techInfo || this.techInfo === '0' || this.status.kind === 'loading' || !this.state.data) {
        return '';
      }
      return techLine(this.fast, this.slow, this.tr);
    },
    statusText() {
      var _data$lag, _data$generatedAt;
      const {
        data
      } = this.state;
      switch (this.status.kind) {
        case 'loading':
          return this.tr('Loading');
        case 'offline':
          return this.tr('Offline', retryInSeconds(this.state.nextAt, this.now));
        case 'delayed':
        case 'stale':
          return this.tr('Delayed', ageText((_data$lag = data === null || data === void 0 ? void 0 : data.lag) !== null && _data$lag !== void 0 ? _data$lag : 0, plainTranslate));
        default:
          return this.tr('Updated', timeOfDay((_data$generatedAt = data === null || data === void 0 ? void 0 : data.generatedAt) !== null && _data$generatedAt !== void 0 ? _data$generatedAt : 0, this.language));
      }
    },
    canFullscreen() {
      return typeof document !== 'undefined' && !!document.fullscreenEnabled;
    },
    windowLabel() {
      return this.tr('WindowMinutes', this.listWindow);
    },
    sparkValues() {
      return sparkFor(this.slow.visitSparkline || [], this.listWindow);
    },
    sparkTop() {
      return scaleMax(Math.max(0, ...this.sparkValues));
    },
    sparkTitles() {
      const minutes = barMinutes(this.sparkValues.length, sparkEnd(this.state.data));
      return this.sparkValues.map((value, i) => this.tr(i === minutes.length - 1 ? 'BarTitleRunning' : 'BarTitle', timeOfDay(minutes[i], this.language).substring(0, 5), this.number(value)));
    },
    sparkTimeLabels() {
      const minutes = this.sparkMinutes;
      return [this.tr('MinutesAgo', minutes), this.tr('MinutesAgo', Math.round(minutes / 2)), this.tr('WindowNow')];
    },
    sparkMinutes() {
      return Math.max(30, Number(this.listWindow));
    },
    sparkBars() {
      return bars(this.sparkValues, this.sparkTop);
    },
    sparkSummary() {
      const values = this.sparkValues;
      const total = values.reduce((sum, v) => sum + v, 0);
      const busiest = Math.max(0, ...values);
      return this.tr('VisitsPerMinuteSummary', this.sparkMinutes, this.number(total), this.number(busiest));
    },
    visitsEstimated() {
      return (this.slow.kVisits || 1) > 1;
    },
    todayNote() {
      var _this$state$data4;
      // the today counters are computed every 5 minutes (the biggest read): say how old they are
      const age = (_this$state$data4 = this.state.data) === null || _this$state$data4 === void 0 ? void 0 : _this$state$data4.ages.today;
      if (age === null || age === undefined || age <= 60 || !this.state.data) {
        return '';
      }
      const time = timeOfDay(this.state.data.generatedAt - age, this.language);
      return this.tr('AsOf', time.substring(0, 5));
    },
    kpis() {
      const w = this.listWindow;
      const revenue = inWindow(this.slow.revenue, w, 0);
      const kpis = [{
        label: this.tr('VisitsWindow', w),
        value: this.number(inWindow(this.slow.visits, w, 0))
      }, {
        label: this.tr('PageviewsWindow', w),
        value: this.number(inWindow(this.slow.pageviews, w, 0))
      }, {
        label: this.tr('ConversionsWindow', w),
        value: this.number(inWindow(this.slow.conversions, w, 0))
      }];
      if (revenue > 0 || (this.today.revenue || 0) > 0) {
        kpis.push({
          label: this.tr('RevenueWindow', w),
          value: this.money(revenue)
        });
      }
      kpis.push({
        label: this.tr('VisitsToday'),
        value: this.number(this.today.visits),
        note: this.todayNote,
        title: this.tr('TodayHelp')
      });
      if ((this.today.revenue || 0) > 0) {
        kpis.push({
          label: this.tr('RevenueToday'),
          value: this.money(this.today.revenue),
          note: this.todayNote,
          title: this.tr('TodayHelp')
        });
      }
      return kpis;
    },
    logRows() {
      const {
        data
      } = this.state;
      if (!data || !this.fast.newestVisits) {
        return [];
      }
      const seen = {};
      return this.fast.newestVisits.map(visit => {
        const page = this.pageLabel === 'title' ? visit.title : pathLabel(visit.page);
        // stable while the visit is unchanged; a visit with a new action is a new row
        const base = [visit.time, visit.country, visit.source, visit.device, visit.page, visit.actions].join('|');
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
          icons: visit.icons || []
        };
      });
    },
    mapRegions() {
      return inWindow(this.slow.regions, this.listWindow, []);
    },
    /** the countries list opens only countries the map knows (not Matomo's "unknown") */
    mapCountryIds() {
      return this.list(this.slow.countries).map(row => String(row.id)).filter(id => iso3(id) !== '');
    },
    topCountry() {
      const top = this.list(this.slow.countries).find(row => iso3(String(row.id)) !== '');
      return top ? String(top.id).toLowerCase() : '';
    },
    mapCountries() {
      return this.list(this.slow.countries).map(row => ({
        code: String(row.id),
        label: row.label,
        count: row.count,
        title: this.tr('MapVisits', row.label, this.number(row.count))
      }));
    },
    mapCities() {
      return inWindow(this.slow.cities, this.listWindow, []).map(city => ({
        key: `${city.country}|${city.region}|${city.city}`,
        country: city.country,
        name: city.city,
        lat: city.lat,
        lon: city.lon,
        count: city.count,
        title: this.tr('MapVisits', city.city ? `${city.city}, ${city.countryLabel}` : city.countryLabel, this.number(city.count))
      }));
    },
    mapTexts() {
      return {
        world: this.tr('MapWorld'),
        noMap: this.tr('MapNoMap'),
        noRegions: this.tr('MapNoRegions'),
        legend: this.tr('MapLegend'),
        legendCities: this.tr('MapLegendCities', this.cityMinVisits),
        hint: this.tr('MapHint')
      };
    },
    logHeads() {
      return {
        duration: this.tr('LogDuration'),
        country: this.tr('LogCountry'),
        source: this.tr('LogSource'),
        device: this.tr('LogDevice'),
        page: this.tr('LogPage'),
        actions: this.tr('LogActions'),
        marks: this.tr('LogMarks')
      };
    },
    pagesNowRows() {
      return pagesNow(this.state.data, this.pageLabel);
    },
    topPageRows() {
      return topPages(this.state.data, this.pageLabel, this.listWindow);
    },
    goalRows() {
      const w = this.listWindow;
      const goals = inWindow(this.slow.goals, w, []);
      const rows = goals.map(goal => ({
        key: `goal${goal.id}`,
        label: goal.label,
        count: this.number(goal.conversions),
        money: goal.revenue > 0 ? this.money(goal.revenue) : ''
      }));
      const orders = inWindow(this.slow.orders, w, 0);
      if (orders > 0) {
        rows.push({
          key: 'orders',
          label: this.tr('Orders'),
          count: this.number(orders),
          money: this.money(inWindow(this.slow.orderRevenue, w, 0))
        });
      }
      return rows;
    },
    moreRows() {
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
    pageLabelOptions() {
      return [{
        value: 'url',
        label: this.tr('ByUrl')
      }, {
        value: 'title',
        label: this.tr('ByTitle')
      }];
    },
    windowOptions() {
      return LIST_WINDOWS.map(w => ({
        value: w,
        label: this.tr('Minutes', w)
      }));
    },
    moreOptions() {
      return [{
        value: 'events',
        label: this.tr('Events')
      }, {
        value: 'downloads',
        label: this.tr('Downloads')
      }, {
        value: 'outlinks',
        label: this.tr('Outlinks')
      }, {
        value: 'search',
        label: this.tr('SiteSearch')
      }];
    }
  },
  methods: {
    feedbackShown(settings, inAWidget) {
      return feedbackShown(settings, inAWidget);
    },
    mapVisits(label, count) {
      return this.tr('MapVisits', label, this.number(count));
    },
    /** Matomo has no map of the country: the one opened by itself gives way to the world map. */
    onMapMissing(code) {
      if (!this.mapChosen && code === this.mapCountry) {
        this.mapCountry = '';
        this.mapChosen = true;
      }
    },
    /** A country chosen on the world map or in the countries list ('' for the world map). */
    openCountry(code) {
      this.mapCountry = String(code).toLowerCase();
      this.mapChosen = true;
    },
    tr(key, ...values) {
      return Object(external_CoreHome_["translate"])(`FeinwerkLiveDashboard_${key}`, ...values.map(String));
    },
    /** when a tier was computed (its data time): a list compares with its last computation */
    revision(tier) {
      const {
        data
      } = this.state;
      const age = data === null || data === void 0 ? void 0 : data.ages[tier];
      return data && age !== null && age !== undefined ? String(data.generatedAt - age) : '';
    },
    list(map) {
      return inWindow(map, this.listWindow, []);
    },
    number(value) {
      return value === undefined ? '0' : formatNumber(value, this.language);
    },
    money(value) {
      return formatMoney(value || 0, this.currencySymbol, this.language);
    },
    retry() {
      sharedPoller(Number(this.idSite), 0).refresh();
    },
    toggleFullscreen() {
      if (document.fullscreenElement) {
        document.exitFullscreen().catch(() => undefined);
      } else {
        this.$el.requestFullscreen().catch(() => undefined);
      }
    },
    measureGrid() {
      const grid = this.$refs.grid;
      if (grid) {
        const template = window.getComputedStyle(grid).gridTemplateColumns;
        this.logColumns = logSpan(gridColumnCount(template));
        this.mapColumns = mapSpan(gridColumnCount(template));
      }
    },
    onFullscreenChange() {
      this.fullscreen = document.fullscreenElement === this.$el;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkLiveDashboard/vue/src/LiveBoard.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkLiveDashboard/vue/src/LiveBoard.vue



LiveBoardvue_type_script_lang_ts.render = render

/* harmony default export */ var LiveBoard = (LiveBoardvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/FeinwerkLiveDashboard/vue/src/index.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js




/***/ })

/******/ });
});
//# sourceMappingURL=FeinwerkLiveDashboard.umd.js.map