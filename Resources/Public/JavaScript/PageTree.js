define([
  'jquery',
  'TYPO3/CMS/FrontendEditing/Storage',
  'TYPO3/CMS/Backend/Modal',
  'TYPO3/CMS/Backend/Severity',
  'TYPO3/CMS/Backend/PageTree/PageTreeElement'
], function($, Storage, Modal, Severity, PageTreeElement) {

  /*if (document.getElementById('typo3-pagetree-tree')) {
    new RegularEvent('click', function (e) {
      console.log('Clicked element:', e.target);
    }).bindTo(document.getElementById('typo3-pagetree-tree'));
  }*/

  /*const el = document.querySelector('.node-bg');
  if (el) {
    new RegularEvent('click', function () {
      // $('#tx_frontendediting_iframe').length
      // console.log($('#typo3-contentIframe #tx_frontendediting_iframe'));
      if (document.getElementById('tx_frontendediting_iframe')) {
        console.log('YESSER');
      }
      // el.target.style.width = window.scrollY + 100 + 'px';
      console.log('yes');
    }).bindTo(el);
  }*/

  // LocalStorage for changes that are to be saved
  // var storage = null;

  //$(document).ready(function() {

    // window.frames[1].window.frames[0];
    if (window.frames[1].window.frames[0]) {
      console.log('INNER');
    }

    let storage = new Storage('TYPO3:FrontendEditing');
    //let pageTreeElement = new PageTreeElement();
    console.log('TRIGGERED');
    //

  // $('.t3js-scaffold-content #typo3-pagetree-tree .node-bg').off();
    $('.t3js-scaffold-content').on('click', '#typo3-pagetree-tree .node-bg, #typo3-pagetree-tree .node', function(event) {

      // Disable navigation in PageTree until decision is made in Modal
      top.TYPO3.Backend.NavigationContainer.PageTree.instance.settings.readOnlyMode = true;

      //event.preventDefault();
      //event.stopPropagation();
      if (window.frames[1].window.frames[0]) {
        // alert('IFRAME');
      }

      console.log(storage.isEmpty());
      console.log(localStorage.getItem('TYPO3:FrontendEditing'));
// localStorage.getItem(this.storageKey)

      // If there are changes unsaved on the page then prompt a Modal
      //if (storage.isEmpty() === false) {
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
          // Activate the PageTree actions again
          top.TYPO3.Backend.NavigationContainer.PageTree.instance.settings.readOnlyMode = false;
          if (e.target.name === 'no') {
            // Do nothing just close Modal
            Modal.dismiss();
          } else if (e.target.name === 'yes') {
            // Discard changes and continue the navigation
            Modal.dismiss();
            // @TODO: Continue navigation somehow...
            // callback.call(null, true);
          } else if (e.target.name === 'save') {
            // Save the content and the continue navigation
            // Content iframe and the frontend editing iframe
            $('#typo3-contentIframe').contents().find("#tx_frontendediting_iframe").contents().find('.t3-frontend-editing__save').click();
            Modal.dismiss();
          }
        });
      // }
    });
    /*new RegularEvent('click', function (e) {
      console.log('Clicked element:', e.target);
    }).bindTo(document.querySelector('.node-bg'));*/
  //});
});
