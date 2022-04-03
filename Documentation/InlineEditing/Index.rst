.. include:: /Includes.rst.txt



.. _inline-editing:

===================================
Editable regions and record editing
===================================

This is an introduction to configuring and customizing Frontend Editing. You can
create frontend-editable regions using Fluid ViewHelpers or TypoScipt.

For in-depth configuration options, see :ref:`configureandextend`.

.. _fluid-styled-content:

Fluid Styled Content
====================

Basic Fluid Styled Content override templates with frontend editing enabled are
included in the extension. To use them, include the static TypoScript template
"Editable Fluid Styled Content v9". The templates are based on Fluid Styled
Content for TYPO3 v9. Templates for other versions may be included in the
future.

.. _custom-fluid-templates:

Custom Fluid templates
======================

Custom fluid templates use :ref:`viewhelpers`, especially the
:ref:`contentEditable ViewHelper <viewhelpers-contenteditable>`.

.. note::

   If you are using Fluid templates, but not fluid_styled_content on the
   website, editIcons must be set manually.

   .. code-block:: typoscript

      lib.fluidContent {
         stdWrap {
            editIcons = tt_content:header
         }
      }

.. _css-styled-content:

CSS Styled Content
==================

If the installation is using the old css_styled_content extension, the
functionality works out of the box and you can start editing at once.

.. _typoscript:

TypoScript
==========

If you are rendering elements with TypoScript only, you can define editable
regions using the `editIcons` property. This example makes frontend usernames
and email addresses editable.

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
			stdWrap.editIcons = fe_users:email
			stdWrap.editIcons.beforeLastTag = 1
		}
		stdWrap.editIcons = pages:users
		stdWrap.editIcons.hasEditableFields = 1
	}

.. tip::

   Are you using :typoscript:`editIcons.beforeLastTag = 1` or
   :typoscript:`editIcons.beforeLastTag = 0`? Used wrong, you may set the entire
   content element as editable, resulting in problems correctly saving the data
   and content from other fields appearing elsewhere after saving.


.. _typoscript-custom-record-editing:

Custom record editing
=====================

There is a possibility to use the edit button when a single extension record is
present. What then will happen is that an editor clicks the edit button and a
modal with the form engine for the records is displayed instead of the plugin
setup.

For example in this case a configuration is added so that it is possible to edit
a single news record. As long as an extension have records it is possible to add
any kind of records.

.. tip::

   Frontend Editing ships with the configuration to make
   `News extension <https://extensions.typo3.org/extension/news>`__ records
   editable out of the box!

   .. code-block:: typoscript

       config {
           tx_frontendediting {
               customRecordEditing {
                   tx_news_pi1 {
                       actionName = detail
                       recordName = news
                       tableName = tx_news_domain_model_news
                       listTypeName = news_pi1
                   }
               }
           }
       }

   An example
   `News template with support for Frontend Editing <https://github.com/FriendsOfTYPO3/frontend_editing/blob/master/Documentation/InlineEditing/NewsCustomRecordExample.html>`__.
   Note that the headline, teaser and description are inline editable.


.. _inline-editing-nonstandard:

Handling fields with non-standard rendering
===========================================

With non-standard rendering, we mean the fields that are no rendered the same
way in the TYPO3 Backend and Frontend.


Problem
-------

A good example is the standard "List" content element. In the Backend form, the
bullet points are entered into a plain-text field, with each bullet point
separated by a new line. In the frontend, these bullets are rendered as proper
HTML lists, using the `<ul>`, `<ol>`, and `<li>` tags. When the data is saved in
Frontend Editing, the frontend version (with HTML tags) is persisted to the
database.

This is an example list as saved in the database:

.. code-block:: html

   Item 1
   Item 2
   Item 3

In the frontend, this content is rendered like below. This is also how
Frontend Editing "sees" the data and how it tries to persist it to the database.

.. code-block:: html

   <ul>
     <li>Item 1</li>
     <li>Item 2</li>
     <li>Item 3</li>
   </ul>

Solution
--------

To ensure the data is persisted to the database in the expected format, we must
transform it. This can be done using one of two TypoScript properties:

*  :ref:`typoscript-contentpersistpreprocessing`, which allows you to
   configure a specific transformation for a specific field.
*  :ref:`typoscript-contentpersistpreprocessingpatterns`, which allows you to
   configure a transformation for any field using a specific RTE preset.

:ref:`typoscript-contentpersistpreprocessingpatterns` is especially handy if the
field doesn't have any rich-rext editing in the backend. Frontend Editing comes
with two presets for this particular use case:

*  :ref:`tca-enablefrontendrichtext` for RTE only in Frontend Editing.
*  :ref:`tca-frontendrichtextconfiguration` for specifying an RTE preset that
   will only be used in Frontend Editing.

Here's an example of how we use a Frontend Editing-only RTE preset:

.. code-block:: php

   $GLOBALS['TCA']['tt_content']['types']['bullets']['columnsOverrides']['bodytext'] = [
       'config' => [
           'enableFrontendRichtext' => true,
           'frontendRichtextConfiguration' => 'listonly',
       ],
   ];

Frontend Editing ships with two RTE presets and
:ref:`typoscript-contentpersistpreprocessingpatterns` configurations for solving
the two most common use cases.

*  **bronly** for handling text that should only accept line breaks, but no
   other formatting.
*  **listonly** for handling lists that are stored as plain text in the
   database.

Here's an example of a Fluid template rendering a list:

.. code-block:: html

   <t3kit:contentEditable tag="ul" table="tt_content" field="bodytext" uid="{data.uid}">
     <f:for each="{bullets}" as="bullet">
       <li>{bullet}</li>
     </f:for>
   </t3kit:contentEditable>
