.. include:: ../Includes.txt



.. _new-content-elements:

New Content Elements
------------

It's possible to add drop zones for new content elements in a custom content elements. This is done by the class called ContentEditableWrapperService.

- Example of usage.

  .. code-block:: typoscript

     page = PAGE
     page.1001 = USER
     page.1001 {
        userFunc = Your\NameSpace\YourWrappingClass->wrapWithDropZone
     }

- Create your PHP class with user function

  .. code-block:: php
    namespace Your\NameSpace;
      class YourWrappingClass {
        /**
         * @param  string          Empty string (no content to process)
         * @param  array           TypoScript configuration
         */
        public function wrapWithDropZone($content, $conf)
        {
          if (\TYPO3\CMS\Core\Utility\GeneralUtility::_GET('frontend_editing')
            && \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\FrontendEditing\Service\AccessService::class)->isEnabled()
          ) {
            /** @var \TYPO3\CMS\FrontendEditing\Service\ContentEditableWrapperService $wrapperService */
            $wrapperService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\FrontendEditing\Service\ContentEditableWrapperService::class);

            $content = $wrapperService->wrapContentWithDropzone(
              'tt_content', // table name
              -1, // page uid, pid
              $content,
              0, // colPos
              // additional fields if needed
              [
                'subheader' => 'default subheader'
              ]
            );
          }
          return $content;
        }
      }