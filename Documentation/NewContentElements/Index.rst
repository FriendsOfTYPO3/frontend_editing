.. include:: ../Includes.txt



.. _content-elements:

Content elements
----------------

New Content Elements
""""""""""""""""""""

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

     <?php

     namespace Your\NameSpace;

     use TYPO3\CMS\Core\Utility\GeneralUtility;
     use TYPO3\CMS\FrontendEditing\Service\AccessService;
     use TYPO3\CMS\FrontendEditing\Service\ContentEditableWrapperService;

     class YourWrappingClass {

          /**
          * @param string $content Empty string (no content to process)
          * @param array $conf TypoScript configuration
          * @return string $content
          */
          public function wrapWithDropZone($content, $conf)
          {
               if (GeneralUtility::_GET('frontend_editing') && GeneralUtility::makeInstance(AccessService::class)->isEnabled()) {
                    $wrapperService = GeneralUtility::makeInstance(ContentEditableWrapperService::class);

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

.. _custom-dropzone-modifier:

Custom Dropzone modifier (using frontend editing together with Gridelements)
""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""

Here is a full example of how to use the hook (wrapWithDropZone) together with
Gridelements (https://github.com/TYPO3-extensions/gridelements) and multi column splitters:

https://gist.github.com/joekolade/674ecba5c2615901581d6c4e4c272b4a
