.. include:: ../Includes.txt



.. _installation:

Installation
------------

The following steps are required to active the frontend editing for a TYPO3 installation.

- Install and active the extension called *frontend_editing*

- Add the TypoScript called *TYPO3 frontend editing* to the site roots where the features should be activated

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
