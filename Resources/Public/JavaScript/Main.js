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
 * Main: Initialize the application
 */
(function(w) {

    'use strict';

    // The global F object for API calls
    window.F = new FrontendEditing({
        // LocalStorage for changes in editors
        storage: new FrontendEditing.Storage('TYPO3:FrontendEditing')
    });

    // Initialize the GUI
    F.initGUI({
        iframeUrl: iframeUrl,
        iframeLoadedCallback: Editor.init
    });

})(window);