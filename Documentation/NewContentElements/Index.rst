.. include:: ../Includes.txt



.. _content-elements:

Content elements
----------------


.. _custom-records-dropzone:

Custom Records
""""""""""""""

With this feature, webmaster is allowed to insert directly record into custom configured zone.
For example: a webmaster can insert news directly into the news list.

The configuration is done in 2 parts.

A custom zone is added via "CustomDropZoneViewHelper" in record listing
Allowed record are registered in TypoScript as follows:

  .. code-block:: typoscript

    plugin.tx_frontendediting{
        customRecords {
            10 {
                table = tx_news_domain_model_news
                pid = 6
            }
        }
    }

After the Typoscript is added you also need to adjust the Fluid template with
the following code:

.. code-block:: html

    <core:customDropZone tables="{0:'tx_news_domain_model_news'}">
    </core:customDropZone>

The result will be this:

  .. figure:: ../Images/CustomRecordsDropzone.png
     :alt: Custom records for dropzones

.. _new-content-elements:

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
