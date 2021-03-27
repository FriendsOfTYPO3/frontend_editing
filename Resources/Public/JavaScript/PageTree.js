define([
  'jquery',
  'TYPO3/CMS/FrontendEditing/Storage',
  'TYPO3/CMS/Backend/Modal',
  'TYPO3/CMS/Backend/Severity'
], function($, Storage, Modal, Severity) {
  var storage = new Storage('TYPO3:FrontendEditing');
  var pageTreeSelector = '#typo3-pagetree-tree';

  $('.t3js-scaffold-content').on('click', pageTreeSelector + ' .node-bg, ' + pageTreeSelector + ' .node', function() {
    // Check that the iframe for frontend_editing is present
    if ($('#typo3-contentIframe').contents().find('#tx_frontendediting_iframe').length > 0) {
      // If there are changes unsaved on the page then prompt a Modal
      if (storage.isEmpty() === false) {
        // Disable navigation in PageTree until decision is made in Modal
        top.TYPO3.Backend.NavigationContainer.PageTree.instance.settings.readOnlyMode = true;

        var title = TYPO3.lang['label.confirm.close_without_save.title'] || 'Do you want to close without saving?';
        var content = TYPO3.lang['label.confirm.close_without_save.content'] || 'You currently have unsaved changes. Are you sure you want to discard these changes?';
        var buttons = [
          {
            text: TYPO3.lang['buttons.confirm.close_without_save.no'] || 'No, I will continue editing',
            btnClass: 'btn-default',
            name: 'no',
          },
          {
            text: TYPO3.lang['buttons.confirm.close_without_save.yes'] || 'Yes, discard my changes',
            btnClass: 'btn-default',
            name: 'yes'
          },
          {
            text: TYPO3.lang['buttons.confirm.save_and_close'] || 'Save and close',
            btnClass: 'btn-warning',
            name: 'save',
            active: true
          }
        ];

        var $modal = Modal.confirm(title, content, Severity.warning, buttons);
        $modal.on('button.clicked', function (e) {
          if (e.target.name === 'no') {
            // Do nothing just close Modal
            Modal.dismiss();
            // Activate the PageTree actions again
            top.TYPO3.Backend.NavigationContainer.PageTree.instance.settings.readOnlyMode = false;
          } else if (e.target.name === 'yes') {
            // Discard changes and continue the navigation
            Modal.dismiss();
            // Send command to pageTree for which to page to navigate to
            if (top.TYPO3.Backend.NavigationContainer.PageTree.instance.getSelectedNodes()[0]) {
              top.TYPO3.Backend.NavigationContainer.PageTree.instance.clickOnLabel(top.TYPO3.Backend.NavigationContainer.PageTree.instance.getSelectedNodes()[0]);
              top.TYPO3.Backend.NavigationContainer.PageTree.instance.sendChangeCommand(top.TYPO3.Backend.NavigationContainer.PageTree.instance.getSelectedNodes()[0]);
            }
            // Activate the PageTree actions again
            top.TYPO3.Backend.NavigationContainer.PageTree.instance.settings.readOnlyMode = false;
          } else if (e.target.name === 'save') {
            // Save the content and the continue navigation
            // Content iframe and the frontend editing iframe
            $('#typo3-contentIframe').contents().find("#tx_frontendediting_iframe").contents().find('.t3-frontend-editing__save').click();
            Modal.dismiss();
            // Activate the PageTree actions again
            top.TYPO3.Backend.NavigationContainer.PageTree.instance.settings.readOnlyMode = false;
          }
        });
      }
    }
  });
});
