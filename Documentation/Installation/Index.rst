.. include:: ../Includes.txt



.. _installation:

============
Installation
============

Required steps
==============

The following steps are required to active the frontend editing for a TYPO3 installation.

- Install and active the extension called *frontend_editing*

- Add the static TypoScript template *Frontend Editing* to the site roots where the features should be activated

  .. figure:: ../Images/AddTypoScript.png
     :alt: TYPO3 frontend editing TyposScript

- After the inclusion of the typoscript settings there is a need to add the following setting to the site root(s)
  Add this to the setup part, where the **1** or **0** indicates if it is active or not

  .. code-block:: typoscript

     config.frontend_editing = 1

- The last thing to do is for the individual users to activate the frontend editing for themselves.
  This is done in the "User settings" in TYPO3:s backend.

  .. figure:: ../Images/UserActivationOfFeedit.png
     :alt: User activation of frontend editing

.. _optional-settings:

Optional steps
==============

- Set the baseUrl for frontend editing if server path is not a top directory. This is done by adding the following part to setup typoscript:

  .. code-block:: typoscript

     plugin.tx_frontendediting.baseUrl = /

- Include the static template *Editable Fluid Styled Content v9*, to include basic editable templates for Fluid Styled Content in TYPO3 v9.

- Enable the Placeholder feature in the extension configuration. By enabling this feature, empty editable content areas will appear with placeholders. Content elements will also appear directly after you have dropped them on the page, skipping the modal pop-up with a backend form that would otherwise appear. :ref:`extension-manager-settings`
