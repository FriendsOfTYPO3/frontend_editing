define([
  'jquery',
  'TYPO3/CMS/FrontendEditing/Storage',
  'TYPO3/CMS/Backend/Modal',
  'TYPO3/CMS/Backend/Severity'
], function($, Storage, Modal, Severity) {

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

    console.log('TRIGGERED');
    //
  // $('.t3js-scaffold-content #typo3-pagetree-tree .node-bg').off();
    $('.t3js-scaffold-content').on('click', '#typo3-pagetree-tree .node-bg', function(event) {
      //event.preventDefault();
      //event.stopPropagation();
      if (window.frames[1].window.frames[0]) {
        // alert('IFRAME');
      }

      console.log(storage.isEmpty());
      console.log(localStorage.getItem('TYPO3:FrontendEditing'));
// localStorage.getItem(this.storageKey)

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
          console.log(e);
          if (e.target.name === 'no') {
            Modal.dismiss();
          } else if (e.target.name === 'yes') {
            Modal.dismiss();
            callback.call(null, true);
          } else if (e.target.name === 'save') {
            // $('form[name=' + FormEngine.formName + ']').append($elem);
            Modal.dismiss();
            $('.t3-frontend-editing__save').click();
            // FormEngine.saveDocument();
          }
        });
      // }
    });
    /*new RegularEvent('click', function (e) {
      console.log('Clicked element:', e.target);
    }).bindTo(document.querySelector('.node-bg'));*/
  //});
});
