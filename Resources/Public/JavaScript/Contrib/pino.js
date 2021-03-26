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
/******/ 	return __webpack_require__(__webpack_require__.s = "./node_modules/pino/browser.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./node_modules/pino/browser.js":
/*!**************************************!*\
  !*** ./node_modules/pino/browser.js ***!
  \**************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
eval("\n\nconst format = __webpack_require__(/*! quick-format-unescaped */ \"./node_modules/quick-format-unescaped/index.js\")\n\nmodule.exports = pino\n\nconst _console = pfGlobalThisOrFallback().console || {}\nconst stdSerializers = {\n  mapHttpRequest: mock,\n  mapHttpResponse: mock,\n  wrapRequestSerializer: passthrough,\n  wrapResponseSerializer: passthrough,\n  wrapErrorSerializer: passthrough,\n  req: mock,\n  res: mock,\n  err: asErrValue\n}\n\nfunction shouldSerialize (serialize, serializers) {\n  if (Array.isArray(serialize)) {\n    const hasToFilter = serialize.filter(function (k) {\n      return k !== '!stdSerializers.err'\n    })\n    return hasToFilter\n  } else if (serialize === true) {\n    return Object.keys(serializers)\n  }\n\n  return false\n}\n\nfunction pino (opts) {\n  opts = opts || {}\n  opts.browser = opts.browser || {}\n\n  const transmit = opts.browser.transmit\n  if (transmit && typeof transmit.send !== 'function') { throw Error('pino: transmit option must have a send function') }\n\n  const proto = opts.browser.write || _console\n  if (opts.browser.write) opts.browser.asObject = true\n  const serializers = opts.serializers || {}\n  const serialize = shouldSerialize(opts.browser.serialize, serializers)\n  let stdErrSerialize = opts.browser.serialize\n\n  if (\n    Array.isArray(opts.browser.serialize) &&\n    opts.browser.serialize.indexOf('!stdSerializers.err') > -1\n  ) stdErrSerialize = false\n\n  const levels = ['error', 'fatal', 'warn', 'info', 'debug', 'trace']\n\n  if (typeof proto === 'function') {\n    proto.error = proto.fatal = proto.warn =\n    proto.info = proto.debug = proto.trace = proto\n  }\n  if (opts.enabled === false) opts.level = 'silent'\n  const level = opts.level || 'info'\n  const logger = Object.create(proto)\n  if (!logger.log) logger.log = noop\n\n  Object.defineProperty(logger, 'levelVal', {\n    get: getLevelVal\n  })\n  Object.defineProperty(logger, 'level', {\n    get: getLevel,\n    set: setLevel\n  })\n\n  const setOpts = {\n    transmit,\n    serialize,\n    asObject: opts.browser.asObject,\n    levels,\n    timestamp: getTimeFunction(opts)\n  }\n  logger.levels = pino.levels\n  logger.level = level\n\n  logger.setMaxListeners = logger.getMaxListeners =\n  logger.emit = logger.addListener = logger.on =\n  logger.prependListener = logger.once =\n  logger.prependOnceListener = logger.removeListener =\n  logger.removeAllListeners = logger.listeners =\n  logger.listenerCount = logger.eventNames =\n  logger.write = logger.flush = noop\n  logger.serializers = serializers\n  logger._serialize = serialize\n  logger._stdErrSerialize = stdErrSerialize\n  logger.child = child\n\n  if (transmit) logger._logEvent = createLogEventShape()\n\n  function getLevelVal () {\n    return this.level === 'silent'\n      ? Infinity\n      : this.levels.values[this.level]\n  }\n\n  function getLevel () {\n    return this._level\n  }\n  function setLevel (level) {\n    if (level !== 'silent' && !this.levels.values[level]) {\n      throw Error('unknown level ' + level)\n    }\n    this._level = level\n\n    set(setOpts, logger, 'error', 'log') // <-- must stay first\n    set(setOpts, logger, 'fatal', 'error')\n    set(setOpts, logger, 'warn', 'error')\n    set(setOpts, logger, 'info', 'log')\n    set(setOpts, logger, 'debug', 'log')\n    set(setOpts, logger, 'trace', 'log')\n  }\n\n  function child (bindings) {\n    if (!bindings) {\n      throw new Error('missing bindings for child Pino')\n    }\n    const bindingsSerializers = bindings.serializers\n    if (serialize && bindingsSerializers) {\n      var childSerializers = Object.assign({}, serializers, bindingsSerializers)\n      var childSerialize = opts.browser.serialize === true\n        ? Object.keys(childSerializers)\n        : serialize\n      delete bindings.serializers\n      applySerializers([bindings], childSerialize, childSerializers, this._stdErrSerialize)\n    }\n    function Child (parent) {\n      this._childLevel = (parent._childLevel | 0) + 1\n      this.error = bind(parent, bindings, 'error')\n      this.fatal = bind(parent, bindings, 'fatal')\n      this.warn = bind(parent, bindings, 'warn')\n      this.info = bind(parent, bindings, 'info')\n      this.debug = bind(parent, bindings, 'debug')\n      this.trace = bind(parent, bindings, 'trace')\n      if (childSerializers) {\n        this.serializers = childSerializers\n        this._serialize = childSerialize\n      }\n      if (transmit) {\n        this._logEvent = createLogEventShape(\n          [].concat(parent._logEvent.bindings, bindings)\n        )\n      }\n    }\n    Child.prototype = this\n    return new Child(this)\n  }\n  return logger\n}\n\npino.levels = {\n  values: {\n    fatal: 60,\n    error: 50,\n    warn: 40,\n    info: 30,\n    debug: 20,\n    trace: 10\n  },\n  labels: {\n    10: 'trace',\n    20: 'debug',\n    30: 'info',\n    40: 'warn',\n    50: 'error',\n    60: 'fatal'\n  }\n}\n\npino.stdSerializers = stdSerializers\npino.stdTimeFunctions = Object.assign({}, { nullTime, epochTime, unixTime, isoTime })\n\nfunction set (opts, logger, level, fallback) {\n  const proto = Object.getPrototypeOf(logger)\n  logger[level] = logger.levelVal > logger.levels.values[level]\n    ? noop\n    : (proto[level] ? proto[level] : (_console[level] || _console[fallback] || noop))\n\n  wrap(opts, logger, level)\n}\n\nfunction wrap (opts, logger, level) {\n  if (!opts.transmit && logger[level] === noop) return\n\n  logger[level] = (function (write) {\n    return function LOG () {\n      const ts = opts.timestamp()\n      const args = new Array(arguments.length)\n      const proto = (Object.getPrototypeOf && Object.getPrototypeOf(this) === _console) ? _console : this\n      for (var i = 0; i < args.length; i++) args[i] = arguments[i]\n\n      if (opts.serialize && !opts.asObject) {\n        applySerializers(args, this._serialize, this.serializers, this._stdErrSerialize)\n      }\n      if (opts.asObject) write.call(proto, asObject(this, level, args, ts))\n      else write.apply(proto, args)\n\n      if (opts.transmit) {\n        const transmitLevel = opts.transmit.level || logger.level\n        const transmitValue = pino.levels.values[transmitLevel]\n        const methodValue = pino.levels.values[level]\n        if (methodValue < transmitValue) return\n        transmit(this, {\n          ts,\n          methodLevel: level,\n          methodValue,\n          transmitLevel,\n          transmitValue: pino.levels.values[opts.transmit.level || logger.level],\n          send: opts.transmit.send,\n          val: logger.levelVal\n        }, args)\n      }\n    }\n  })(logger[level])\n}\n\nfunction asObject (logger, level, args, ts) {\n  if (logger._serialize) applySerializers(args, logger._serialize, logger.serializers, logger._stdErrSerialize)\n  const argsCloned = args.slice()\n  let msg = argsCloned[0]\n  const o = {}\n  if (ts) {\n    o.time = ts\n  }\n  o.level = pino.levels.values[level]\n  let lvl = (logger._childLevel | 0) + 1\n  if (lvl < 1) lvl = 1\n  // deliberate, catching objects, arrays\n  if (msg !== null && typeof msg === 'object') {\n    while (lvl-- && typeof argsCloned[0] === 'object') {\n      Object.assign(o, argsCloned.shift())\n    }\n    msg = argsCloned.length ? format(argsCloned.shift(), argsCloned) : undefined\n  } else if (typeof msg === 'string') msg = format(argsCloned.shift(), argsCloned)\n  if (msg !== undefined) o.msg = msg\n  return o\n}\n\nfunction applySerializers (args, serialize, serializers, stdErrSerialize) {\n  for (const i in args) {\n    if (stdErrSerialize && args[i] instanceof Error) {\n      args[i] = pino.stdSerializers.err(args[i])\n    } else if (typeof args[i] === 'object' && !Array.isArray(args[i])) {\n      for (const k in args[i]) {\n        if (serialize && serialize.indexOf(k) > -1 && k in serializers) {\n          args[i][k] = serializers[k](args[i][k])\n        }\n      }\n    }\n  }\n}\n\nfunction bind (parent, bindings, level) {\n  return function () {\n    const args = new Array(1 + arguments.length)\n    args[0] = bindings\n    for (var i = 1; i < args.length; i++) {\n      args[i] = arguments[i - 1]\n    }\n    return parent[level].apply(this, args)\n  }\n}\n\nfunction transmit (logger, opts, args) {\n  const send = opts.send\n  const ts = opts.ts\n  const methodLevel = opts.methodLevel\n  const methodValue = opts.methodValue\n  const val = opts.val\n  const bindings = logger._logEvent.bindings\n\n  applySerializers(\n    args,\n    logger._serialize || Object.keys(logger.serializers),\n    logger.serializers,\n    logger._stdErrSerialize === undefined ? true : logger._stdErrSerialize\n  )\n  logger._logEvent.ts = ts\n  logger._logEvent.messages = args.filter(function (arg) {\n    // bindings can only be objects, so reference equality check via indexOf is fine\n    return bindings.indexOf(arg) === -1\n  })\n\n  logger._logEvent.level.label = methodLevel\n  logger._logEvent.level.value = methodValue\n\n  send(methodLevel, logger._logEvent, val)\n\n  logger._logEvent = createLogEventShape(bindings)\n}\n\nfunction createLogEventShape (bindings) {\n  return {\n    ts: 0,\n    messages: [],\n    bindings: bindings || [],\n    level: { label: '', value: 0 }\n  }\n}\n\nfunction asErrValue (err) {\n  const obj = {\n    type: err.constructor.name,\n    msg: err.message,\n    stack: err.stack\n  }\n  for (const key in err) {\n    if (obj[key] === undefined) {\n      obj[key] = err[key]\n    }\n  }\n  return obj\n}\n\nfunction getTimeFunction (opts) {\n  if (typeof opts.timestamp === 'function') {\n    return opts.timestamp\n  }\n  if (opts.timestamp === false) {\n    return nullTime\n  }\n  return epochTime\n}\n\nfunction mock () { return {} }\nfunction passthrough (a) { return a }\nfunction noop () {}\n\nfunction nullTime () { return false }\nfunction epochTime () { return Date.now() }\nfunction unixTime () { return Math.round(Date.now() / 1000.0) }\nfunction isoTime () { return new Date(Date.now()).toISOString() } // using Date.now() for testability\n\n/* eslint-disable */\n/* istanbul ignore next */\nfunction pfGlobalThisOrFallback () {\n  function defd (o) { return typeof o !== 'undefined' && o }\n  try {\n    if (typeof globalThis !== 'undefined') return globalThis\n    Object.defineProperty(Object.prototype, 'globalThis', {\n      get: function () {\n        delete Object.prototype.globalThis\n        return (this.globalThis = this)\n      },\n      configurable: true\n    })\n    return globalThis\n  } catch (e) {\n    return defd(self) || defd(window) || defd(this) || {}\n  }\n}\n/* eslint-enable */\n\n\n//# sourceURL=webpack:///./node_modules/pino/browser.js?");

/***/ }),

/***/ "./node_modules/quick-format-unescaped/index.js":
/*!******************************************************!*\
  !*** ./node_modules/quick-format-unescaped/index.js ***!
  \******************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
eval("\nfunction tryStringify (o) {\n  try { return JSON.stringify(o) } catch(e) { return '\"[Circular]\"' }\n}\n\nmodule.exports = format\n\nfunction format(f, args, opts) {\n  var ss = (opts && opts.stringify) || tryStringify\n  var offset = 1\n  if (typeof f === 'object' && f !== null) {\n    var len = args.length + offset\n    if (len === 1) return f\n    var objects = new Array(len)\n    objects[0] = ss(f)\n    for (var index = 1; index < len; index++) {\n      objects[index] = ss(args[index])\n    }\n    return objects.join(' ')\n  }\n  if (typeof f !== 'string') {\n    return f\n  }\n  var argLen = args.length\n  if (argLen === 0) return f\n  var x = ''\n  var str = ''\n  var a = 1 - offset\n  var lastPos = -1\n  var flen = (f && f.length) || 0\n  for (var i = 0; i < flen;) {\n    if (f.charCodeAt(i) === 37 && i + 1 < flen) {\n      lastPos = lastPos > -1 ? lastPos : 0\n      switch (f.charCodeAt(i + 1)) {\n        case 100: // 'd'\n          if (a >= argLen)\n            break\n          if (lastPos < i)\n            str += f.slice(lastPos, i)\n          if (args[a] == null)  break\n          str += Number(args[a])\n          lastPos = i = i + 2\n          break\n        case 79: // 'O'\n        case 111: // 'o'\n        case 106: // 'j'\n          if (a >= argLen)\n            break\n          if (lastPos < i)\n            str += f.slice(lastPos, i)\n          if (args[a] === undefined) break\n          var type = typeof args[a]\n          if (type === 'string') {\n            str += '\\'' + args[a] + '\\''\n            lastPos = i + 2\n            i++\n            break\n          }\n          if (type === 'function') {\n            str += args[a].name || '<anonymous>'\n            lastPos = i + 2\n            i++\n            break\n          }\n          str += ss(args[a])\n          lastPos = i + 2\n          i++\n          break\n        case 115: // 's'\n          if (a >= argLen)\n            break\n          if (lastPos < i)\n            str += f.slice(lastPos, i)\n          str += String(args[a])\n          lastPos = i + 2\n          i++\n          break\n        case 37: // '%'\n          if (lastPos < i)\n            str += f.slice(lastPos, i)\n          str += '%'\n          lastPos = i + 2\n          i++\n          break\n      }\n      ++a\n    }\n    ++i\n  }\n  if (lastPos === -1)\n    return f\n  else if (lastPos < flen) {\n    str += f.slice(lastPos)\n  }\n\n  return str\n}\n\n\n//# sourceURL=webpack:///./node_modules/quick-format-unescaped/index.js?");

/***/ })

/******/ })});;