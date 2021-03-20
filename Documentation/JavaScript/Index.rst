.. include:: ../Includes.txt


.. _jsconfiguration:

Javascript
==================

.. _jsconfiguration-language:

Language
--------

Use `TYPO3/CMS/FrontendEditing/Utils/TranslatorLoader` to customize or extend
 language translation on client side. There is an `configure` function which can
 be used to merge customize configuration with the default one.

If the configuration is already present, the merge strategy can be used to force
 an `'merge'` or `'override'`.

.. code-block:: javascript

    var configuration = {
        translationLabels: {},
        namespaceMapping: {},
    }
    var configuration = "none";
    TranslatorLoader.configure(configuration, mergeStrategy);


Following example show how to use on server side loading:

.. code-block:: php

    $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/FrontendEditing/Utils/TranslatorLoader', "function(TranslatorLoader) {
        TranslatorLoader.configure({
            translationLabels: ${translationLabelMap},
            namespaceMapping: ${translationLabelMapping},
        });
    }");

In the frontend editing default bootstrap is no merge strategy used. So if it
 was configured before, no server side `translationLabels` get configured.
 Instead it uses the default fallback implementation.

Be aware that the `namespaceMapping` configuration property will be changed
 without deprecation warning since it is not stable.


.. _jsconfiguration-logger:

Logger
------
The `ulog` logging framework is used to handle logs. It is a highly configurable an extendable logger.

Persist log record on Typo3 server is not implemented yet.

Loggers available:

- FEditing:Component:LoadingScreen
- FEditing:Component:Widget:Modal
- FEditing:Component:Widget:Notification
- FEditing:CRUD
- FEditing:Editor
- FEditing:FrontendEditing
- FEditing:GUI
- FEditing:localStorage
- FEditing:Utils:Scroller
- FEditing:Utils:TranslatorFactory
- FEditing:Utils:TranslatorLoader

.. _jsconfiguration-logger-level:

### Log Levels:
Following log levels exists:
- `error` : Used if an exception occured and the normal handling failed.
- `warn`: Also used if an exception occured, but the normal handling can be done.
- `info`: Used if an state change occured like an event (eg. mouse click)
- `log`: Used if an point of interesse is reached like the an array to loop.
- `debug`: Used if an calculation happend.
- `trace` (Not implemented yet): Used if an function get called and end. Only useful to record the call chains.

There are some special log levels only used to configure ulog:
- `none`: No logs get selected
- `all`: Normaly it is like `trace`. But if `ulog` log levels get extended it is much more save to use `all`.

**Hint:** The log level during log selection in configuration are chained. Use `warn` log level does indirectly also select `error` logs.

.. _jsconfiguration-logger-config:

### Configuration

Configuration can be done as URL parameter or/and as localStorage item.
There are follwing configuration options:
- `log`: The main setting to control logger’s levels with
- `log_output`: To configure the output of the `output` channel, where logging should go
- `log_drain`: To configure the output of the `drain` channel

More details can be found at: https://ulog.js.org/#configure

.. _jsconfiguration-logger-config-syntax:

#### Syntax:

`<config_option> = <config> [; <config>] ...`
Configuration options can have more than one configuration entry:

`<config> := [ <filter> = ] <value>`
Configuration entry have a value and a filter to select the loggers to apply the config:
**Hint:** If no filter or a wildcard is set, the config is used as default config.

`<filter> := <RegEx> [ , [ - ] <RegEx> ] ...`
The filter is a list of RegEx with option to add or substract loggers from the selection set:

**Attention:**
- in `log` the `<value>` is a log level
- in `log_<channel_name>` the `<value>` is one or more `output`

.. _jsconfiguration-logger-config-example:

#### Examples
With URL parameters:
- to log in general info level:
`?log=info`
**Hint:** Technical, this is equals to: `?log=*=info`
- to log widgets in debug and the rest in info mode:
`?log=info;FEditing:Component:Widget:*=debug`
- to print and persist log in output channel: `?log_ouput=console persist`
- to print logs in general and persist logs from widgets in output channel:
`?log_ouput=console;FEditing:Component:Widget:*=console persist`
- to print logs in general and only persist logs from widgets in output channel:
`?log_ouput=console;FEditing:Component:Widget:*=persist`

.. _jsconfiguration-logger-output:

### Output:
By default there are two outputs:
- `console`: default browser `console`
- `noop`: No operation function, used to prevent log in `drain` channel

.. _jsconfiguration-logger-channel:

### Channel:
By default there are two channels:
- `output`: Used if `log` configuration is matching. eg. log=info and logRecord.level is smaller than info
- `drain`: Opposite to `output` channel. Used to have the opportunity to handle this log records to like persist in indexedDB.

.. _jsconfiguration-logger-extension:

### Extensions
There are two extensions as AMD available to use:

- `TYPO3/CMS/FrontendEditing/Utils/LoggerPersist`
Add two output `Persist`, `Server` and a new channel `highorder`
- `TYPO3/CMS/FrontendEditing/Utils/LoggerWindowError`
Register window error handlers to log them with a logger.

.. _jsconfiguration-logger-extension-highorder:

#### `highorder` channel
There are two new configuration options:
- `highorder_log`: The main setting to control `highorder` channel selection.
*Hint:* Analog to the `log` option and the `output` channel
- `log_highorder`: To configure the output of the `highorder` channel, where logging should go
