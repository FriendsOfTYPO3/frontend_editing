.. include:: /Includes.rst.txt



.. _emptycolumns:

============================
Drop zones for empty columns
============================

Premise
~~~~~~~
The Frontend Editing extension automatically adds one drop zone at the beginning and one at the end of each content element so the user can drop a new content element before or after an existing content element.

When a page or container is empty, there are no drop zones to allow the user to add new content elements. The Frontend Editing extension can't automatically add the first drop zone because it can't know the colPos value when the frontend is rendered.

To solve this issue, the admin can add a custom drop zone in the page or container templates.

.. _emptycolumns-page:

Example of custom drop zone for a column of a page
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: html

   <core:customDropZone tables="{0:'tt_content'}" pageUid="{data.uid}" defVals="{colPos: 0}" />

The needed argument in this case are:

* the table (tt_content)
* the page uid
* the colPos in which the element will be placed

.. _emptycolumns-container:

Example of custom drop zone for a column of a container (EXT:container)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: html

   <core:customDropZone tables="{0:'tt_content'}" pageUid="{data.uid}" defVals="{colPos: 101, tx_container_parent: data.uid}" />

The needed argument in this case are:

* the table of the CE (tt_content)
* the page uid
* the colPos of the column of the container in which the element will be placed as default value (defVals)
* in the case of EXT:container, the uid of the parent (field: tx_container_parent) as default value (defVals)

.. tip::

   The Frontend Editing extension uses json_encode for defVals viewhelper argument. Depending on your template setup it could be necessary to wrap the core:customDropZone viewhelper with a f:format.htmlentities viewhelper.
