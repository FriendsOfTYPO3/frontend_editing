.. include:: /Includes.rst.txt


.. _jsconfiguration:

==========
JavaScript
==========

.. _jsconfiguration-language:

Language
========

Use `TYPO3/CMS/FrontendEditing/Utils/TranslatorLoader` to customize or extend
 language translation on client side. There is a `configure` function that can
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


The following example shows how to use it on the server side:

.. code-block:: php

    $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/FrontendEditing/Utils/TranslatorLoader', "function(TranslatorLoader) {
        TranslatorLoader.configure({
            translationLabels: ${translationLabelMap},
            namespaceMapping: ${translationLabelMapping},
        });
    }");

No merge strategy is used in the frontend editing default bootstrap. So if it
 was configured before, no server side `translationLabels` get configured.
 Instead it uses the default fallback implementation.

Be aware that the `namespaceMapping` configuration property may change without
deprecation warning since it is not stable.
