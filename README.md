<h1>Pixelant</h1>

[![CircleCI](https://circleci.com/gh/pixelant/frontend_editing.svg?style=svg)](https://circleci.com/gh/pixelant/frontend_editing) [![TYPO3](https://img.shields.io/badge/TYPO3-7.6.0-orange.svg?style=flat-square)](https://typo3.org/)

<h2>Pixelant frontend editing (frontend_editing)</h2>
This package gives frontend editing capability to TYPO3 CMS, the editor used is [Ckeditor](http://ckeditor.com/).

[Dependencies](#dependencies) &nbsp; [Workflow](#workflow)&nbsp;

<a name="dependencies"/>
## Dependencies
We rely on node.js for a lot of our tooling. So if you haven't got it installed(shame on you!!) go to http://nodejs.org/ and fetch it.

To install tooling dependencies, run:

    npm install

Composer must also be installed. Download it and install it from the composer website, then move it to your binary path.
If you are unsure of where it is try echo "$PATH", it will usually be /usr/local/bin or /usr/local.

    curl -sS https://getcomposer.org/installer | php
    sudo mv ./composer.phar /usr/bin/

Make it executable and link to it:

    sudo chmod +x /usr/bin/composer.phar
    sudo ln -s /usr/bin/composer.phar /usr/bin/composer

Then install the composer dependencies:

    composer install
    
> NOTE: On Ubuntu be sure to have php5 and php5-curl installed
>
> When asked for a token, goto to your github account and into settings->personal access tokens and click Generate new token, copy-paste the token into the input
    
## Workflow
For working with the extension, the following can be run to accomplish common tasks.

### Test

To run the PHP codesniffer run the following command:

    npm run php:codesniffer

To run the PHP Unit tests run the following command:

    npm run php:unittests

To simulate the full build process locally, then run this packaged command:

    npm run build:suite --silent

### Add node_modules to Public/Resources folder

Add the ckeditor node_module to Public/Resources folder:

    npm run add:resource:ckeditor

Add the toastr (notifications) node_module to Public/Resources folder:

    npm run add:resource:toastr

### Styling

The extension are using SASS for compiling into CSS. 

To build the stylesheets use the following command:

    npm run build-css

While developing use the following watch command:

    npm run watch-css

### Publish

To build the extension before a publish to TER use the following command to copy all necessary
node_modules into Public/Resources folder (is a bundle of all **add:resource** commands):

    npm run build:extension