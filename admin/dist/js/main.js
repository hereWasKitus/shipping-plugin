// modules are defined as an array
// [ module function, map of requires ]
//
// map of requires is short require name -> numeric require
//
// anything defined in a previous bundle is accessed via the
// orig method which is the require for previous bundles

(function (modules, entry, mainEntry, parcelRequireName, globalName) {
  /* eslint-disable no-undef */
  var globalObject =
    typeof globalThis !== 'undefined'
      ? globalThis
      : typeof self !== 'undefined'
      ? self
      : typeof window !== 'undefined'
      ? window
      : typeof global !== 'undefined'
      ? global
      : {};
  /* eslint-enable no-undef */

  // Save the require from previous bundle to this closure if any
  var previousRequire =
    typeof globalObject[parcelRequireName] === 'function' &&
    globalObject[parcelRequireName];

  var cache = previousRequire.cache || {};
  // Do not use `require` to prevent Webpack from trying to bundle this call
  var nodeRequire =
    typeof module !== 'undefined' &&
    typeof module.require === 'function' &&
    module.require.bind(module);

  function newRequire(name, jumped) {
    if (!cache[name]) {
      if (!modules[name]) {
        // if we cannot find the module within our internal map or
        // cache jump to the current global require ie. the last bundle
        // that was added to the page.
        var currentRequire =
          typeof globalObject[parcelRequireName] === 'function' &&
          globalObject[parcelRequireName];
        if (!jumped && currentRequire) {
          return currentRequire(name, true);
        }

        // If there are other bundles on this page the require from the
        // previous one is saved to 'previousRequire'. Repeat this as
        // many times as there are bundles until the module is found or
        // we exhaust the require chain.
        if (previousRequire) {
          return previousRequire(name, true);
        }

        // Try the node require function if it exists.
        if (nodeRequire && typeof name === 'string') {
          return nodeRequire(name);
        }

        var err = new Error("Cannot find module '" + name + "'");
        err.code = 'MODULE_NOT_FOUND';
        throw err;
      }

      localRequire.resolve = resolve;
      localRequire.cache = {};

      var module = (cache[name] = new newRequire.Module(name));

      modules[name][0].call(
        module.exports,
        localRequire,
        module,
        module.exports,
        this
      );
    }

    return cache[name].exports;

    function localRequire(x) {
      var res = localRequire.resolve(x);
      return res === false ? {} : newRequire(res);
    }

    function resolve(x) {
      var id = modules[name][1][x];
      return id != null ? id : x;
    }
  }

  function Module(moduleName) {
    this.id = moduleName;
    this.bundle = newRequire;
    this.exports = {};
  }

  newRequire.isParcelRequire = true;
  newRequire.Module = Module;
  newRequire.modules = modules;
  newRequire.cache = cache;
  newRequire.parent = previousRequire;
  newRequire.register = function (id, exports) {
    modules[id] = [
      function (require, module) {
        module.exports = exports;
      },
      {},
    ];
  };

  Object.defineProperty(newRequire, 'root', {
    get: function () {
      return globalObject[parcelRequireName];
    },
  });

  globalObject[parcelRequireName] = newRequire;

  for (var i = 0; i < entry.length; i++) {
    newRequire(entry[i]);
  }

  if (mainEntry) {
    // Expose entry point to Node, AMD or browser globals
    // Based on https://github.com/ForbesLindesay/umd/blob/master/template.js
    var mainExports = newRequire(mainEntry);

    // CommonJS
    if (typeof exports === 'object' && typeof module !== 'undefined') {
      module.exports = mainExports;

      // RequireJS
    } else if (typeof define === 'function' && define.amd) {
      define(function () {
        return mainExports;
      });

      // <script>
    } else if (globalName) {
      this[globalName] = mainExports;
    }
  }
})({"kbgMO":[function(require,module,exports) {
"use strict";
var HMR_HOST = null;
var HMR_PORT = 1234;
var HMR_SECURE = false;
var HMR_ENV_HASH = "d6ea1d42532a7575";
module.bundle.HMR_BUNDLE_ID = "50b517e5a2dedd6c";
function _toConsumableArray(arr) {
    return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread();
}
function _nonIterableSpread() {
    throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
}
function _iterableToArray(iter) {
    if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter);
}
function _arrayWithoutHoles(arr) {
    if (Array.isArray(arr)) return _arrayLikeToArray(arr);
}
function _createForOfIteratorHelper(o, allowArrayLike) {
    var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"];
    if (!it) {
        if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") {
            if (it) o = it;
            var i = 0;
            var F = function F() {
            };
            return {
                s: F,
                n: function n() {
                    if (i >= o.length) return {
                        done: true
                    };
                    return {
                        done: false,
                        value: o[i++]
                    };
                },
                e: function e(_e) {
                    throw _e;
                },
                f: F
            };
        }
        throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
    }
    var normalCompletion = true, didErr = false, err;
    return {
        s: function s() {
            it = it.call(o);
        },
        n: function n() {
            var step = it.next();
            normalCompletion = step.done;
            return step;
        },
        e: function e(_e2) {
            didErr = true;
            err = _e2;
        },
        f: function f() {
            try {
                if (!normalCompletion && it.return != null) it.return();
            } finally{
                if (didErr) throw err;
            }
        }
    };
}
function _unsupportedIterableToArray(o, minLen) {
    if (!o) return;
    if (typeof o === "string") return _arrayLikeToArray(o, minLen);
    var n = Object.prototype.toString.call(o).slice(8, -1);
    if (n === "Object" && o.constructor) n = o.constructor.name;
    if (n === "Map" || n === "Set") return Array.from(o);
    if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen);
}
function _arrayLikeToArray(arr, len) {
    if (len == null || len > arr.length) len = arr.length;
    for(var i = 0, arr2 = new Array(len); i < len; i++)arr2[i] = arr[i];
    return arr2;
}
/* global HMR_HOST, HMR_PORT, HMR_ENV_HASH, HMR_SECURE */ /*::
import type {
  HMRAsset,
  HMRMessage,
} from '@parcel/reporter-dev-server/src/HMRServer.js';
interface ParcelRequire {
  (string): mixed;
  cache: {|[string]: ParcelModule|};
  hotData: mixed;
  Module: any;
  parent: ?ParcelRequire;
  isParcelRequire: true;
  modules: {|[string]: [Function, {|[string]: string|}]|};
  HMR_BUNDLE_ID: string;
  root: ParcelRequire;
}
interface ParcelModule {
  hot: {|
    data: mixed,
    accept(cb: (Function) => void): void,
    dispose(cb: (mixed) => void): void,
    // accept(deps: Array<string> | string, cb: (Function) => void): void,
    // decline(): void,
    _acceptCallbacks: Array<(Function) => void>,
    _disposeCallbacks: Array<(mixed) => void>,
  |};
}
declare var module: {bundle: ParcelRequire, ...};
declare var HMR_HOST: string;
declare var HMR_PORT: string;
declare var HMR_ENV_HASH: string;
declare var HMR_SECURE: boolean;
*/ var OVERLAY_ID = '__parcel__error__overlay__';
var OldModule = module.bundle.Module;
function Module(moduleName) {
    OldModule.call(this, moduleName);
    this.hot = {
        data: module.bundle.hotData,
        _acceptCallbacks: [],
        _disposeCallbacks: [],
        accept: function accept(fn) {
            this._acceptCallbacks.push(fn || function() {
            });
        },
        dispose: function dispose(fn) {
            this._disposeCallbacks.push(fn);
        }
    };
    module.bundle.hotData = undefined;
}
module.bundle.Module = Module;
var checkedAssets, acceptedAssets, assetsToAccept;
function getHostname() {
    return HMR_HOST || (location.protocol.indexOf('http') === 0 ? location.hostname : 'localhost');
}
function getPort() {
    return HMR_PORT || location.port;
} // eslint-disable-next-line no-redeclare
var parent = module.bundle.parent;
if ((!parent || !parent.isParcelRequire) && typeof WebSocket !== 'undefined') {
    var hostname = getHostname();
    var port = getPort();
    var protocol = HMR_SECURE || location.protocol == 'https:' && !/localhost|127.0.0.1|0.0.0.0/.test(hostname) ? 'wss' : 'ws';
    var ws = new WebSocket(protocol + '://' + hostname + (port ? ':' + port : '') + '/'); // $FlowFixMe
    ws.onmessage = function(event) {
        checkedAssets = {
        };
        acceptedAssets = {
        };
        assetsToAccept = [];
        var data = JSON.parse(event.data);
        if (data.type === 'update') {
            // Remove error overlay if there is one
            if (typeof document !== 'undefined') removeErrorOverlay();
            var assets = data.assets.filter(function(asset) {
                return asset.envHash === HMR_ENV_HASH;
            }); // Handle HMR Update
            var handled = assets.every(function(asset) {
                return asset.type === 'css' || asset.type === 'js' && hmrAcceptCheck(module.bundle.root, asset.id, asset.depsByBundle);
            });
            if (handled) {
                console.clear();
                assets.forEach(function(asset) {
                    hmrApply(module.bundle.root, asset);
                });
                for(var i = 0; i < assetsToAccept.length; i++){
                    var id = assetsToAccept[i][1];
                    if (!acceptedAssets[id]) hmrAcceptRun(assetsToAccept[i][0], id);
                }
            } else window.location.reload();
        }
        if (data.type === 'error') {
            // Log parcel errors to console
            var _iterator = _createForOfIteratorHelper(data.diagnostics.ansi), _step;
            try {
                for(_iterator.s(); !(_step = _iterator.n()).done;){
                    var ansiDiagnostic = _step.value;
                    var stack = ansiDiagnostic.codeframe ? ansiDiagnostic.codeframe : ansiDiagnostic.stack;
                    console.error('🚨 [parcel]: ' + ansiDiagnostic.message + '\n' + stack + '\n\n' + ansiDiagnostic.hints.join('\n'));
                }
            } catch (err) {
                _iterator.e(err);
            } finally{
                _iterator.f();
            }
            if (typeof document !== 'undefined') {
                // Render the fancy html overlay
                removeErrorOverlay();
                var overlay = createErrorOverlay(data.diagnostics.html); // $FlowFixMe
                document.body.appendChild(overlay);
            }
        }
    };
    ws.onerror = function(e) {
        console.error(e.message);
    };
    ws.onclose = function() {
        console.warn('[parcel] 🚨 Connection to the HMR server was lost');
    };
}
function removeErrorOverlay() {
    var overlay = document.getElementById(OVERLAY_ID);
    if (overlay) {
        overlay.remove();
        console.log('[parcel] ✨ Error resolved');
    }
}
function createErrorOverlay(diagnostics) {
    var overlay = document.createElement('div');
    overlay.id = OVERLAY_ID;
    var errorHTML = '<div style="background: black; opacity: 0.85; font-size: 16px; color: white; position: fixed; height: 100%; width: 100%; top: 0px; left: 0px; padding: 30px; font-family: Menlo, Consolas, monospace; z-index: 9999;">';
    var _iterator2 = _createForOfIteratorHelper(diagnostics), _step2;
    try {
        for(_iterator2.s(); !(_step2 = _iterator2.n()).done;){
            var diagnostic = _step2.value;
            var stack = diagnostic.codeframe ? diagnostic.codeframe : diagnostic.stack;
            errorHTML += "\n      <div>\n        <div style=\"font-size: 18px; font-weight: bold; margin-top: 20px;\">\n          \uD83D\uDEA8 ".concat(diagnostic.message, "\n        </div>\n        <pre>").concat(stack, "</pre>\n        <div>\n          ").concat(diagnostic.hints.map(function(hint) {
                return '<div>💡 ' + hint + '</div>';
            }).join(''), "\n        </div>\n        ").concat(diagnostic.documentation ? "<div>\uD83D\uDCDD <a style=\"color: violet\" href=\"".concat(diagnostic.documentation, "\" target=\"_blank\">Learn more</a></div>") : '', "\n      </div>\n    ");
        }
    } catch (err) {
        _iterator2.e(err);
    } finally{
        _iterator2.f();
    }
    errorHTML += '</div>';
    overlay.innerHTML = errorHTML;
    return overlay;
}
function getParents(bundle, id) /*: Array<[ParcelRequire, string]> */ {
    var modules = bundle.modules;
    if (!modules) return [];
    var parents = [];
    var k, d, dep;
    for(k in modules)for(d in modules[k][1]){
        dep = modules[k][1][d];
        if (dep === id || Array.isArray(dep) && dep[dep.length - 1] === id) parents.push([
            bundle,
            k
        ]);
    }
    if (bundle.parent) parents = parents.concat(getParents(bundle.parent, id));
    return parents;
}
function updateLink(link) {
    var newLink = link.cloneNode();
    newLink.onload = function() {
        if (link.parentNode !== null) // $FlowFixMe
        link.parentNode.removeChild(link);
    };
    newLink.setAttribute('href', link.getAttribute('href').split('?')[0] + '?' + Date.now()); // $FlowFixMe
    link.parentNode.insertBefore(newLink, link.nextSibling);
}
var cssTimeout = null;
function reloadCSS() {
    if (cssTimeout) return;
    cssTimeout = setTimeout(function() {
        var links = document.querySelectorAll('link[rel="stylesheet"]');
        for(var i = 0; i < links.length; i++){
            // $FlowFixMe[incompatible-type]
            var href = links[i].getAttribute('href');
            var hostname = getHostname();
            var servedFromHMRServer = hostname === 'localhost' ? new RegExp('^(https?:\\/\\/(0.0.0.0|127.0.0.1)|localhost):' + getPort()).test(href) : href.indexOf(hostname + ':' + getPort());
            var absolute = /^https?:\/\//i.test(href) && href.indexOf(window.location.origin) !== 0 && !servedFromHMRServer;
            if (!absolute) updateLink(links[i]);
        }
        cssTimeout = null;
    }, 50);
}
function hmrApply(bundle, asset) {
    var modules = bundle.modules;
    if (!modules) return;
    if (asset.type === 'css') reloadCSS();
    else if (asset.type === 'js') {
        var deps = asset.depsByBundle[bundle.HMR_BUNDLE_ID];
        if (deps) {
            if (modules[asset.id]) {
                // Remove dependencies that are removed and will become orphaned.
                // This is necessary so that if the asset is added back again, the cache is gone, and we prevent a full page reload.
                var oldDeps = modules[asset.id][1];
                for(var dep in oldDeps)if (!deps[dep] || deps[dep] !== oldDeps[dep]) {
                    var id = oldDeps[dep];
                    var parents = getParents(module.bundle.root, id);
                    if (parents.length === 1) hmrDelete(module.bundle.root, id);
                }
            }
            var fn = new Function('require', 'module', 'exports', asset.output);
            modules[asset.id] = [
                fn,
                deps
            ];
        } else if (bundle.parent) hmrApply(bundle.parent, asset);
    }
}
function hmrDelete(bundle, id1) {
    var modules = bundle.modules;
    if (!modules) return;
    if (modules[id1]) {
        // Collect dependencies that will become orphaned when this module is deleted.
        var deps = modules[id1][1];
        var orphans = [];
        for(var dep in deps){
            var parents = getParents(module.bundle.root, deps[dep]);
            if (parents.length === 1) orphans.push(deps[dep]);
        } // Delete the module. This must be done before deleting dependencies in case of circular dependencies.
        delete modules[id1];
        delete bundle.cache[id1]; // Now delete the orphans.
        orphans.forEach(function(id) {
            hmrDelete(module.bundle.root, id);
        });
    } else if (bundle.parent) hmrDelete(bundle.parent, id1);
}
function hmrAcceptCheck(bundle, id, depsByBundle) {
    if (hmrAcceptCheckOne(bundle, id, depsByBundle)) return true;
     // Traverse parents breadth first. All possible ancestries must accept the HMR update, or we'll reload.
    var parents = getParents(module.bundle.root, id);
    var accepted = false;
    while(parents.length > 0){
        var v = parents.shift();
        var a = hmrAcceptCheckOne(v[0], v[1], null);
        if (a) // If this parent accepts, stop traversing upward, but still consider siblings.
        accepted = true;
        else {
            // Otherwise, queue the parents in the next level upward.
            var p = getParents(module.bundle.root, v[1]);
            if (p.length === 0) {
                // If there are no parents, then we've reached an entry without accepting. Reload.
                accepted = false;
                break;
            }
            parents.push.apply(parents, _toConsumableArray(p));
        }
    }
    return accepted;
}
function hmrAcceptCheckOne(bundle, id, depsByBundle) {
    var modules = bundle.modules;
    if (!modules) return;
    if (depsByBundle && !depsByBundle[bundle.HMR_BUNDLE_ID]) {
        // If we reached the root bundle without finding where the asset should go,
        // there's nothing to do. Mark as "accepted" so we don't reload the page.
        if (!bundle.parent) return true;
        return hmrAcceptCheck(bundle.parent, id, depsByBundle);
    }
    if (checkedAssets[id]) return true;
    checkedAssets[id] = true;
    var cached = bundle.cache[id];
    assetsToAccept.push([
        bundle,
        id
    ]);
    if (!cached || cached.hot && cached.hot._acceptCallbacks.length) return true;
}
function hmrAcceptRun(bundle, id) {
    var cached = bundle.cache[id];
    bundle.hotData = {
    };
    if (cached && cached.hot) cached.hot.data = bundle.hotData;
    if (cached && cached.hot && cached.hot._disposeCallbacks.length) cached.hot._disposeCallbacks.forEach(function(cb) {
        cb(bundle.hotData);
    });
    delete bundle.cache[id];
    bundle(id);
    cached = bundle.cache[id];
    if (cached && cached.hot && cached.hot._acceptCallbacks.length) cached.hot._acceptCallbacks.forEach(function(cb) {
        var assetsToAlsoAccept = cb(function() {
            return getParents(module.bundle.root, id);
        });
        if (assetsToAlsoAccept && assetsToAccept.length) // $FlowFixMe[method-unbinding]
        assetsToAccept.push.apply(assetsToAccept, assetsToAlsoAccept);
    });
    acceptedAssets[id] = true;
}

},{}],"jP6N6":[function(require,module,exports) {
(($)=>$(document).ready(async ()=>{
        console.log('kek');
        const locationsToDelete = [];
        /**
   * Timepicker
   */ $('.js-timepicker:not(.js-timepicker-60)').timepicker({
            timeFormat: 'H:i'
        });
        $('.js-timepicker-60').timepicker({
            timeFormat: 'H:i',
            step: 60
        });
        /**
   * Datepicker
   */ const date1 = new Date();
        const targetDate = new Date(`${date1.getMonth() + 1} ${date1.getDate()} 2020`);
        const datesContainer = document.querySelector('#sp-public-holidays');
        const dateOptionName = document.querySelector('.sp-dates-input')?.name;
        let selectedDates = [];
        const datesFd = new FormData();
        datesFd.append('action', 'get_option');
        datesFd.append('name', dateOptionName);
        try {
            const selectedDatesResponse = await fetch(wpdata.ajaxUrl, {
                method: 'POST',
                body: datesFd
            });
            selectedDates = await selectedDatesResponse.json();
        } catch (error) {
            console.log('No date field!');
        }
        $('#sp-multi-datepicker').multiDatesPicker({
            defaultDate: targetDate,
            yearRange: `2020:2020`,
            onUpdateDatepicker () {
                datesContainer.innerHTML = '';
                let dates = $('#sp-multi-datepicker').multiDatesPicker('getDates');
                if (dates.length === 0) return;
                const formatedDates = dates.map((dateString)=>new Date(dateString)
                );
                const months = [
                    'January',
                    'February',
                    'March',
                    'April',
                    'May',
                    'June',
                    'July',
                    'August',
                    'September',
                    'October',
                    'Novermber',
                    'December'
                ];
                formatedDates.forEach((date, index)=>{
                    const tag = document.createElement('div');
                    tag.classList.add('sp-public-holiday');
                    tag.innerHTML = `${months[date.getMonth()]} ${date.getDate()} <a href="#" data-date="${dates[index]}" class="sp-public-holiday__remove js-remove-date"><i class="gg-close"></i></a>`;
                    datesContainer.append(tag);
                });
            }
        });
        if (selectedDates.length) $('#sp-multi-datepicker').multiDatesPicker('addDates', selectedDates);
        $('.sp-public-holidays-container').on('click', '.js-remove-date', (e)=>{
            e.preventDefault();
            $('#sp-multi-datepicker').multiDatesPicker('removeDates', new Date(e.currentTarget.dataset.date));
        });
        /**
   * Schedule block
   */ $('.js-add-schedule').on('click', (e)=>{
            e.preventDefault();
            const scheduleItem = createScheduleItem();
            const step = +e.currentTarget.dataset.interval || 30;
            e.currentTarget.closest('.sp-schedule-day').querySelector('.sp-schedule-day__slots').append(scheduleItem);
            $('.js-timepicker:not(.ui-timepicker-input)').timepicker({
                timeFormat: 'H:i',
                step
            });
        });
        $('.sp-schedule').on('click', '.js-remove-slot', (e)=>{
            e.preventDefault();
            e.currentTarget.closest('.sp-schedule-day__slot').remove();
        });
        function createScheduleItem() {
            let li = document.createElement('li');
            li.classList.add('sp-schedule-day__slot');
            let from = document.createElement('input');
            from.classList.add('js-timepicker');
            from.type = 'text';
            from.required = true;
            from.autocomplete = "new-password";
            from.placeholder = 'From';
            let to = document.createElement('input');
            to.classList.add('js-timepicker');
            to.type = 'text';
            to.required = true;
            to.autocomplete = "new-password";
            to.placeholder = "To";
            let removeEl = document.createElement('a');
            removeEl.classList.add('js-remove-slot');
            let removeIcon = document.createElement('i');
            removeIcon.classList.add('gg-trash');
            removeEl.append(removeIcon);
            li.append(from, to, removeEl);
            return li;
        }
        function collectScheduleValues() {
            const schedule = {
            };
            const days = document.querySelectorAll('.sp-schedule-day');
            days.forEach((dayEl)=>{
                let dayName = dayEl.dataset.day;
                let inputs = dayEl.querySelectorAll('.js-timepicker:not(.next-day-delivery)');
                schedule[dayName] = {
                    slots: [],
                    nextDayDelivery: dayEl.querySelector('.next-day-delivery').value,
                    preparationTime: dayEl.querySelector('.preparation-time').value
                };
                if (!inputs.length) return;
                for(let index = 0; index < inputs.length; index += 2)// as every slot have 2 time inputs I increase index by 2 to jump to next slot on next iteration
                schedule[dayName].slots = [
                    ...schedule[dayName].slots,
                    [
                        inputs[index].value,
                        inputs[index + 1].value
                    ]
                ];
            });
            return schedule;
        }
        /**
   * Locations
   */ if (document.querySelector('.sp-countries-container')) {
            const countriesContainer = document.querySelector('.sp-countries-list ul');
            const tableName = document.querySelector('.js-file-upload').dataset.table;
            const fd = new FormData();
            fd.set('action', 'sp_get_locations');
            fd.set('table_name', tableName);
            fetch(wpdata.ajaxUrl, {
                method: 'POST',
                body: fd
            }).then((res)=>res.json()
            ).then((data)=>{
                if (!data.length) return;
                const fragment = document.createDocumentFragment();
                countriesContainer.innerHTML = '';
                data.forEach(({ id , sku , name , price  })=>{
                    const el = createLocationElement(id, sku, name, price);
                    fragment.append(el);
                });
                countriesContainer.append(fragment);
            });
        }
        if (document.querySelector('.sp-countries-container')) {
            const countriesContainer = document.querySelector('.sp-countries-list ul');
            countriesContainer.addEventListener('change', (e)=>{
                e.target.closest('li').classList.add('changed');
            });
        }
        /**
   * CSV
   */ $('.js-file-upload').on('change', async ({ target  })=>{
            const fd = new FormData();
            fd.append('file', target.files[0]);
            fd.append('action', 'sp_get_csv_content');
            fd.append('table', target.dataset.table);
            const resp = await fetch(wpdata.ajaxUrl, {
                method: 'POST',
                body: fd
            });
            const data = await resp.json();
            const countriesContainer = document.querySelector('.sp-countries-list ul');
            const fragment = document.createDocumentFragment();
            countriesContainer.innerHTML = '';
            data.forEach(({ id , sku , name , price  })=>{
                const el = createLocationElement(id, sku, name, price);
                fragment.append(el);
            });
            countriesContainer.append(fragment);
        });
        $('.js-add-location').on('click', (e)=>{
            e.preventDefault();
            const countriesContainer = document.querySelector('.sp-countries-list ul');
            if (!countriesContainer.querySelector('input')) countriesContainer.innerHTML = '';
            const el = createLocationElement();
            countriesContainer.append(el);
        });
        $('.sp-countries-container').on('click', '.js-remove-location', (e)=>{
            e.preventDefault();
            const id = e.target.closest('li').dataset.id;
            locationsToDelete.push(id);
            e.currentTarget.parentElement.remove();
        });
        function createLocationElement(id = '', sku = '', name = '', price = '') {
            const li = document.createElement('li');
            if (id) li.dataset.id = id;
            else li.classList.add('new');
            const skuInput = document.createElement('input');
            skuInput.required = true;
            skuInput.placeholder = 'SKU';
            skuInput.type = 'text';
            skuInput.value = sku;
            skuInput.name = 'sku';
            const nameInput = document.createElement('input');
            nameInput.required = true;
            nameInput.placeholder = 'Name';
            nameInput.type = 'text';
            nameInput.value = name;
            nameInput.name = 'name';
            const priceInput = document.createElement('input');
            priceInput.required = true;
            priceInput.placeholder = 'Price';
            priceInput.type = 'number';
            priceInput.value = +price;
            priceInput.name = 'price';
            const deleteEl = document.createElement('a');
            deleteEl.classList.add('js-remove-location');
            const deleteIcon = document.createElement('i');
            deleteIcon.classList.add('gg-trash');
            deleteEl.append(deleteIcon);
            li.append(skuInput, nameInput, priceInput, deleteEl);
            return li;
        }
        /**
   *
   * @returns {Array<{sku: String, name: string, price: Number}>} Array of
   */ function collectLocationValues() {
            const locations = document.querySelectorAll('.sp-countries-list li');
            const locationValue = [];
            if (!locations[0]?.querySelector('input')) return '';
            locations.forEach((li)=>{
                locationValue.push({
                    sku: li.querySelector('[name="sku"]').value,
                    name: li.querySelector('[name="name"]').value,
                    price: li.querySelector('[name="price"]').value
                });
            });
            return locationValue;
        }
        /**
   * Another person delivery
   */ function collectAnotherPersonDeliverySettings() {
            const settingsBlocks = document.querySelectorAll('.sp-field-settings');
            settingsBlocks.forEach((item)=>{
                const inputs = item.querySelectorAll('input:not([type="hidden"])');
                const resultInput = item.querySelector('input[type="hidden"]');
                const resObj = {
                    label: inputs[0].value,
                    placeholder: inputs[1].value,
                    required: inputs[2].checked
                };
                resultInput.value = JSON.stringify(resObj);
            });
        }
        /**
   * Web components
   */ const { BlessingList  } = await require("ac0c5a1b495a9c94");
        customElements.define('blessing-list', BlessingList);
        /**
   * Blessing
   */ const blessingContainer = document.querySelector('.blessings-container');
        const addBlessingButton = document.getElementById('js-add-blessing');
        if (addBlessingButton) addBlessingButton.addEventListener('click', (e)=>{
            e.preventDefault();
            const blessing = document.createElement('blessing-list');
            blessingContainer.append(blessing);
        });
        /**
   * JSON Generator
   */ await require("2bf295f7e61118da");
        /**
   * Form submit
   */ $('.js-options-form').on('submit', async (e)=>{
            e.preventDefault();
            // Schedule
            const scheduleInput = document.querySelector('.sp-schedule-input');
            if (scheduleInput) {
                let scheduleValues = collectScheduleValues();
                scheduleInput.value = JSON.stringify(scheduleValues);
            }
            // Locations
            const locationsUl = document.querySelector('.sp-countries-container ul');
            if (locationsUl) {
                const changedItems = locationsUl.querySelectorAll('.changed:not(.new)');
                const items = [
                    ...changedItems
                ].map((li)=>({
                        id: li.dataset.id,
                        sku: li.querySelector('[name="sku"]').value,
                        name: li.querySelector('[name="name"]').value,
                        price: li.querySelector('[name="price"]').value
                    })
                );
                if (items.length) $.ajax(wpdata.ajaxUrl, {
                    type: 'POST',
                    async: false,
                    data: {
                        action: 'sp_update_locations',
                        items,
                        table: document.querySelector('.js-file-upload').dataset.table
                    },
                    complete (data) {
                        console.log('completed');
                    }
                });
            }
            if (locationsToDelete.length) $.ajax(wpdata.ajaxUrl, {
                type: 'POST',
                async: false,
                data: {
                    action: 'sp_delete_locations',
                    items: locationsToDelete,
                    table: document.querySelector('.js-file-upload').dataset.table
                }
            });
            if (locationsUl) {
                const newItems = locationsUl.querySelectorAll('.new');
                const items = [
                    ...newItems
                ].map((li)=>({
                        sku: li.querySelector('[name="sku"]').value,
                        name: li.querySelector('[name="name"]').value,
                        price: li.querySelector('[name="price"]').value
                    })
                );
                if (items.length) $.ajax(wpdata.ajaxUrl, {
                    type: 'POST',
                    async: false,
                    data: {
                        action: 'sp_insert_locations',
                        items,
                        table: document.querySelector('.js-file-upload').dataset.table
                    },
                    complete (data) {
                        console.log('completed');
                    }
                });
            }
            // Datepicker
            const datesInput = document.querySelector('.sp-dates-input');
            if (datesInput) {
                const dates = $('#sp-multi-datepicker').multiDatesPicker('getDates');
                datesInput.value = JSON.stringify(dates);
            }
            // Another person
            const anotherPersonDelivery = document.querySelector('.sp-field-settings');
            if (anotherPersonDelivery) collectAnotherPersonDeliverySettings();
            const blessingNodes = document.querySelectorAll('blessing-list');
            if (blessingNodes.length) {
                const data = [
                    ...blessingNodes
                ].map((node)=>node.getBlessings()
                );
                document.querySelector('.js-blessing-field').value = JSON.stringify(data);
            }
            e.currentTarget.submit();
        });
    })
)(jQuery);

},{"ac0c5a1b495a9c94":"2QjMu","2bf295f7e61118da":"ipyjc"}],"2QjMu":[function(require,module,exports) {
module.exports = require("./helpers/browser/js-loader")(require('./helpers/bundle-url').getBundleURL('6VBq8') + "BlessingList.f50b2295.js" + "?" + Date.now()).catch((err)=>{
    delete module.bundle.cache[module.id];
    throw err;
}).then(()=>module.bundle.root('4wmf3')
);

},{"./helpers/browser/js-loader":"61B45","./helpers/bundle-url":"lgJ39"}],"61B45":[function(require,module,exports) {
"use strict";
var cacheLoader = require('../cacheLoader');
module.exports = cacheLoader(function(bundle) {
    return new Promise(function(resolve, reject) {
        // Don't insert the same script twice (e.g. if it was already in the HTML)
        var existingScripts = document.getElementsByTagName('script');
        if ([].concat(existingScripts).some(function isCurrentBundle(script) {
            return script.src === bundle;
        })) {
            resolve();
            return;
        }
        var script1 = document.createElement('script');
        script1.async = true;
        script1.type = 'text/javascript';
        script1.charset = 'utf-8';
        script1.src = bundle;
        script1.onerror = function(e) {
            var error = new TypeError("Failed to fetch dynamically imported module: ".concat(bundle, ". Error: ").concat(e.message));
            script1.onerror = script1.onload = null;
            script1.remove();
            reject(error);
        };
        script1.onload = function() {
            script1.onerror = script1.onload = null;
            resolve();
        };
        document.getElementsByTagName('head')[0].appendChild(script1);
    });
});

},{"../cacheLoader":"j49pS"}],"j49pS":[function(require,module,exports) {
"use strict";
var cachedBundles = {
};
var cachedPreloads = {
};
var cachedPrefetches = {
};
function getCache(type) {
    switch(type){
        case 'preload':
            return cachedPreloads;
        case 'prefetch':
            return cachedPrefetches;
        default:
            return cachedBundles;
    }
}
module.exports = function(loader, type) {
    return function(bundle) {
        var cache = getCache(type);
        if (cache[bundle]) return cache[bundle];
        return cache[bundle] = loader.apply(null, arguments).catch(function(e) {
            delete cache[bundle];
            throw e;
        });
    };
};

},{}],"lgJ39":[function(require,module,exports) {
"use strict";
var bundleURL = {
};
function getBundleURLCached(id) {
    var value = bundleURL[id];
    if (!value) {
        value = getBundleURL();
        bundleURL[id] = value;
    }
    return value;
}
function getBundleURL() {
    try {
        throw new Error();
    } catch (err) {
        var matches = ('' + err.stack).match(/(https?|file|ftp):\/\/[^)\n]+/g);
        if (matches) // The first two stack frames will be this function and getBundleURLCached.
        // Use the 3rd one, which will be a runtime in the original bundle.
        return getBaseURL(matches[2]);
    }
    return '/';
}
function getBaseURL(url) {
    return ('' + url).replace(/^((?:https?|file|ftp):\/\/.+)\/[^/]+$/, '$1') + '/';
} // TODO: Replace uses with `new URL(url).origin` when ie11 is no longer supported.
function getOrigin(url) {
    var matches = ('' + url).match(/(https?|file|ftp):\/\/[^/]+/);
    if (!matches) throw new Error('Origin not found');
    return matches[0];
}
exports.getBundleURL = getBundleURLCached;
exports.getBaseURL = getBaseURL;
exports.getOrigin = getOrigin;

},{}],"ipyjc":[function(require,module,exports) {
module.exports = require("./helpers/browser/js-loader")(require('./helpers/bundle-url').getBundleURL('6VBq8') + "JSONGenerator.a8ce5cac.js" + "?" + Date.now()).catch((err)=>{
    delete module.bundle.cache[module.id];
    throw err;
}).then(()=>module.bundle.root('iqvlm')
);

},{"./helpers/browser/js-loader":"61B45","./helpers/bundle-url":"lgJ39"}]},["kbgMO","jP6N6"], "jP6N6", "parcelRequire2a8c")

//# sourceMappingURL=main.js.map
