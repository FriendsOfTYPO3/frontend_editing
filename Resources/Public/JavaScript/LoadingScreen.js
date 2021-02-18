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
 * LoadingScreen: Fade in and out the loading screen
 */
define(['jquery'], function LoadingScreenFactory ($) {
    'use strict';

    return function LoadingScreen (element) {
        var $loadingScreen = $(element);
        var loadingScreenLevel = 0;

        return {
            showLoadingScreen: function () {
                if (loadingScreenLevel === 0) {
                    $loadingScreen.fadeIn('fast');
                }

                loadingScreenLevel++;
            },
            hideLoadingScreen: function () {
                loadingScreenLevel--;

                if (loadingScreenLevel <= 0) {
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
