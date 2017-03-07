.. include:: ../../Includes.txt

.. highlight:: rst

============
Dependencies
============

We rely on node.js for a lot of our tooling. So if you haven't got it installed go to http://nodejs.org/ and fetch it.

To install tooling dependencies, run:

npm install

Composer must also be installed. Download it and install it from the composer website, then move it to your binary path. If you are unsure of where it is try echo "$PATH", it will usually be /usr/local/bin or /usr/local.

::

   curl -sS https://getcomposer.org/installer | php
   sudo mv ./composer.phar /usr/bin/

Make it executable and link to it:

::

   sudo chmod +x /usr/bin/composer.phar
   sudo ln -s /usr/bin/composer.phar /usr/bin/composer

Then install the composer dependencies:

::

   composer install

.. note::

   NOTE: On Ubuntu be sure to have php5 and php5-curl installed

   When asked for a token, go to to your GitHub account and into Settings->Personal access tokens and click Generate new token, copy-paste the token into the input
