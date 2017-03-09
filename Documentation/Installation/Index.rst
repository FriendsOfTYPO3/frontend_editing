.. include:: ../Includes.txt

.. highlight:: rst

===================
TYPO3 Installation
===================

The following steps are required to activate the frontend editing for a TYPO3 installation.

#. Install and activate the extension called **frontend_editing**.

#. Add the typoscript-static-template called **TYPO3 frontend editing** to the site root template.

   |img-add-typoscript|

#. After the inclusion of the typoscript-static-template you need to add the following setting to the site root.

   Add this to the setup part, where the 1 or 0 indicates if it is active or not

   .. code-block:: typoscript

      config.tx_frontend_editing = 1

#. The last thing to do is for the individual users to activate the frontend editing for themselves. This is done in the "User settings" in TYPO3's backend.

   |img-user-activation-of-feedit|

You are now ready for some easy editing! :)
