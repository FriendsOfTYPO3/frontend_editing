.. include:: ../../Includes.txt

.. highlight:: rst

========
Workflow
========

For working with the extension, the following can be run to accomplish common tasks.

Test
----

Run the following command to execute the PHP codesniffer:

::

   npm run php:codesniffer

Run the following command to run the PHP Unit tests:


::

   npm run php:unittests

Run this command to simulate the full build process locally:

::

   npm run build:suite --silent

Add node_modules to Public/Resources folder
-------------------------------------------

Add the toastr (notifications) node_module to Public/Resources folder:

::

   npm run add:resource:toastr

Add the immutable (https://facebook.github.io/immutable-js/) node_module to Public/Resources folder:

::

   npm run add:resource:immutable

Add the alertify.js (confirms) node_module to Public/Resources folder:

::

   npm run add:resource:alertify

Styling
-------

The extension is using LESS.
Use the following command to build the stylesheets:

::

   npm run build:css

Use the following watch command while developing:

::

   npm run watch:css

Publish
-------

Use the following command to copy all necessary node_modules into Public/Resources folder and compile the LESS:
::

   npm run build:extension

