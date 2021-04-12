.. include:: ../Includes.txt



.. _extension-manager-settings:

==================================
Available extension configurations
==================================

These configurations can be found in *Admin Tools > Settings*.

.. _extension-manager-settings-basic:


Basic
=====

The tag name that will be used for the Content editable wrapper
---------------------------------------------------------------

:aspect:`DataType`
   string

:aspect:`Default`
   div

The default wrapper tag name used by the frontend editing functionality is
`div`, but you can change it to something else here.

.. tip::

   You can override the default tag using the
   :ref:`core:contentEditable ViewHelper's <fluid-styled-content>` optional
   tag argument.


.. _extension-manager-settings-features:

Features
========

Enable placeholders and direct drop-to-edit without modal
---------------------------------------------------------

:aspect:`DataType`
   boolean

:aspect:`Default`
   true

This feature makes empty editable content areas appear with placeholders. Content elements will also appear directly after you have dropped them on the page, skipping the modal pop-up with a backend form that will otherwise appear.

You should adapt your templates to show empty fields using the :ref:`viewhelpers-isplaceholderenabled` view helper.
