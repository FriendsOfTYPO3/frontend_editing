.. include:: /Includes.rst.txt



.. _installation:

============
Installation
============

Required steps
==============

The following steps are required to use frontend editing in a TYPO3 installation.

* Install the extension

   #. **Using Composer** (recommended): `composer req friendsoftypo3/frontend-editing` or by downloading the extension
   #. **Using the Extension Manager** in *Admin Tools > Extensions* as explained in the :ref:`Extension Installation <t3start:extensions_legacy_management>` chapter of the official TYPO3 documentation.

* Add the static TypoScript template *Frontend Editing* to the site roots where the features should be activated

  .. figure:: ../Images/AddTypoScript.png
     :alt: TYPO3 frontend editing TyposScript

.. _optional-settings:

Optional steps
==============

* Set the baseUrl for frontend editing if server path is not a top directory. This is done by adding the following part to setup typoscript:

  .. code-block:: typoscript

     plugin.tx_frontendediting.baseUrl = /

* Include the static template *Editable Fluid Styled Content v9*, to include basic editable templates for Fluid Styled Content in TYPO3 v9.

* Disable the Placeholder feature in the extension configuration. By disabling this feature, empty editable content areas will no longer with placeholders. When creating a new content element by dragging and dropping onto the page, a modal pop-up with a backend form will appear so that you can fill in the initial content. :ref:`extension-manager-settings`
