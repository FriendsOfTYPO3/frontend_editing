.. include:: ../../Includes.txt

.. highlight:: rst

============
Dependencies
============

We rely on node.js for a lot of our tooling. Go to http://nodejs.org/ and install it.

To install the tooling dependencies, run:

::

   npm install

Composer must also be installed. Download and install it from https://getcomposer.org/, then move it to your binary path. If you are unsure of the location try echo "$PATH", it will usually be /usr/local/bin or /usr/local.

::

   curl -sS https://getcomposer.org/installer | php
   sudo mv ./composer.phar /usr/bin/

Make it executable and create symlink:

::

   sudo chmod +x /usr/bin/composer.phar
   sudo ln -s /usr/bin/composer.phar /usr/bin/composer

Then install the composer dependencies:

::

   composer install

.. note::

   NOTE: For Ubuntu users: be sure to have php5 and php5-curl installed

   When asked for a token, go to to your GitHub account to Settings->Personal access tokens and click 'Generate new token', then copy-paste the token into the input
