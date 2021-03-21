/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Module: TYPO3/CMS/FrontendEditing/LoadingScreen
 * Fade in and out the loading screen
 */
define(['jquery', './Utils/Logger'], function LoadingScreenFactory ($, Logger) {
    'use strict';

    var log = Logger('FEditing:Component:LoadingScreen');
    log.trace('--> LoadingScreenFactory');

    return function LoadingScreen (element) {
        log.trace('LoadingScreen', element);

        var $loadingScreen = $(element);
        var loadingScreenLevel = 0;

        return {
            showLoadingScreen: function () {
                log.trace('showLoadingScreen', loadingScreenLevel);

                if (loadingScreenLevel === 0) {
                    log.info('show loading screen');

                    $loadingScreen.fadeIn('fast');
                }

                loadingScreenLevel++;
                log.debug('new loadingScreenLevel', loadingScreenLevel);
            },
            hideLoadingScreen: function () {
                log.trace('--> LoadingScreenFactory');
                loadingScreenLevel--;

                log.debug('new loadingScreenLevel', loadingScreenLevel);

                if (loadingScreenLevel <= 0) {
                    log.info('hide loading screen');

                    loadingScreenLevel = 0;
                    $loadingScreen.fadeOut('slow');
                }
            },
            getLoadingScreenLevel: function () {
                return loadingScreenLevel;
            }
        };
    };
});
