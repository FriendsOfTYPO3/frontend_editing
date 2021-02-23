import '../../Resources/Public/Css/frontend_editing.css';
import '../../Resources/Public/Css/inline_editing.css';


try {
    require('../../.Build/Web/typo3/sysext/backend/Resources/Public/Css/backend.css');
} catch (ex) {
    // shit happens
    console.log(ex);
}

if (!window.TYPO3) {
    window.TYPO3 = {
        settings: {
            ajaxUrls: {
                icons: ''
            },
        }
    };
}
