.. include:: ../Includes.txt



.. _inline-editing:

Inline Editing
==============

After the frontend editing have been activated from within the TYPO3 backend
there are one scenario that needs to been taking into account. It is what
kind of templating engine are used for the frontend template for the websites
that you are using.

.. _css-styled-content:

CSS Styled Content
------------------

If the installation are using the well known (and old) extension which is called
css_styled_content are being used. The functionality comes straight out of the
box and the editing can start directly.

.. _fluid-styled-content:

Fluid Styled Content
--------------------

Basic Fluid Styled Content override templates with frontend editing enabled are included. To use them, include the static TypoScript template "Editable Fluid Styled Content v9". The templates are based on Fluid Styled Content for TYPO3 v9. Templates for other versions may be included in the future.

Note:

If fluid_styled_content is not included on the website or is disabled,
the Typoscript of editIcons must be set manually.

.. code-block:: typoscript

	lib.fluidContent {
		stdWrap {
			editIcons = tt_content:header
		}
	}

When it comes to fluid_styled_content there are some things that needs to be
adjusted to your template to get the editing to work. First of all there is
a view helper that needs to be included and configured.

Namespace:

.. code-block:: html

	<html xmlns:core="http://typo3.org/ns/TYPO3/CMS/FrontendEditing/ViewHelpers"
	      data-namespace-typo3-fluid="true">

	</html>

First, find the content that you want editable and wrap it with the view
helper:

.. code-block:: html
	<core:contentEditable table="tt_content" field="bodytext" uid="{item.uid}">
		{item.bodytext}
	</core:contentEditable>

The available options are:

- *table*: The database table name to where the data should be saved
- *field*: The database field to where the data should be saved (optional)
- *uid*: The database field to where the data should be saved

A full example for inline editing of a certain field looks like this:

.. code-block:: html

	<core:contentEditable table="{item.table}" field="{item.field}" uid="{item.uid}">
		{item.bodytext}
	</core:contentEditable>

The output would then look like the following in frontend edit mode:

.. code-block:: html

	<div contenteditable="true" data-table="tt_content" data-field="bodytext" data-uid="1">
		This is the content text to edit
	</div>

While not in frontend edit mode the output are the following:

.. code-block:: html

	This is the content text to edit

There is also a possibility to make the content element editable through the popup backend editor.
It is done by skipping the *field* option in the view helper:

.. code-block:: html

	<core:contentEditable table="tt_content" uid="{data.uid}">
		{item.bodytext}
	</core:contentEditable>

.. _typoscript:

TypoScript
~~~~~~~~~~

If you are listing elements with TypoScript only, you can still include the
editing icons using the included hook into TYPO3 rendering process.
This example lists editable the frontend user names and emails:

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

.. _tip::

	Are you using :typoscript:`editIcons.beforeLastTag = 1` or :typoscript:`editIcons.beforeLastTag = 0`? Used wrong, you may set the entire content element as editable, resulting in problems correctly saving the data and content from other fields appearing elsewhere after saving.


.. _typoscript-custom-record-editing:

TypoScript custom record editing
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

There is a possibility to use the edit button when a single extension record is present. What then will happen is that an editor
clicks the edit button and a modal with the form engine for the records is displayed instead of the plugin setup.

For example in this case a configuration is added so that it is possible to edit a single news record. As long as an extension
have records it is possible to add any kind of records.

Frontend editing is shipped so that the news extension is editable out of the box!

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


.. _viewhelpers:

View Helpers
------------


.. _viewhelpers-contenteditable:

contentEditable
~~~~~~~~~~~~~~~

Enable frontend editing for records in fluid.

Example:

.. code-block:: html

   <core:contentEditable table="tt_content" field="bodytext" uid="{item.uid}">
       {item.bodytext}
   </core:contentEditable>

Output:

.. code-block:: html

   <div contenteditable="true" data-table="tt_content" data-field="bodytext" data-uid="1">
       This is the content text to edit
   </div>

Example with RTE links parsed:

When not using *lib.parseFunc* internal links will have the following format *t3://page?uid=1*

.. code-block:: html

   <core:contentEditable table="tt_content" field="bodytext" uid="{item.uid}">
        <f:format.html parseFuncTSPath="lib.parseFunc">
            {item.bodytext}
        </f:format.html>
   </core:contentEditable>

Output:

.. code-block:: html

   <div contenteditable="true" data-table="tt_content" data-field="bodytext" data-uid="1">
       This is the content text to edit <a href="https://mydomain.com/path/to/page">My parsed link</a>
   </div>


A full example on how to get a news single record content editable can be found here. Note
that the headline, teaser and description are inline editable.

https://gist.github.com/MattiasNilsson/903b5cfe770fa9eadafd3d4de2c32895


.. _viewhelpers-customdropzone:

customDropZone
~~~~~~~~~~~~~~

Inserts a custom drop zone. Read more: :ref:`custom-records-dropzone`


.. _viewhelpers-isfrontendeditingactive:

isFrontendEditingActive
~~~~~~~~~~~~~~~~~~~~~~~

Useful to determine whether or not frontend editing is active. Use in conditions to hide or show content for editors.

Example:

.. code-block:: html

   <f:if condition="{core:isFrontendEditingActive()}">
      <p>You're using Frontend Editing. Congratulations!</p>
   </f:if>

No output if Frontend editing is disabled. Output if Frontend Editing is enabled:

.. code-block:: html

   <p>You're using Frontend Editing. Congratulations!</p>


.. _viewhelpers-isplaceholderenabled:

isPlaceholderEnabled
~~~~~~~~~~~~~~~~~~~~

Use this view helper in conditions to show empty fields when the :ref:`placeholder feature <extension-manager-settings-features>` is enabled.

Example:

.. code-block:: html

     <f:if condition="{header} || {core:isPlaceholderEnabled()}">
       {header}
     </f:if>
