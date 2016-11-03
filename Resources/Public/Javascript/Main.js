(function(w) {

    'use strict';

    // The global F object for API calls
    window.F = new FrontendEditing({

        // LocalStorage for changes in editors
        storage: new FrontendEditing.Storage('TYPO3:FrontendEditing'),

    });

    // Initialize the GUI
    F.initGUI({
        iframeUrl: iframeUrl,
        iframeLoadedCallback: Editor.init,
    });

})(window);