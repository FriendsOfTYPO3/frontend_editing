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
