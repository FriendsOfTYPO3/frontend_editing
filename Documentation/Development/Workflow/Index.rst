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

Styling
-------

The extension is using SASS. The build-process order is:

* CSS linting, based on TYPO3.CMS (stylelint)
* Compile to CSS (node-sass)
* Add vendor prefixes, based on TYPO3.CMS (autoprefixer)
* Minifying CSS (postcss-clean)

Use the following watch command while developing:

::

   npm run watch:css

Use the following command to build the stylesheets:

::

   npm run build:css


Publish
-------

Use the following command to copy all necessary node_modules into Public/Resources folder and compile the SASS:

::

   npm run build:extension


Storybook
---------

The extension is using Storybook (https://storybook.js.org/) for component testing.

Use the following commands to build the Storybook and url is http://localhost:6006/

::

   npm run build:storybook

Use the following to watch and build the Storybook:

::

   npm run watch:storybook