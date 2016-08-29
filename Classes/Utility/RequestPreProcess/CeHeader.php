<?php
namespace TYPO3\CMS\FrontendEditing\Utility\RequestPreProcess;

use TYPO3\CMS\FrontendEditing\Controller\SaveController;

/**
 * Hook for saving content element "header"
 */
class CeHeader implements RequestPreProcessInterface
{

    /**
     * Pre process the request
     *
     * @param array $request save request
     * @param bool $finished
     * @param \TYPO3\CMS\FrontendEditing\Controller\SaveController $parentObject
     * @return array
     */
    public function preProcess(array &$request, &$finished, SaveController &$parentObject)
    {
        // Only allowed for field header
        if ($parentObject->getTable() === 'tt_content' && (
                $parentObject->getField() === 'header'
                || $parentObject->getField() === 'subheader'
            )
        ) {
            // Check if we need to update header-type field
            if (substr($request['content'], 0, 2) === "<h") {
                $headerTypeRequest = $request;

                switch (substr($request['content'], 0, 3)) {
                    case '<h1':
                        $headerTypeRequest['content'] = 1;
                        break;
                    case '<h2':
                        $headerTypeRequest['content'] = 2;
                        break;
                    case '<h3':
                        $headerTypeRequest['content'] = 3;
                        break;
                    case '<h4':
                        $headerTypeRequest['content'] = 4;
                        break;
                    case '<h5':
                        $headerTypeRequest['content'] = 5;
                        break;
                    case '<h6':
                        $headerTypeRequest['content'] = 6;
                        break;
                    default:
                        $headerTypeRequest['content'] = 0;
                        break;
                }
                // Do a direct save for the header-type field.
                $headerTypeRequest['identifier'] =
                    $parentObject->getTable() .
                    '--header_layout--' .
                    $parentObject->getUid()
                ;
                // $parentObject->directSave($headerTypeRequest, TRUE);

                $parentObject->setField('header');
            }

            // Remove tags so we only have the plaint text.
            $request['content'] = urldecode(strip_tags($request['content']));
        }

        return $request;
    }
}
