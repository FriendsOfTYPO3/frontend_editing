define([
    'TYPO3/CMS/Backend/FormEngineLinkBrowserAdapter',
    'TYPO3/CMS/Recordlist/ElementBrowser'
  ], function(FormEngineLinkBrowserAdapter, ElementBrowser) {
    const FormEngineLinkBrowserAdapterParentFunction = FormEngineLinkBrowserAdapter.getParent;
    const getParent = () => {
      if (
        typeof window.parent !== 'undefined' &&
        typeof window.parent.document.list_frame !== 'undefined' &&
        window.parent.document.list_frame.length > 0 &&
        window.parent.document.list_frame[window.parent.document.list_frame.length - 1].contentWindow.parent.document.querySelector('.t3js-modal-iframe') !== null
      ) {
        return window.parent.document.list_frame[window.parent.document.list_frame.length - 1].contentWindow;
      }
      return null;
    }

    FormEngineLinkBrowserAdapter.getParent = () => {
      return getParent() || FormEngineLinkBrowserAdapterParentFunction();
    }

    ElementBrowser.getParent = () => {
      ElementBrowser.opener = FormEngineLinkBrowserAdapter.getParent()
        return ElementBrowser.opener;
    }
});
