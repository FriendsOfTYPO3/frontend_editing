.. include:: ../../Includes.txt

.. highlight:: rst

========
Workflow
========

For working with the extension, the following can be run to accomplish common tasks.

Test
----

To run the PHP codesniffer run the following command:

::

   npm run php:codesniffer

To run the PHP Unit tests run the following command:


::

   npm run php:unittests

To simulate the full build process locally, then run this packaged command:

::

   npm run build:suite --silent

Add node_modules to Public/Resources folder
-------------------------------------------

Add the ckeditor node_module to Public/Resources folder:

::

   npm run add:resource:ckeditor

Add the toastr (notifications) node_module to Public/Resources folder:

::

   npm run add:resource:toastr

Add the immutable (https://facebook.github.io/immutable-js/) node_module to Public/Resources folder:

::

   npm run add:resource:immutable

Add the alertify.js (confirms) node_module to Public/Resources folder:

::

   npm run add:resource:alertify

Add the lity (modals) node_module to Public/Resources folder:

::

   npm run add:resource:lity

Styling
-------

The extension are using SASS for compiling into CSS.
To build the stylesheets use the following command:

::

   npm run build:css

While developing use the following watch command:

::

   npm run watch:css

Publish
-------

To build the extension before a publish to TER use the following command to copy all necessary node_modules into Public/Resources folder (is a bundle of all add:resource commands) and compile the SASS:
::

   npm run build:extension

