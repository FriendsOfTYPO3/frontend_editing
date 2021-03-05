define(function() { return /******/ (function(modules) { // webpackBootstrap
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
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./node_modules/ulog/ulog.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./node_modules/anylogger/anylogger.cjs.js":
/*!*************************************************!*\
  !*** ./node_modules/anylogger/anylogger.cjs.js ***!
  \*************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("/**\r\n *  A  N  Y  L  O  G  G  E  R\r\n *  Get a logger. Any logger.\r\n *\r\n *  © 2020 by Stijn de Witt, some rights reserved\r\n *  Licensed under the MIT Open Source license\r\n *  https://opensource.org/licenses/MIT\r\n */\r\n\r\n // stores loggers keyed by name\r\nvar loggers = Object.create(null);\r\n\r\n/**\r\n * anylogger([name] [, options]) => function logger([level='log'] [, ...args])\r\n *\r\n * The main `anylogger` function creates a new or returns an existing logger\r\n * with the given `name`. It maintains a registry of all created loggers,\r\n * which it returns when called without a name, or with an empty name.\r\n *\r\n * If anylogger needs to create a new logger, it invokes\r\n * [`anylogger.new`](#anyloggernew).\r\n *\r\n * @param name {String} The name of the logger to create\r\n * @param options {Object} An optional options object\r\n *\r\n * @returns A logger with the given `name` and `options`.\r\n */\r\nvar anylogger = function(name, options){\r\n  // return the existing logger, or create a new one. if no name was given, return all loggers\r\n  return name ? loggers[name] || (loggers[name] = anylogger.ext(anylogger.new(name, options))) : loggers\r\n};\r\n\r\n/**\r\n * `anylogger.levels`\r\n *\r\n * An object containing a mapping of level names to level values.\r\n *\r\n * To be compliant with the anylogger API, loggers should support at least\r\n * the log methods corresponding to the default levels, but they may define\r\n * additional levels and they may choose to use different numeric values\r\n * for all the levels.\r\n *\r\n * The guarantees the Anylogger API makes are:\r\n * - there is a logging method corresponding to each level listed in anylogger.levels\r\n * - the levels error, warn, info, log, debug and trace are always there\r\n * - each level corresponds to a numeric value\r\n *\r\n * Note that the Anylogger API explicitly does not guarantee that all levels\r\n * have distinct values or that the numeric values will follow any pattern\r\n * or have any specific order. For this reason it is best to think of levels\r\n * as separate log channels, possibly going to different output locations.\r\n *\r\n * You can replace or change this object to include levels corresponding with\r\n * those available in the framework you are writing an adapter for. Please\r\n * make sure to always include the default levels as well so all code can\r\n * rely on the 6 console methods `error`, `warn`, `info`, `log`, `debug` and\r\n * `trace` to always be there.\r\n */\r\nanylogger.levels = { error: 1, warn: 2, info: 3, log: 4, debug: 5, trace: 6 };\r\n\r\n/**\r\n * `anylogger.new(name, options)`\r\n *\r\n * Creates a new logger function that calls `anylogger.log` when invoked.\r\n *\r\n * @param name {String} The name of the logger to create\r\n * @param options {Object} An optional options object\r\n *\r\n * @returns A new logger function with the given `name`.\r\n */\r\nanylogger.new = function(name, options) {\r\n  var result = {};\r\n  result[name] = function() {anylogger.log(name, [].slice.call(arguments));};\r\n  // some old browsers dont'create the function.name property. polyfill it for those\r\n  try {Object.defineProperty(result[name], 'name', {get:function(){return name}});} catch(e) {}\r\n  return result[name]\r\n};\r\n\r\n/**\r\n * `anylogger.log(name, args)`\r\n *\r\n * The log function used by the logger created by `anylogger.new`.\r\n *\r\n * You can override this method to change invocation behavior.\r\n *\r\n * @param name {String} The name of the logger to use. Required. Not empty.\r\n * @param args {Array} The log arguments. Required. May be empty.\r\n *\r\n * If multiple arguments were given in `args` and the first argument is a\r\n * log level name from anylogger.levels, this method will remove that argument\r\n * and call the corresponding log method with the remaining arguments.\r\n * Otherwise it will call the `log` method with the arguments given.\r\n */\r\nanylogger.log = function(name, args) {\r\n  var level = args.length > 1 && anylogger.levels[args[0]] ? args.shift() : 'log';\r\n  loggers[name][level].apply(loggers[name], args);\r\n};\r\n\r\n/**\r\n * `anylogger.ext(logger) => logger`\r\n *\r\n * Called when a logger needs to be extended, either because it was newly\r\n * created, or because it's configuration or settings changed in some way.\r\n *\r\n * This method must ensure that a log method is available on the logger for\r\n * each level in `anylogger.levels`.\r\n *\r\n * When overriding `anylogger.ext`, please ensure the function can safely\r\n * be called multiple times on the same object\r\n *\r\n * @param logger Function The logger to be (re-)extended\r\n *\r\n * @return The logger that was given, extended\r\n */\r\nanylogger.ext = function(logger) {\r\n  logger.enabledFor = function(){};\r\n  for (var method in anylogger.levels) {logger[method] = function(){};}\r\n  return logger\r\n};\n\nmodule.exports = anylogger;\n\n\n//# sourceURL=webpack:///./node_modules/anylogger/anylogger.cjs.js?");

/***/ }),

/***/ "./node_modules/kurly/parse.js":
/*!*************************************!*\
  !*** ./node_modules/kurly/parse.js ***!
  \*************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("/**\r\n * Parses a string with template tags in it into an abstract syntax tree.\r\n *\r\n * @param {Object} options An optional options object\r\n * @param {String} str The string to parse, may be null or undefined\r\n *\r\n * @returns An array, possibly empty but never null or undefined.\r\n */\r\nfunction parse(str, options) {\r\n  if (true) {\r\n    if (str && typeof str != 'string') throw new TypeError('`str` is not a string: ' + typeof str)\r\n    if (options && (typeof options != 'object')) throw new TypeError('`options` is not an object: ' + typeof options)\r\n    if (Array.isArray(options)) throw new TypeError('`options` is not an object: array')\r\n  }\r\n\r\n  var openTag = options && options.open || '{'\r\n  var closeTag = options && options.close || '}'\r\n  var opt = options && options.optional\r\n  var regex = new RegExp('(' + openTag + (opt ? '?' : '') + ')([_a-zA-Z][_a-zA-Z0-9]*)([^_a-zA-Z0-9' + closeTag + '].*)?(' + closeTag + (opt ? '?' : '') + ')')\r\n  var tag, result = []\r\n\r\n  if (str || (str === '')) {\r\n    while (tag = next(str, regex, openTag, closeTag)) {\r\n      var before = str.substring(0, tag.index)\r\n      if (before) result.push(before)\r\n      tag.ast = !options || tag.open ? parse(tag.text, options) : tag.text ? [ tag.text ] : []\r\n      str = str.substring(tag.end)\r\n      result.push(tag)\r\n    }\r\n    if (str) result.push(str)\r\n  }\r\n  return result\r\n}\r\n\r\n/**\r\n * Finds the next tag in the given `str` and returns a record with the tag\r\n * name, the index where it starts in the string, the index where it ends\r\n * and the text contained in the body of the tag.\r\n *\r\n * @param {String} str The string to search in\r\n * @param {Object} options An optional options object\r\n *\r\n * @returns {Object} The tag info object, or `undefined` if no tags were found.\r\n */\r\nfunction next(str, regex, openTag, closeTag) {\r\n  var match = str.match(regex)\r\n  if (!match) return\r\n\r\n  var result = {\r\n    index: match.index,\r\n    open: match[1],\r\n    name: match[2],\r\n    sep: '',\r\n    text: '',\r\n    close: match[4]\r\n  }\r\n\r\n  // 'naked' tags, that have no open/close markers, are space terminated\r\n  if (! result.open) {\r\n    result.end = str.indexOf(' ', result.index)\r\n    result.end = result.end === -1 ? str.length : result.end\r\n    result.text = str.substring(match.index + result.name.length, result.end)\r\n    return result\r\n  }\r\n\r\n  // tags that have open/close markers are parsed\r\n  var esc = false\r\n  var open = 1\r\n  var start = match.index+result.name.length+result.open.length\r\n  if (start == str.length) return\r\n\r\n  for (var i=start; i<str.length; i++) {\r\n    var token = str[i]\r\n    if (esc) {\r\n      token = (token == 'n') ? '\\n' :\r\n              (token == 't') ? '\\t' :\r\n              (token == openTag) ||\r\n              (token == closeTag) ||\r\n              (token == '\\\\') ? token :\r\n              '\\\\' + token // unrecognized escape sequence is ignored\r\n    }\r\n    else {\r\n      if (token === openTag) {\r\n        open++\r\n      }\r\n      if (token === closeTag) {\r\n        open--\r\n        if (!open) {\r\n          result.end = i + 1\r\n          break\r\n        }\r\n      }\r\n      if (token === '\\\\') {\r\n        esc = true\r\n        continue\r\n      }\r\n      if (!result.text && token.search(/\\s/) === 0) {\r\n        result.sep += token\r\n        continue\r\n      }\r\n    }\r\n    result.text += token\r\n    esc = false\r\n  }\r\n  return result\r\n}\r\n\r\nparse.default = parse\r\nmodule.exports = parse\r\n\n\n//# sourceURL=webpack:///./node_modules/kurly/parse.js?");

/***/ }),

/***/ "./node_modules/kurly/pipe.js":
/*!************************************!*\
  !*** ./node_modules/kurly/pipe.js ***!
  \************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("function pipe(ast, tags, rec) {\r\n  if (true) {\r\n    if ((ast === undefined) || (ast === null)) throw new Error('parameter `ast` is required')\r\n    if (! Array.isArray(ast)) throw new Error('parameter `ast` must be an array')\r\n    if ((tags === undefined) || (tags === null)) throw new Error('parameter `tags` is required')\r\n    if (typeof tags != 'object') throw new Error('parameter `tags` must be an object: ' + typeof tags)\r\n    if (Array.isArray(tags)) throw new Error('parameter `tags` must be an object: array')\r\n    if (rec && (typeof rec != 'object')) throw new Error('parameter `rec` must be an object: ' + typeof rec)\r\n    if (Array.isArray(rec)) throw new Error('parameter `rec` must be an object: array')\r\n  }\r\n\r\n  var result = ast.map(function(n){\r\n    if (!n || !n.ast) return n\r\n    var tag = tags[n.name] || tags['*']\r\n    if (!tag) return n.open + n.name + n.sep + n.text + n.close\r\n    var ctx = {}\r\n    for (var prop in n) ctx[prop] = n[prop]\r\n    ctx.ast = pipe(n.ast, tags, rec)\r\n    if (typeof tag == 'function') {\r\n      if (tag.length == 2) {\r\n        // static tag\r\n        if (rec && pipe.isStatic(ctx.ast)) {\r\n          // in a static pipe\r\n          ctx.tag = tag(ctx, rec)\r\n          ctx.tag.toString = ctx.tag\r\n        } else {\r\n          // in a dynamic pipe\r\n          ctx.tag = function(rec){\r\n            return tag(ctx, rec)()\r\n          }\r\n        }\r\n      } else {\r\n        // dynamic tag\r\n        ctx.tag = tag(ctx)\r\n      }\r\n    } else {\r\n      ctx.tag = tag\r\n    }\r\n    return ctx\r\n  })\r\n  return result\r\n}\r\n\r\npipe.isStatic = function(ast) {\r\n  return ast.reduce(function(r,n){\r\n    return r && ((typeof n == 'string') || (typeof n.tag != 'function') || (n.tag.toString === n.tag))\r\n  }, true)\r\n}\r\n\r\npipe.default = pipe\r\nmodule.exports = pipe\r\n\n\n//# sourceURL=webpack:///./node_modules/kurly/pipe.js?");

/***/ }),

/***/ "./node_modules/ulog/base.js":
/*!***********************************!*\
  !*** ./node_modules/ulog/base.js ***!
  \***********************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("(module.exports = __webpack_require__(/*! ./core */ \"./node_modules/ulog/core/index.js\")).use(\r\n  __webpack_require__(/*! ./mods/config */ \"./node_modules/ulog/mods/config/index.js\")\r\n)\n\n//# sourceURL=webpack:///./node_modules/ulog/base.js?");

/***/ }),

/***/ "./node_modules/ulog/core/grab.js":
/*!****************************************!*\
  !*** ./node_modules/ulog/core/grab.js ***!
  \****************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("var merge = __webpack_require__(/*! ./merge */ \"./node_modules/ulog/core/merge.js\")\n\nmodule.exports = function(ulog, name, result) {\n\tulog.mods.reduce(function(r,mod){\n\t\tif (Array.isArray(r) && (name in mod)) {\n\t\t\tr.push(mod[name])\n\t\t} else {\n\t\t\tmerge(r, mod[name])\n\t\t}\n\t\treturn r\n\t}, result)\n\treturn result\n}\n\n\n//# sourceURL=webpack:///./node_modules/ulog/core/grab.js?");

/***/ }),

/***/ "./node_modules/ulog/core/index.js":
/*!*****************************************!*\
  !*** ./node_modules/ulog/core/index.js ***!
  \*****************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("var ulog = __webpack_require__(/*! anylogger */ \"./node_modules/anylogger/anylogger.cjs.js\")\nvar grab = __webpack_require__(/*! ./grab */ \"./node_modules/ulog/core/grab.js\")\n\nvar ext = ulog.ext\n\n/**\n * `ulog.ext(logger) => logger`\n *\n * Called when a logger needs to be extended, either because it was newly\n * created, or because it's configuration or settings changed in some way.\n *\n * This method must ensure that a log method is available on the logger\n * for each level in `ulog.levels`.\n *\n * This override calls `ext` on all mods when a logger is extended and\n * enables calling ext on all loggers by passing no arguments.\n */\nulog.ext = function(logger) {\n\tif (logger) {\n\t\text(logger)\n\t\tgrab(ulog, 'ext', []).map(function(ext){\n\t\t\text.call(ulog, logger)\n\t\t})\n\t\tgrab(ulog, 'after', []).map(function(ext){\n\t\t\text.call(ulog, logger)\n\t\t})\n\t\treturn logger\n\t} else {\n\t\tfor (logger in ulog()) {\n\t\t\tulog.ext(ulog(logger))\n\t\t}\n\t}\n}\n\nulog.mods = []\n\n/**\n * ### `ulog.use(mod: Object|Array<Object>): Number`\n *\n * Makes ulog use `mod`.\n *\n * The mod(s) is/are added to `ulog.mods`. This function checks whether `mod`\n * is already in use and only adds it if needed. Checks whether `mod` has a key\n * `use` containing mods `mod` depends on and adding those first, guaranteeing\n * the order in which mods are added. Returns the total number of mods added,\n * including transitive dependencies.\n *\n * @param mod A single mod object or an array of mod objects.\n * @returns The number of mods that were added\n *\n * E.g.:\n * ```\n * var mod = require('./mod')\n * var solo = {} // the simplest mod is just an empty object\n * var using = {\n *   // you can declare a dependency on other mods\n *   use: [\n *     mod\n *   ]\n * }\n *\n * ulog.add(solo)  // returns 1\n * ulog.add(solo)  // returns 0, because mods are only added once\n * ulog.add(using) // returns 2, because `using` depends on `mod`\n * ulog.add(mod)   // returns 0, because `mod` was already added by `using`\n * ```\n */\nulog.use = function(mod) {\n\t// handle mod being an array of mods\n\tif (Array.isArray(mod)) {\n\t\treturn mod.reduce(function(r,mod){return r + ulog.use(mod)}, 0)\n\t}\n\t// // handle mod being a single mod\n\tvar result = ulog.mods.indexOf(mod) === -1 ? 1 : 0\n\tif (result) {\n\t\tif (mod.use) {\n\t\t\t// use dependencies\n\t\t\tresult += ulog.use(mod.use)\n\t\t}\n\t\tif (mod.extend) {\n\t\t\tfor (var name in mod.extend) {\n\t\t\t\tulog[name] = mod.extend[name]\n\t\t\t}\n\t\t}\n\t\tulog.mods.push(mod)\n\t\tif (mod.init) {\n\t\t\tmod.init.call(ulog)\n\t\t}\n\t}\n\treturn result\n}\n\n// ulog.grab = function(name){\n// \treturn ulog.mods.reduce(function(r,mod){\n// \t\tfor (var o in mod[name]) {\n// \t\t\tr[o] = mod[name][o]\n// \t\t}\n// \t\treturn r\n// \t}, {})\n// }\n\n// var recorders = []\n// for (var i=0,mod; mod=ulog.mods[i]; i++) {\n// \tif (mod.record) recorders.push(mod.record)\n// }\n\n\n// ulog.enabled = ulog.get.bind(ulog, 'debug')\n// ulog.enable = ulog.set.bind(ulog, 'debug')\n// ulog.disable = ulog.set.bind(ulog, 'debug', undefined)\n\nmodule.exports = ulog\n\n\n//# sourceURL=webpack:///./node_modules/ulog/core/index.js?");

/***/ }),

/***/ "./node_modules/ulog/core/merge.js":
/*!*****************************************!*\
  !*** ./node_modules/ulog/core/merge.js ***!
  \*****************************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("var merge = module.exports = function(result, obj) {\r\n\tfor (var o in obj) {\r\n\t\tif ((typeof obj[o] == 'object') && (Object.getPrototypeOf(obj[o]) === Object.prototype)) {\r\n\t\t\tif (! (o in result)) result[o] = {}\r\n\t\t\tif ((typeof result[o] == 'object') && (Object.getPrototypeOf(obj[o]) === Object.prototype)) {\r\n\t\t\t\tmerge(result[o], obj[o])\r\n\t\t\t} else {\r\n\t\t\t\tresult[o] = obj[o]\r\n\t\t\t}\r\n\t\t} else {\r\n\t\t\tresult[o] = obj[o]\r\n\t\t}\r\n\t}\r\n}\r\n\n\n//# sourceURL=webpack:///./node_modules/ulog/core/merge.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/align/index.js":
/*!***********************************************!*\
  !*** ./node_modules/ulog/mods/align/index.js ***!
  \***********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("// var grab = require('../../core/grab')\n// var palette = require('./utils').palette\n// var levels = require('./utils').levels\n\nvar boolean = __webpack_require__(/*! ../props/boolean */ \"./node_modules/ulog/mods/props/boolean.js\")\n\nmodule.exports = {\n  use: [\n    __webpack_require__(/*! ../props */ \"./node_modules/ulog/mods/props/index.js\"),\n  ],\n\n  settings: {\n    align: {\n      config: 'log_align',\n      prop: boolean()\n    },\n  },\n}\n\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/align/index.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/align/utils.browser.js":
/*!*******************************************************!*\
  !*** ./node_modules/ulog/mods/align/utils.browser.js ***!
  \*******************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("﻿var ZWSP = '​' // zero-width space\nvar firefox = __webpack_require__(/*! ../colors/utils */ \"./node_modules/ulog/mods/colors/utils.browser.js\").firefox\n\nmodule.exports = {\n  // alignment depends on color format specifiers in the browser\n  hasAlign: __webpack_require__(/*! ../colors/utils */ \"./node_modules/ulog/mods/colors/utils.browser.js\").hasColor,\n\n  specifier: {\n    error: '%c%s%c%s',\n    warn: '%c%s%c%s',\n    info: '%c%s%c%s',\n    log: '%c%s%c%s',\n    debug: '%c%s%c%s',\n    trace: '%c%s%c%s',\n  },\n\n  args: {\n    error: ['padding-left:0px', ZWSP, 'padding-left:0px', ZWSP],\n    warn:  ['padding-left:' + (firefox ? '12' :  '0') + 'px', ZWSP, 'padding-left:0px', ZWSP],\n    info:  ['padding-left:' + (firefox ? '12' : '10') + 'px', ZWSP, 'padding-left:0px', ZWSP],\n    log:   ['padding-left:' + (firefox ? '12' : '10') + 'px', ZWSP, 'padding-left:0px', ZWSP],\n    debug: ['padding-left:' + (firefox ? '12' : '10') + 'px', ZWSP, 'padding-left:0px', ZWSP],\n    trace: ['padding-left:0px', ZWSP, 'padding-left:0px', ZWSP],\n  },\n}\n\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/align/utils.browser.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/channels/console.js":
/*!****************************************************!*\
  !*** ./node_modules/ulog/mods/channels/console.js ***!
  \****************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("module.exports = (typeof console != 'undefined') && console\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/channels/console.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/channels/index.js":
/*!**************************************************!*\
  !*** ./node_modules/ulog/mods/channels/index.js ***!
  \**************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("var grab = __webpack_require__(/*! ../../core/grab */ \"./node_modules/ulog/core/grab.js\")\nvar console = __webpack_require__(/*! ./console */ \"./node_modules/ulog/mods/channels/console.js\")\nvar noop = __webpack_require__(/*! ./noop */ \"./node_modules/ulog/mods/channels/noop.js\")\nvar method = __webpack_require__(/*! ./method */ \"./node_modules/ulog/mods/channels/method.js\")\n\n/**\n * mod: channels\n *\n * Introduces the concept of log channels.\n *\n * A log channel is a path a log message may take that leads to an output.\n *\n * This mod enables multiple channels to be defined (by other mods) and offers\n * a hook for other mods to customize how the channel output is created.\n *\n * This mod adds two default channels named 'output' and 'drain'.\n */\nmodule.exports = {\n  use: [\n    __webpack_require__(/*! ../config */ \"./node_modules/ulog/mods/config/index.js\"),\n    // require('../options'),\n    __webpack_require__(/*! ../props */ \"./node_modules/ulog/mods/props/index.js\"),\n  ],\n\n  // adds the channels 'output' and 'drain'\n  channels: {\n    output: {\n      out: console,\n    },\n    drain: {\n      out: noop,\n    }\n  },\n\n  // enhance the given loggers with channels\n  ext: function(logger) {\n    var ulog = this\n    var channels = grab(ulog, 'channels', {})\n    var channelOutputs = grab(ulog, 'channelOutput', [])\n    var recorders = grab(ulog, 'record', [])\n    logger.channels = {}\n    for (var channel in channels) {\n      var ch = logger.channels[channel] = {\n        name: channel,\n        channels: channels,\n        out: channels[channel].out || console,\n        recorders: recorders,\n        fns: {},\n      }\n      ch.out = channelOutputs.reduce(function(r, channelOutput){\n        return channelOutput.call(ulog, logger, ch) || r\n      }, ch.out);\n      for (var level in ulog.levels) {\n        var rec = ch.recorders.reduce(function(rec, record){\n          record.call(ulog, logger, rec)\n          return rec\n        }, { channel: channel, level: level })\n        ch.fns[level] = (function(ch,rec){\n          return (typeof ch.out == 'function'\n            ? function(){\n              rec.message = [].slice.call(arguments)\n              ch.out(rec)\n            }\n            : method(ch.out, rec)\n          )\n        })(ch,rec)\n      }\n    }\n  },\n\n  // after all ext hooks have run, assign the log methods to\n  // the right channels based on logger.enabledFor\n  after: function(logger) {\n    for (var level in this.levels) {\n      logger[level] = logger.channels[logger.enabledFor(level) ? 'output' : 'drain'].fns[level]\n    }\n  },\n\n  record: function(logger, rec){\n    rec.name = logger.name\n    rec.logger = logger\n    rec.ulog = this\n  }\n}\n\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/channels/index.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/channels/method.js":
/*!***************************************************!*\
  !*** ./node_modules/ulog/mods/channels/method.js ***!
  \***************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("module.exports = function(out, rec) {\r\n  return out[rec.level] || out.log || function(){}\r\n}\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/channels/method.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/channels/noop.js":
/*!*************************************************!*\
  !*** ./node_modules/ulog/mods/channels/noop.js ***!
  \*************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("module.exports = {\r\n  log: function(){}\r\n}\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/channels/noop.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/colors/index.js":
/*!************************************************!*\
  !*** ./node_modules/ulog/mods/colors/index.js ***!
  \************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("var grab = __webpack_require__(/*! ../../core/grab */ \"./node_modules/ulog/core/grab.js\")\nvar palette = __webpack_require__(/*! ./utils */ \"./node_modules/ulog/mods/colors/utils.browser.js\").palette\nvar levels = __webpack_require__(/*! ./utils */ \"./node_modules/ulog/mods/colors/utils.browser.js\").levels\nvar boolean = __webpack_require__(/*! ../props/boolean */ \"./node_modules/ulog/mods/props/boolean.js\")\n\nmodule.exports = {\n  use: [\n    __webpack_require__(/*! ../props */ \"./node_modules/ulog/mods/props/index.js\")\n  ],\n\n  colors: {\n    palette: palette,\n    levels: levels,\n  },\n\n  settings: {\n    colored: {\n      config: 'log_color',\n      prop: boolean(),\n    },\n  },\n\n  record: function(logger, rec) {\n    if (logger.colored) {\n      if (!logger.colors) {\n        logger.colors = grab(this, 'colors', {})\n        logger.colors.index = hash(logger.name) % logger.colors.palette.length\n      }\n      if (!logger.color) {\n        logger.color = logger.colors.palette[logger.colors.index]\n      }\n    }\n  }\n}\n\nfunction hash(s) {\n  for (var i=0, h=0xdeadbeef; i<s.length; i++)\n      h = imul(h ^ s.charCodeAt(i), 2654435761)\n  return (h ^ h >>> 16) >>> 0\n}\n\n// https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Math/imul#Polyfill\nfunction imul(a, b) {\n  b |= 0\n  var result = (a & 0x003fffff) * b;\n  if (a & 0xffc00000 /*!== 0*/) result += (a & 0xffc00000) * b |0;\n  return result |0;\n}\n\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/colors/index.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/colors/utils.browser.js":
/*!********************************************************!*\
  !*** ./node_modules/ulog/mods/colors/utils.browser.js ***!
  \********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("var console = __webpack_require__(/*! ../channels/console */ \"./node_modules/ulog/mods/channels/console.js\")\n\nmodule.exports = {\n  // Detect firefox to compensate for it's lack of support for format specifiers on console.trace\n  firefox: (typeof navigator != 'undefined') && /firefox/i.test(navigator.userAgent),\n\n  hasColor: function(output){\n    return (output === console) &&\n        (navigator.userAgent.indexOf('MSIE') === -1) &&\n        (navigator.userAgent.indexOf('Trident') === -1)\n  },\n\n  colorSpecifier: function(color){\n    return '%c'\n  },\n\n  colorSpecifierAfter: function(color){\n    return ''\n  },\n\n  colorArgument: function(color){\n    return ['color:rgb(' + color.r + ',' + color.g + ',' + color.b + ')']\n  },\n\n  palette: (function() {\n    var palette = []\n    for (var r=0; r<8; r++) {\n      for (var g=0; g<8; g++) {\n        for (var b=0; b<8; b++) {\n          if ((r + g + b > 8) && (r + g + b < 16)) // filter out darkest and lightest colors\n          palette.push({r:24*r, g:24*g, b:24*b})\n        }\n      }\n    }\n    return palette\n  })(),\n\n  levels: {\n    error: { r: 192, g:  64, b:   0 },\n    warn:  { r: 180, g:  96, b:   0 },\n    info:  { r:  64, g: 128, b:  16 },\n    log:   { r:  64, g:  64, b:  64 },\n    debug: { r:  96, g:  96, b:  96 },\n    trace: { r: 112, g: 112, b: 112 },\n  },\n}\n\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/colors/utils.browser.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/config/args.browser.js":
/*!*******************************************************!*\
  !*** ./node_modules/ulog/mods/config/args.browser.js ***!
  \*******************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("var parse = __webpack_require__(/*! ./parse */ \"./node_modules/ulog/mods/config/parse.js\")\n\nmodule.exports = parse(typeof location == 'undefined' ? [] : location.search.replace(/^(\\?|#|&)/, '').split('&'),\n  /\\+/g, ' ', decodeURIComponent\n)\n\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/config/args.browser.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/config/configure.js":
/*!****************************************************!*\
  !*** ./node_modules/ulog/mods/config/configure.js ***!
  \****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("var merge = __webpack_require__(/*! ../../core/merge */ \"./node_modules/ulog/core/merge.js\")\r\nvar env = __webpack_require__(/*! ./env */ \"./node_modules/ulog/mods/config/env.browser.js\")\r\nvar args = __webpack_require__(/*! ./args */ \"./node_modules/ulog/mods/config/args.browser.js\")\r\n\r\nmodule.exports = function(watched, data) {\r\n  var cfg = {}\r\n  merge(cfg, env)\r\n  merge(cfg, args)\r\n  data && merge(cfg, data)\r\n  // var result = {}\r\n  // for (var setting in watched) {\r\n  //   if (setting in cfg) result[setting] = cfg[setting]\r\n  // }\r\n  return cfg\r\n}\r\n\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/config/configure.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/config/env.browser.js":
/*!******************************************************!*\
  !*** ./node_modules/ulog/mods/config/env.browser.js ***!
  \******************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("module.exports = {}\r\n\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/config/env.browser.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/config/index.js":
/*!************************************************!*\
  !*** ./node_modules/ulog/mods/config/index.js ***!
  \************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("var grab = __webpack_require__(/*! ../../core/grab */ \"./node_modules/ulog/core/grab.js\")\n// var args = require('./args')\n// var env = require('./env')\nvar read = __webpack_require__(/*! ./read */ \"./node_modules/ulog/mods/config/read.browser.js\")\nvar update = __webpack_require__(/*! ./update */ \"./node_modules/ulog/mods/config/update.js\")\nvar notify = __webpack_require__(/*! ./notify */ \"./node_modules/ulog/mods/config/notify.js\")\nvar watch = __webpack_require__(/*! ./watch */ \"./node_modules/ulog/mods/config/watch.browser.js\")\nvar config = module.exports = {\n  use: [\n    __webpack_require__(/*! ../settings */ \"./node_modules/ulog/mods/settings/index.js\"),\n  ],\n\n  settings: {\n    config: {\n      config: 'log_config'\n    }\n  },\n\n  init: function(){\n    this.get('config')\n  },\n\n  get: function(result, name) {\n    if (! this.config) {\n      config.update(this)\n    }\n    if (!result) {\n      var settings = grab(this, 'settings', {})\n      name = settings[name] && settings[name].config || name\n      result = this.config[name]\n    }\n    return result\n  },\n\n  update: function(ulog) {\n    ulog.config = ulog.config || {}\n    var newCfg = read(ulog)\n    var changed = update(ulog.config, newCfg)\n    if (changed.length) notify(ulog, changed)\n    watch(ulog)\n  },\n\n  set: function(name) {\n    if (name === 'log_config') config.update(this)\n  }\n}\n\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/config/index.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/config/notify.js":
/*!*************************************************!*\
  !*** ./node_modules/ulog/mods/config/notify.js ***!
  \*************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("var watches = __webpack_require__(/*! ./watches */ \"./node_modules/ulog/mods/config/watches.js\")\r\n\r\nmodule.exports = function(ulog, changed) {\r\n  ulog.ext()\r\n  var watched = watches(ulog)\r\n\r\n  changed.map(function(change){\r\n    return { change: change, watches: watched.filter(function(watch){return typeof watch[change.name] == 'function'}) }\r\n  })\r\n  .filter(function(item){\r\n    return item.watches.length\r\n  })\r\n  .forEach(function(item){\r\n    item.watches.forEach(function(watch) {\r\n      setTimeout(function(){\r\n        watch[item.change.name].call(ulog, item.change)\r\n      },0)\r\n    })\r\n  })\r\n}\r\n\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/config/notify.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/config/parse.js":
/*!************************************************!*\
  !*** ./node_modules/ulog/mods/config/parse.js ***!
  \************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("module.exports = function parse(data, regex, replacement, decode) {\r\n  return data.reduce(function(result,value){\r\n    value = value.replace(regex, replacement)\r\n    var i = value.indexOf('=')\r\n    if (i !== -1) {\r\n      var n = value.substring(0, i).replace(/^\\s+|\\s+$/g, '')\r\n      if (n) {\r\n        var v = value.substring(i + 1).replace(/^\\s+|\\s+$/g, '')\r\n        if (decode) v = decode(v)\r\n        result[n] = v\r\n      }\r\n    }\r\n    return result\r\n  }, {})\r\n}\r\n\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/config/parse.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/config/read.browser.js":
/*!*******************************************************!*\
  !*** ./node_modules/ulog/mods/config/read.browser.js ***!
  \*******************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("\r\nvar configure = __webpack_require__(/*! ./configure */ \"./node_modules/ulog/mods/config/configure.js\")\r\nvar watched = __webpack_require__(/*! ./watched */ \"./node_modules/ulog/mods/config/watched.js\")\r\n\r\nmodule.exports = function(ulog, callback) {\r\n  var watches = watched(ulog)\r\n\r\n  var cfg = {}\r\n  for (var name in watches) {\r\n    try {\r\n      var value = localStorage.getItem(name)\r\n      if (value) cfg[name] = value\r\n    } catch(ignore){}\r\n  }\r\n\r\n  cfg = configure(watches, cfg)\r\n  return callback ? callback(cfg) : cfg\r\n}\r\n\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/config/read.browser.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/config/update.js":
/*!*************************************************!*\
  !*** ./node_modules/ulog/mods/config/update.js ***!
  \*************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("module.exports = function(cfg, newCfg) {\r\n  var name, changes = []\r\n  for (name in cfg) {\r\n    if (! (name in newCfg)) {\r\n      changes.push({ name: name, old: cfg[name] })\r\n      delete cfg[name]\r\n    }\r\n  }\r\n  for (name in newCfg) {\r\n    if ((! (name in cfg)) || (cfg[name] !== newCfg[name])) {\r\n      if (! (name in cfg)) {\r\n        changes.push({ name: name, new: newCfg[name] })\r\n      } else {\r\n        changes.push({ name: name, old: cfg[name], new: newCfg[name] })\r\n      }\r\n      cfg[name] = newCfg[name]\r\n    }\r\n  }\r\n  return changes\r\n}\r\n\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/config/update.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/config/watch.browser.js":
/*!********************************************************!*\
  !*** ./node_modules/ulog/mods/config/watch.browser.js ***!
  \********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("var read = __webpack_require__(/*! ./read */ \"./node_modules/ulog/mods/config/read.browser.js\")\nvar update = __webpack_require__(/*! ./update */ \"./node_modules/ulog/mods/config/update.js\")\nvar notify = __webpack_require__(/*! ./notify */ \"./node_modules/ulog/mods/config/notify.js\")\n\nmodule.exports = function(ulog) {\n  // storage events unfortunately only fire on events triggered by other windows...\n  // so we need to poll here...\n  setInterval(function(){\n    if (ulog.config) {\n      var cfg = read(ulog)\n      setTimeout(function(){\n        var changed = update(ulog.config, cfg)\n        if (changed.length) setTimeout(function(){\n          notify(ulog, changed)\n        }, 0)\n      }, 0)\n    }\n  }, 350)\n}\n\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/config/watch.browser.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/config/watched.js":
/*!**************************************************!*\
  !*** ./node_modules/ulog/mods/config/watched.js ***!
  \**************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("var grab = __webpack_require__(/*! ../../core/grab */ \"./node_modules/ulog/core/grab.js\")\r\nvar watches = __webpack_require__(/*! ./watches */ \"./node_modules/ulog/mods/config/watches.js\")\r\n\r\nmodule.exports = function(ulog){\r\n  var settings = grab(ulog, 'settings', {})\r\n  var watchers = watches(ulog)\r\n  var watched = {}\r\n  watchers.forEach(function(watcher){\r\n    for (var name in watcher) {\r\n      watched[name] = watchers[name]\r\n    }\r\n  })\r\n  for (var setting in settings) {\r\n    var name = (settings[setting] && settings[setting].config) || setting\r\n    watched[name] = settings[setting]\r\n  }\r\n  return watched\r\n}\r\n\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/config/watched.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/config/watches.js":
/*!**************************************************!*\
  !*** ./node_modules/ulog/mods/config/watches.js ***!
  \**************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("var grab = __webpack_require__(/*! ../../core/grab */ \"./node_modules/ulog/core/grab.js\")\r\n\r\nmodule.exports = function(ulog){\r\n  return grab(ulog, 'watch', []).map(function(watch){\r\n    var result = {}\r\n    for (var key in watch) {\r\n      key.split(',').forEach(function(name){\r\n        result[name] = watch[key]\r\n      })\r\n    }\r\n    return result\r\n  })\r\n}\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/config/watches.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/debug/index.js":
/*!***********************************************!*\
  !*** ./node_modules/ulog/mods/debug/index.js ***!
  \***********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("module.exports = {\r\n  use: [\r\n    __webpack_require__(/*! ../levels */ \"./node_modules/ulog/mods/levels/index.js\"),\r\n  ],\r\n\r\n  init: function() {\r\n    this.enabled = this.get.bind(this, 'debug')\r\n    this.enable = this.set.bind(this, 'debug')\r\n    this.disable = this.set.bind(this, 'debug', '')\r\n  }\r\n}\r\n\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/debug/index.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/formats/apply-alignment.js":
/*!***********************************************************!*\
  !*** ./node_modules/ulog/mods/formats/apply-alignment.js ***!
  \***********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("var alignment = __webpack_require__(/*! ../align/utils */ \"./node_modules/ulog/mods/align/utils.browser.js\")\nvar hasAlign = alignment.hasAlign\n\nmodule.exports = function(rec, r){\n  var a = hasAlign(rec.logger.channels[rec.channel].out) && rec.logger.align && alignment\n  r[0] = ((a && a.specifier && a.specifier[rec.level]) || '') + r[0]\n  r.splice.apply(r, [1, 0].concat((a && a.args && a.args[rec.level]) || []))\n  return r\n}\n\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/formats/apply-alignment.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/formats/apply-formatting.js":
/*!************************************************************!*\
  !*** ./node_modules/ulog/mods/formats/apply-formatting.js ***!
  \************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("var console = __webpack_require__(/*! ../channels/console */ \"./node_modules/ulog/mods/channels/console.js\")\nvar hasColor = __webpack_require__(/*! ../colors/utils */ \"./node_modules/ulog/mods/colors/utils.browser.js\").hasColor\nvar colorSpecifier = __webpack_require__(/*! ../colors/utils */ \"./node_modules/ulog/mods/colors/utils.browser.js\").colorSpecifier\nvar colorArgument = __webpack_require__(/*! ../colors/utils */ \"./node_modules/ulog/mods/colors/utils.browser.js\").colorArgument\nvar colorSpecifierAfter = __webpack_require__(/*! ../colors/utils */ \"./node_modules/ulog/mods/colors/utils.browser.js\").colorSpecifierAfter\n\nmodule.exports = function(rec, fmt, msg, r){\n  var out = rec.logger.channels[rec.channel].out\n  if (out === console) {\n    var colored = hasColor(out)\n    var c = colored && rec.logger.colored && fmt.color\n    c = c == 'level' ? rec.logger.colors.levels[rec.level] : c\n    c = c == 'logger' ? rec.logger.color : c\n    r[0] += (c && colorSpecifier(c)) || ''\n    var len = Array.isArray(msg) ? msg.length : 1\n    for (var i=0; i<len; i++) {\n      var m = Array.isArray(msg) ? msg[i] : msg\n      r[0] += fmt.specifier || (typeof m == 'object' ? '%O ' : '%s ')\n    }\n    r[0] += (c && colorSpecifierAfter(c)) || ''\n    r.push.apply(r, (c && colorArgument(c)) || [])\n  }\n  r.push.apply(r, Array.isArray(msg) ? msg : [ msg ])\n  return r\n}\n\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/formats/apply-formatting.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/formats/date.js":
/*!************************************************!*\
  !*** ./node_modules/ulog/mods/formats/date.js ***!
  \************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("var formatter = __webpack_require__(/*! ../formats/formatter */ \"./node_modules/ulog/mods/formats/formatter.js\")\nvar pad = __webpack_require__(/*! ../formats/pad */ \"./node_modules/ulog/mods/formats/pad.js\")\n\nmodule.exports = function(ctx, rec){\n  return formatter(ctx, rec, { color: 'logger' }, function() {\n    var time = new Date()\n    return time.getFullYear() + '/' +\n        pad(time.getMonth().toString(), 2, '0', pad.LEFT) + '/' +\n        pad(time.getDate().toString(), 2, '0', pad.LEFT)\n  })\n}\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/formats/date.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/formats/default.browser.js":
/*!***********************************************************!*\
  !*** ./node_modules/ulog/mods/formats/default.browser.js ***!
  \***********************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("module.exports = 'lvl name perf'\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/formats/default.browser.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/formats/formatter.js":
/*!*****************************************************!*\
  !*** ./node_modules/ulog/mods/formats/formatter.js ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("var pad = __webpack_require__(/*! ./pad */ \"./node_modules/ulog/mods/formats/pad.js\")\n\nmodule.exports = function formatter(ctx, rec, props, fn) {\n  if (! fn) {fn = props; props = rec; rec = undefined}\n  var dir = props.dir === pad.LEFT ? pad.LEFT : pad.RIGHT, padding = props.padding || 0\n  ctx.text && ctx.text.split(':').forEach(function(text){\n    if (text[0] == '>') dir = pad.LEFT\n    if (text[0] == '<') dir = pad.RIGHT\n    text = (text[0] == '>') || (text[0] == '<') ? text.substring(1) : text\n    if (Number(text) && (Number(text) === Number(text))) padding = Number(text)\n  })\n  var fmt = function(rec) {\n    var result = fn(rec)\n    if (Array.isArray(result) && (result.length == 1) && (typeof result[0] == 'string'))\n    result = result[0]\n    if (padding && (typeof result == 'string')) result = pad(result, padding, ' ', dir)\n    return result\n  }\n  var result = rec ? function() {return fmt(rec)} : function(rec){return fmt(rec)}\n  for (var prop in props) {\n    result[prop] = props[prop]\n  }\n  return result\n}\n\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/formats/formatter.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/formats/index.js":
/*!*************************************************!*\
  !*** ./node_modules/ulog/mods/formats/index.js ***!
  \*************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("var parse = __webpack_require__(/*! kurly/parse */ \"./node_modules/kurly/parse.js\")\nvar pipe = __webpack_require__(/*! kurly/pipe */ \"./node_modules/kurly/pipe.js\")\nvar grab = __webpack_require__(/*! ../../core/grab */ \"./node_modules/ulog/core/grab.js\")\nvar console = __webpack_require__(/*! ../channels/console */ \"./node_modules/ulog/mods/channels/console.js\")\nvar makeStatic = __webpack_require__(/*! ./utils */ \"./node_modules/ulog/mods/formats/utils.browser.js\").makeStatic\nvar makeStaticPipe = __webpack_require__(/*! ./utils */ \"./node_modules/ulog/mods/formats/utils.browser.js\").makeStaticPipe\nvar applyFormatting = __webpack_require__(/*! ./apply-formatting */ \"./node_modules/ulog/mods/formats/apply-formatting.js\")\nvar applyAlignment = __webpack_require__(/*! ./apply-alignment */ \"./node_modules/ulog/mods/formats/apply-alignment.js\")\n\n/**\n * mod - formats\n *\n * Makes log formatting configurable and extendable\n */\nmodule.exports = {\n  use: [\n    __webpack_require__(/*! ../channels */ \"./node_modules/ulog/mods/channels/index.js\"),\n  ],\n\n  settings: {\n    format: {\n      config: 'log_format',\n      prop: {\n        default: __webpack_require__(/*! ./default */ \"./node_modules/ulog/mods/formats/default.browser.js\"),\n      }\n    },\n  },\n\n  formats: {\n    // add a bunch of formats\n    cr: function(ctx,rec){return function(){return '\\r\\n'}},\n    date: __webpack_require__(/*! ./date */ \"./node_modules/ulog/mods/formats/date.js\"),\n    lvl: __webpack_require__(/*! ./lvl */ \"./node_modules/ulog/mods/formats/lvl.js\"),\n    message: __webpack_require__(/*! ./message */ \"./node_modules/ulog/mods/formats/message.js\"),\n    name: __webpack_require__(/*! ./name */ \"./node_modules/ulog/mods/formats/name.js\"),\n    perf: __webpack_require__(/*! ./perf */ \"./node_modules/ulog/mods/formats/perf.js\"),\n    time: __webpack_require__(/*! ./time */ \"./node_modules/ulog/mods/formats/time.js\"),\n    '*': __webpack_require__(/*! ./wildcard */ \"./node_modules/ulog/mods/formats/wildcard.js\"),\n  },\n\n  ext: function(logger) {\n    var ulog = this\n    for (var channel in logger.channels) {\n      for (var level in ulog.levels) {\n        logger.channels[channel].fns[level] = makePipe(ulog, logger, channel, level)\n      }\n    }\n\n    function makePipe(ulog, logger, channel, level) {\n      var formats = grab(ulog, 'formats', {})\n      var ast = parse(logger.format, { optional: true })\n      var rec = logger.channels[channel].recorders.reduce(function(rec, record){\n        record.call(ulog, logger, rec)\n        return rec\n      }, { channel: channel, level: level })\n      var line = pipe(ast, formats, rec)\n      var ch = logger.channels[channel]\n      var method = ch.fns[level]\n      var output = ch.out\n\n      if ((output === console) && pipe.isStatic(line)) {\n        // derive the arguments to be bound from the pipeline\n        var args = line\n        .map(toTag)\n        .filter(skip)\n        .reduce(function(r,fmt){\n          var msg = makeStatic(fmt)\n          return applyFormatting(rec, fmt, msg, r)\n        }, [''])\n        // apply alignment if needed\n        applyAlignment(rec, args)\n        // bind the output and arguments to the log method\n        // this uses a devious trick to apply formatting without\n        // actually replacing the original method, and thus\n        // without mangling the call stack\n        return makeStaticPipe(output, method, rec, args)\n      } else {\n        return makeDynamicPipe(output, method, rec, line)\n      }\n  }\n\n    function makeDynamicPipe(output, method, rec, line) {\n     // set up a dynamic pipeline as a function\n     var containsMessage = line.reduce(function(r,node){return r || (node && node.name === 'message')}, false)\n     return (function(rec){return function() {\n        // arguments to this function are the message\n        rec.message = [].slice.call(arguments)\n        // run through the pipeline, running all formatters\n        var args = line\n        .map(toTag)\n        .filter(skip)\n        .reduce(function(r,fmt){\n          var msg = typeof fmt == 'function' ? fmt(rec) : fmt\n          return applyFormatting(rec, fmt, msg, r)\n        }, [''])\n        if (! containsMessage) args.push.apply(args, rec.message)\n        // apply alignment if needed\n        applyAlignment(rec, args)\n        // pass the formatted arguments on to the original output method\n        method.apply(output, args)\n      }})(rec)\n    }\n\n    function toTag(node) {return (\n      !node || !node.tag ? node :\n      node.tag\n    )}\n\n    function skip(tag){return (typeof tag != 'string') || tag.trim().length}\n  },\n}\n\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/formats/index.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/formats/lvl.js":
/*!***********************************************!*\
  !*** ./node_modules/ulog/mods/formats/lvl.js ***!
  \***********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("var formatter = __webpack_require__(/*! ./formatter */ \"./node_modules/ulog/mods/formats/formatter.js\")\n\nmodule.exports = function(ctx, rec){\n  return formatter(ctx, rec, { color: 'level' }, function(){\n    return [' ', 'x', '!', 'i', '-', '>', '}'][rec.ulog.levels[rec.level]]\n  })\n}\n\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/formats/lvl.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/formats/message.js":
/*!***************************************************!*\
  !*** ./node_modules/ulog/mods/formats/message.js ***!
  \***************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("var formatter = __webpack_require__(/*! ../formats/formatter */ \"./node_modules/ulog/mods/formats/formatter.js\")\n\nmodule.exports = function(ctx) {\n  return formatter(ctx, { color: 'level' }, function(rec){\n    return rec.message\n  })\n}\n\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/formats/message.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/formats/name.js":
/*!************************************************!*\
  !*** ./node_modules/ulog/mods/formats/name.js ***!
  \************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("var formatter = __webpack_require__(/*! ./formatter */ \"./node_modules/ulog/mods/formats/formatter.js\")\n\nmodule.exports = function(ctx, rec) {\n  return formatter(ctx, rec, { color: 'logger', padding: 16 }, function(){\n    return rec.logger.name\n  })\n}\n\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/formats/name.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/formats/pad.js":
/*!***********************************************!*\
  !*** ./node_modules/ulog/mods/formats/pad.js ***!
  \***********************************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("var pad = module.exports = function(s, len, c, left){\r\n  var s = s.substring(0, len)\r\n  for (var i=len-s.length; i>0; i--)\r\n    s = left ? (c || ' ') + s : s + (c || ' ')\r\n  return s\r\n}\r\npad.RIGHT = 0\r\npad.LEFT = 1\r\n\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/formats/pad.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/formats/perf.js":
/*!************************************************!*\
  !*** ./node_modules/ulog/mods/formats/perf.js ***!
  \************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("var formatter = __webpack_require__(/*! ./formatter */ \"./node_modules/ulog/mods/formats/formatter.js\")\n\nmodule.exports = function(ctx, rec) {\n  return formatter(ctx, rec, { color: 'logger', padding: 6, dir: 1 }, function(){\n    var time = new Date()\n    rec.logger.lastCalled = rec.logger.lastCalled || time\n    var ms = time.getTime() - rec.logger.lastCalled.getTime()\n    rec.logger.lastCalled = time\n    return (\n      ms >= 36000000 ?  (ms/3600000).toFixed(1) + 'h' :\n      ms >=   600000 ?    (ms/60000).toFixed(ms >= 6000000 ? 1 : 2) + 'm' :\n      ms >=    10000 ?     (ms/1000).toFixed(ms >=  100000 ? 1 : 2) + 's' :\n      // a one-ms diff is bound to occur at some point,\n      // but it doesn't really mean anything as it's\n      // basically just the next clock tick, so only\n      // show values > 1\n      ms >        1 ?                      ms + 'ms' :\n      ''\n    )\n  })\n}\n\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/formats/perf.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/formats/time.js":
/*!************************************************!*\
  !*** ./node_modules/ulog/mods/formats/time.js ***!
  \************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("var formatter = __webpack_require__(/*! ../formats/formatter */ \"./node_modules/ulog/mods/formats/formatter.js\")\nvar pad = __webpack_require__(/*! ../formats/pad */ \"./node_modules/ulog/mods/formats/pad.js\")\n\nmodule.exports = function(ctx, rec){\n  return formatter(ctx, rec, { color: 'logger' }, function() {\n    var time = new Date()\n    return pad(time.getHours().toString(), 2, '0', pad.LEFT) + ':' +\n        pad(time.getMinutes().toString(), 2, '0', pad.LEFT)\n  })\n}\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/formats/time.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/formats/utils.browser.js":
/*!*********************************************************!*\
  !*** ./node_modules/ulog/mods/formats/utils.browser.js ***!
  \*********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("// Detect V8 in browsers to compensate for a bug where toString is called twice\nvar bug = (typeof Intl != 'undefined') && Intl.v8BreakIterator\nvar firefox = __webpack_require__(/*! ../colors/utils */ \"./node_modules/ulog/mods/colors/utils.browser.js\").firefox\n\nmodule.exports.makeStatic = function(fmt){\n  if (bug && (typeof fmt == 'function') && (fmt.toString === fmt)) {\n    var skip = bug\n    fmt.toString = function(){\n      if (skip) return skip = ''\n      skip = bug\n      return fmt()\n    }\n  }\n  return fmt\n}\n\nmodule.exports.makeStaticPipe = function(output, method, rec, args) {\n  return method.bind.apply(method, [output].concat(firefox && (rec.level === 'trace') ? [] : args))\n}\n\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/formats/utils.browser.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/formats/wildcard.js":
/*!****************************************************!*\
  !*** ./node_modules/ulog/mods/formats/wildcard.js ***!
  \****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("var formatter = __webpack_require__(/*! ./formatter */ \"./node_modules/ulog/mods/formats/formatter.js\")\n\nmodule.exports = function(ctx, rec) {\n  return formatter(ctx, rec, { color: 'level' }, function() {\n    return ctx.name in rec ? rec[ctx.name] : ctx.name + (ctx.text ? ctx.text : '')\n  })\n}\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/formats/wildcard.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/levels/default.browser.js":
/*!**********************************************************!*\
  !*** ./node_modules/ulog/mods/levels/default.browser.js ***!
  \**********************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("module.exports = 'warn'\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/levels/default.browser.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/levels/index.js":
/*!************************************************!*\
  !*** ./node_modules/ulog/mods/levels/index.js ***!
  \************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("module.exports = {\n  use: [\n    __webpack_require__(/*! ../channels */ \"./node_modules/ulog/mods/channels/index.js\"),\n  ],\n\n  settings: {\n    debug: {},\n    level: {\n      config: 'log',\n      // level property\n      prop: {\n        // default value\n        default: __webpack_require__(/*! ./default.js */ \"./node_modules/ulog/mods/levels/default.browser.js\"),\n        // level number from string\n        fromStr: function(v) {\n          return Number(v) === Number(v) ? Number(v) : v && this[v.toUpperCase()]\n        },\n        // level number to string\n        toStr: function(v) {\n          for (var x in this)\n            if (this[x] === v) return x.toLowerCase()\n          return v\n        },\n        // property getter extension, called when property on logger is read\n        get: function(v, ulog){\n          return Math.max(ulog.get('debug', this.name) && this.DEBUG || this.NONE, v)\n        },\n      },\n    },\n  },\n\n  ext: function(logger) {\n    logger.NONE = 0\n    logger.ALL = 7\n    for (var level in this.levels) {\n      logger[level.toUpperCase()] = this.levels[level]\n    }\n    logger.enabledFor = function(level){\n      return logger.level >= logger[level.toUpperCase()]\n    }\n  },\n}\n\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/levels/index.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/options/index.js":
/*!*************************************************!*\
  !*** ./node_modules/ulog/mods/options/index.js ***!
  \*************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("// options are smart settings....\nvar options = module.exports = {\n  use: [\n    __webpack_require__(/*! ../settings */ \"./node_modules/ulog/mods/settings/index.js\")\n  ],\n\n  /**\n   * `options.get(result: String|Object, name?: String, loggerName?: String): String|Object`\n   *\n   * @param {String|Object} result The result found so far.\n   * @param {String} name The name of the setting to get. Optional.\n   * @param {String} loggerName The name of the logger to get the effective setting for. Optional.\n   * @returns {String|Object} The (effective) setting for the given `name` and `loggerName`.\n   *\n   * if no `loggerName` is given, returns the `result` unchanged.\n   *\n   * If a `loggerName` is given, the effective setting value for that specific\n   * logger name is returned.\n   *\n   * If empty string is given as `loggerName`, the effective global/default\n   * setting value is returned.\n   *\n   * For example, given that the following settings are active:\n   *\n   * `{ level: 'info; libA=warn; libB=error', output: 'console' }`\n   *\n   * These following statements would be true:\n   *\n   * `JSON.stringify(ulog.get()) == '{\"level\":\"info; libA=warn; libB=error\",\"output\":\"console\"}'`\n   * `ulog.get('output') == 'console`\n   * `ulog.get('level') == 'info; libA=warn; libB=error'`\n   * `ulog.get('level', 'libA') == 'warn'`\n   * `ulog.get('level', 'libB') == 'error'`\n   * `ulog.get('level', 'libC') == 'info'`\n   * `ulog.get('level', '') == 'info'`\n   */\n  get: function(result, name, loggerName) {\n    return (loggerName === undefined) ? result : options.eval(options.parse(result, name), loggerName)\n  },\n\n  /**\n   * `parse(value: string, name?: string) => Array<Object>`\n   *\n   * Parses the setting value string, returning an AST\n   *\n   * e.g `parse('warn; test=debug')` would yield:\n   *\n   * [{\n   * \tincl: [test],\n   * \texcl: [],\n   * \tvalue: 'debug'\n   * },{\n   *   incl: [*],\n   *   excl: [],\n   *   value: 'warn'\n   * }]`\n   *\n   * if `debugStyle` is truthy, the setting value string is parsed debug-style\n   * and `impliedValue` is used as the value of the setting\n   *\n   * @param {String} value The setting value string.\n   * @param {String} name The name of the setting. Optional.\n   *\n   * @returns {Array} The parsed setting value objects\n   */\n  parse: function(value, name) {\n    var d = (name == 'debug') && name\n    var settings = []\n    var items = (value||'').trim().split(';').map(function(x){return x.replace('\\\\;', ';')})\n    // parse `ulog` style settings, include support for `debug` style\n    var implied = []\n    for (var i=0,item,idx; item=items[i]; i++) {\n      var x = ((idx = item.indexOf('=')) == -1)\n          ? [item.trim()]\n          : [item.substring(0,idx).trim(), item.substring(idx + 1).trim()]\n      // ulog: expressions is first param or none if only a setting value is present (implied)\n      // debug: expressions is always first and only param\n      var expressions = x[1] || d ? x[0].split(/[\\s,]+/) : []\n      // ulog: setting value is second param, or first if only a value is present (implied)\n      // debug: setting value is always implied\n      var setting = { value: x[1] || (!d && x[0]) || d, incl: [], excl: [] }\n      if (expressions.length) {\n        settings.push(setting)\n      }\n      else {\n        expressions.push('*')\n        implied.push(setting)\n      }\n      // add the expressions to the incl/excl lists on the setting\n      for (var j=0,s; s=expressions[j]; j++) {\n        s = s.replace(/\\*/g, '.*?')\n        setting[s[0]=='-'?'excl':'incl'].push(new RegExp('^' + s.substr(s[0]=='-'?1:0) + '$'))\n      }\n    }\n    // add implied settings last so they act as defaults\n    settings.push.apply(settings, implied)\n    return settings\n  },\n\n  /**\n   * Evaluates the given AST for the given logger name.\n   *\n   * @param {Array} ast AST\n   * @param {String} name Logger name\n   *\n   * @returns {String} The effective option value for the given logger name\n   */\n  eval: function(ast, name){\n    for (var i=0,s,r; s=ast[i]; i++) {              // for all parts ('info; test=debug' has 2 parts)\n      if (excl(s, name)) continue\n      if (r = incl(s, name)) return r\n    }\n\n    function excl(s, name) {\n      for (var j=0,br,excl; excl=s.excl[j]; j++)    // for all exclusion tests\n        if (br = excl.test(name)) return br   // if logger matches exclude, return true\n    }\n\n    function incl(s, name) {\n      for (var j=0,incl; incl=s.incl[j]; j++)  \t    // for all inclusion tests\n        if (incl.test(name)) return s.value   // if logger matches include, return result\n    }\n  },\n}\n\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/options/index.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/outputs/index.js":
/*!*************************************************!*\
  !*** ./node_modules/ulog/mods/outputs/index.js ***!
  \*************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("var parse = __webpack_require__(/*! kurly/parse */ \"./node_modules/kurly/parse.js\")\nvar pipe = __webpack_require__(/*! kurly/pipe */ \"./node_modules/kurly/pipe.js\")\nvar grab = __webpack_require__(/*! ../../core/grab */ \"./node_modules/ulog/core/grab.js\")\nvar console = __webpack_require__(/*! ../channels/console */ \"./node_modules/ulog/mods/channels/console.js\")\nvar method = __webpack_require__(/*! ../channels/method */ \"./node_modules/ulog/mods/channels/method.js\")\nconst noop = __webpack_require__(/*! ../channels/noop */ \"./node_modules/ulog/mods/channels/noop.js\")\n\n/**\n * mod: outputs\n *\n * Makes the outputs of logger channels configurable via props\n */\nmodule.exports = {\n  use: [\n    __webpack_require__(/*! ../props */ \"./node_modules/ulog/mods/props/index.js\"),\n    __webpack_require__(/*! ../channels */ \"./node_modules/ulog/mods/channels/index.js\"),\n  ],\n\n  // adds a collection of outputs\n  // an output is either an object with `log()`, `info()`, `warn()` etc methods,\n  // or a kurly tag\n  outputs: {\n    console: console,\n    noop: noop,\n  },\n\n  // adds 'output' and `drain` props to configure the output of these channels\n  settings: {\n    output: {\n      config: 'log_output',\n      prop: {\n        default: 'console',\n      },\n    },\n    drain: {\n      config: 'log_drain',\n      prop: {\n        default: 'noop',\n      },\n    },\n  },\n\n  // override the channel output constructor to take logger props into account\n  channelOutput: function(logger, ch){\n    if (! (ch.cfg = logger[ch.name])) return\n    ch.outputs = grab(this, 'outputs', {})\n    var ast = parse(ch.cfg, { optional: true })\n        .filter(function(node){return typeof node == 'object'})\n    var outs = pipe(ast, ch.outputs)\n        .map(function(node){return node.tag})\n    return (\n      outs.length === 0 ? 0 :\n      (outs.length === 1) && (typeof outs[0] == 'object') ? outs[0] :\n      function(rec) {\n        for (var i=0,out; out=outs[i]; i++) {\n          if (typeof out == 'function') out(rec)\n          else method(out, rec).apply(out, rec.message)\n        }\n      }\n    )\n  },\n}\n\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/outputs/index.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/props/boolean.js":
/*!*************************************************!*\
  !*** ./node_modules/ulog/mods/props/boolean.js ***!
  \*************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("var merge = __webpack_require__(/*! ../../core/merge */ \"./node_modules/ulog/core/merge.js\")\n\nmodule.exports = function(prop) {\n  var result = {\n    default: 'on',\n    fromStr: function(v){return v=='on' || v=='yes' || v=='true' || v=='enabled'},\n    toStr: function(v){return v ? 'on' : 'off'}\n  }\n  merge(result, prop)\n  return result\n}\n\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/props/boolean.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/props/index.js":
/*!***********************************************!*\
  !*** ./node_modules/ulog/mods/props/index.js ***!
  \***********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("var grab = __webpack_require__(/*! ../../core/grab */ \"./node_modules/ulog/core/grab.js\")\r\n\r\n/**\r\n * Mod: props\r\n *\r\n * Enables properties on loggers that are backed by options on ulog.\r\n *\r\n * This mod allows other mods to declare props that will be added on each created logger\r\n * and that are backed by options on ulog itself. A getter and setter will be created that\r\n * return the value of the option for that logger, or set (override) the value of that\r\n * option for that specific logger.\r\n *\r\n * When a prop is set directly on a logger, the logger will keep an in-memory setting that\r\n * overrides the option set on ulog itself. To clear a prop and have it use the global option\r\n * again, set `undefined` as the value for the prop.\r\n */\r\nvar props = module.exports = {\r\n  use: [\r\n    __webpack_require__(/*! ../options */ \"./node_modules/ulog/mods/options/index.js\"),\r\n  ],\r\n\r\n  // // called when a logger needs to be enhanced\r\n  ext: function(logger) {\r\n    var settings = grab(this, 'settings', {})\r\n    for (var name in settings) {\r\n      if (settings[name].prop) {\r\n        props.new.call(this, logger, name, settings[name].prop)\r\n      }\r\n    }\r\n  },\r\n\r\n\r\n  // contribute props to log records\r\n  record: function(logger, rec) {\r\n    var settings = grab(this, 'settings', {})\r\n    for (var name in settings) {\r\n      if (settings[name].prop) {\r\n        rec['log_' + name] = this.get(name, logger.name)\r\n      }\r\n    }\r\n  },\r\n\r\n  /**\r\n   * `new(logger, name, prop)`\r\n   *\r\n   * Creates an option named `name` on the given `logger`, using\r\n   * the provided `prop` whenever applicable.\r\n   *\r\n   * @param {Function} logger The logger function\r\n   * @param {String} name The name of the property to create\r\n   * @param {Object} prop A prop object\r\n   *\r\n   * The `prop` object can have functions `fromStr` and `toStr` that\r\n   * convert from and to String, and `get` and `set` that are called whenever\r\n   * the property is read or written.\r\n   *\r\n   * @returns The given `logger`\r\n   */\r\n  new: function(logger, name, prop) {\r\n    if (name in logger) return logger // already exist\r\n    var ulog=this\r\n    var value // private field\r\n    return Object.defineProperty(logger, prop.name || name, {\r\n      get: function(){\r\n        var v = value !== undefined ? value : ulog.get(name, logger.name)\r\n        v = v !== undefined ? v : prop.default\r\n        v = prop.fromStr ? prop.fromStr.call(logger, v, ulog) : v\r\n        v = prop.get ? prop.get.call(logger, v, ulog) : v\r\n        return v\r\n      },\r\n      set: function(v){\r\n        v = prop.toStr ? prop.toStr.call(logger, v, ulog) : v\r\n        if (value !== v) {\r\n          value = v\r\n          ulog.ext(logger)\r\n        }\r\n        prop.set && prop.set.call(logger, v, ulog)\r\n      }\r\n    })\r\n  }\r\n}\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/props/index.js?");

/***/ }),

/***/ "./node_modules/ulog/mods/settings/index.js":
/*!**************************************************!*\
  !*** ./node_modules/ulog/mods/settings/index.js ***!
  \**************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("var grab = __webpack_require__(/*! ../../core/grab */ \"./node_modules/ulog/core/grab.js\")\r\n\r\nmodule.exports = {\r\n  extend: {\r\n    settings: {},\r\n\r\n   /**\r\n     * `ulog.get(name?: String, ...args): String|Object`\r\n     *\r\n     * Get hook.\r\n     *\r\n     * This method can be used to read settings. When called, it\r\n     * initializes the return value to the value for the setting with `name`\r\n     * and then calls the `get` method on all registered mods, allowing them\r\n     * to modify the result value. Then it returns that result.\r\n     *\r\n     * The first argument to `get` is expected to be the name of the setting\r\n     * to get. Any other arguments are passed on to the `get` methods on\r\n     * registered mods unchanged.\r\n     */\r\n    get: function() {\r\n      var ulog = this\r\n      var args = [].slice.call(arguments)\r\n      var name = args[0]\r\n      if (! name) return ulog.settings\r\n      args.unshift(ulog.settings[name])\r\n      var getters = grab(ulog, 'get', [])\r\n      getters.map(function(get){\r\n        args[0] = get.apply(ulog, args)\r\n      })\r\n      return args[0]\r\n    },\r\n\r\n    /**\r\n     * `ulog.set(name, value)`\r\n     *\r\n     * Sets the setting named `name` to the given `value`.\r\n     *\r\n     * E.g. to set the log level for all loggers to 'warn':\r\n     *\r\n     * `ulog.set('log', 'warn')`\r\n     *\r\n     * The `value` may contain a literal value for the setting, or\r\n     * it may contain a semicolon separated list of `expression=value` pairs,\r\n     * where `expression` is a debug-style pattern and `value` is a literal value\r\n     * for the setting. The literal value may not contain any semicolons, or must\r\n     * escape them by preceding them with a backslash: `\\;`.\r\n     *\r\n     * E.g. to set the log level for libA to ERROR, for libB to INFO and for\r\n     * all other loggers to WARN:\r\n     *\r\n     * `ulog.set('log', 'libA=error; libB=info; *=warn')`\r\n     *\r\n     * Both forms may be combined:\r\n     *\r\n     * `ulog.set('log', 'warn; libA=error; libB=info')` // same as above\r\n     *\r\n     * The `expression=value` pairs are evaluated in the order they are listed,\r\n     * the first `expression` to match decides which `value` is returned.\r\n     *\r\n     * The `expression` can be a list of patterns and contain wildcards\r\n     * and negations:\r\n     *\r\n     * `ulog.set('log', 'info; lib*,-libC=error; libC=warn')`\r\n     *\r\n     * Because of the expression=value pairs being evaluated in order, the simplest\r\n     * is generally to list specific rules first and general rules later:\r\n     *\r\n     * `ulog.set('log', 'libC=warn; lib*=error; info')` // equivalent to above\r\n     */\r\n    set: function(name, value) {\r\n      var ulog = this\r\n      var changed = ulog.settings[name] !== value\r\n      ulog.settings[name] = value\r\n      grab(ulog, 'set', []).map(function(set){\r\n        set.call(ulog, name, value)\r\n      })\r\n      if (changed) ulog.ext()\r\n    }\r\n  }\r\n}\r\n\n\n//# sourceURL=webpack:///./node_modules/ulog/mods/settings/index.js?");

/***/ }),

/***/ "./node_modules/ulog/ulog.js":
/*!***********************************!*\
  !*** ./node_modules/ulog/ulog.js ***!
  \***********************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("// ulog - the universal logger\n// © 2021 by Stijn de Witt\n// License: MIT\n(module.exports = __webpack_require__(/*! ./base */ \"./node_modules/ulog/base.js\")).use([\n  __webpack_require__(/*! ./mods/debug */ \"./node_modules/ulog/mods/debug/index.js\"),\n  __webpack_require__(/*! ./mods/outputs */ \"./node_modules/ulog/mods/outputs/index.js\"),\n  __webpack_require__(/*! ./mods/formats */ \"./node_modules/ulog/mods/formats/index.js\"),\n  __webpack_require__(/*! ./mods/colors */ \"./node_modules/ulog/mods/colors/index.js\"),\n  __webpack_require__(/*! ./mods/align */ \"./node_modules/ulog/mods/align/index.js\"),\n])\n\n\n//# sourceURL=webpack:///./node_modules/ulog/ulog.js?");

/***/ })

/******/ })});;