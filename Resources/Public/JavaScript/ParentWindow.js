define([
  'TYPO3/CMS/Backend/FormEngineLinkBrowserAdapter',
  'TYPO3/CMS/Recordlist/ElementBrowser'
], function(FormEngineLinkBrowserAdapter, ElementBrowser) {
  const FormEngineLinkBrowserAdapterParentFunction = FormEngineLinkBrowserAdapter.getParent;
  const queryString = window.location.search;
  const urlParams = new URLSearchParams(queryString);

  if (urlParams.has('frontend_editing')) {
    const getParent = () => {
      // Accessing the frame using .contentWindow does not
      // work in Chrome or Edge.
      if (
        typeof window.parent !== 'undefined' &&
        typeof window.parent.document.list_frame !== 'undefined' &&
        window.parent.document.list_frame.length > 0
      ) {
        var frame = window.parent.document.list_frame[window.parent.document.list_frame.length - 1];
        frame = frame.contentWindow || frame;
        if (frame.parent.document.querySelector('.t3js-modal-iframe') !== null) {
          return frame;
        }
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
  } else {
    FormEngineLinkBrowserAdapter.getParent = () => {
      return FormEngineLinkBrowserAdapterParentFunction();
    }

    ElementBrowser.getParent = () => {
      ElementBrowser.opener = FormEngineLinkBrowserAdapter.getParent()
      return ElementBrowser.opener;
    }
  }
});
