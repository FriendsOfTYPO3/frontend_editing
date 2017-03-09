.. include:: ../Includes.txt

.. highlight:: rst

==============
Inline editing
==============

After activating the extension from the TYPO3-backend you need to check what templating engine is used in the TYPO3-installation.

CSS Styled Content (css_styled_content)
---------------------------------------

If the installation is using the well known (and old) css_styled_content-extension the functionality comes straight out of the box. The editing can start directly.

Fluid Styled Content (fluid_styled_content)
-------------------------------------------

When using fluid_styled_content there are some things that needs to be adjusted to your template to get the editing to work. First of all there is a view helper that needs to be included and configured.

First import the namespace:

.. code-block:: php

   {namespace fe=TYPO3\CMS\FrontendEditing\ViewHelpers}

The next step is to find the content that you want editable and wrap it with the view helper:

.. code-block:: html

   <fe:editable table="tt_content" field="bodytext" uid="{item.uid}">
       {item.bodytext}
   </fe:editable>

The output will look like this in frontend edit mode:

.. code-block:: html

   <div contenteditable="true" data-table="tt_content" data-field="bodytext" data-uid="1">
       This is the content text to edit
   </div>

While not in frontend edit mode the output is:

.. code-block:: html

   This is the content text to edit

TypoScript
----------

If you are listing elements with TypoScript only, you can still include the editing icons using the included hook into TYPO3 rendering process. This example lists the editable frontend user names and emails:

.. code-block:: typoscript

   page.20 = CONTENT
   page.20 {
     table = fe_users
     select.pidInList = 38
     renderObj = COA
     renderObj.10 = TEXT
     renderObj.10 {
       field = username
       wrap = Username:|<br/>
       stdWrap.editIcons = fe_users: username
       stdWrap.editIcons.beforeLastTag = 1
     }
     renderObj.20 = TEXT
     renderObj.20 {
       field = email
       wrap = Email:|<br/><br/>
       stdWrap.editIcons = fe_users: email
       stdWrap.editIcons.beforeLastTag = 1
     }
     stdWrap.editIcons = pages: users
     stdWrap.editIcons.hasEditableFields = 1
   }
