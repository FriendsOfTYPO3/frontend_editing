.. include:: ../Includes.txt



.. _extension-manager-settings:

==================================
Available extension configurations
==================================

These configurations can be found in the Extension Manager in TYPO3 v7 and v8 and in the Settings (Admin Tools) in TYPO3 v9 and up.

.. _extension-manager-settings-providers:

Providers
=========

SEO provider
------------

Experimental connection to SEO provides.

.. _extension-manager-settings-basic:

Basic
=====

The tag name that will be used for the Content editable wrapper
---------------------------------------------------------------

The default wrapper tag name used by the frontend editing functionality is `div`, but you can change it to something else here.

.. _extension-manager-settings-features:

Features
========

Enable placeholders and direct drop-to-edit without modal
---------------------------------------------------------

By enabling this feature, empty editable content areas will appear with placeholders. Content elements will also appear directly after you have dropped them on the page, skipping the modal pop-up with a backend form that would otherwise appear.

When using this feature, you should adapt your templates to show empty fields using the :ref:`viewhelpers-isplaceholderenabled` view helper.
