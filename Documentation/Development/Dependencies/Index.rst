.. include:: ../../Includes.txt

.. highlight:: rst

============
Dependencies
============

We rely on node.js for a lot of our tooling. Go to http://nodejs.org/ and install it.

To install the tooling dependencies, run:

::

   npm install

Composer must also be installed. Download and install it from https://getcomposer.org/, then install the Composer dependencies:

::

   composer install

.. note::

   NOTE: For Ubuntu users: be sure to have php5 and php5-curl installed

   When asked for a token, go to to your GitHub account to Settings->Personal access tokens and click 'Generate new token', then copy-paste the token into the input
