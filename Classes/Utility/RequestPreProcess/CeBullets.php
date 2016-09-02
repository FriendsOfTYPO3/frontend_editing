<?php
namespace TYPO3\CMS\FrontendEditing\Utility\RequestPreProcess;

use TYPO3\CMS\FrontendEditing\Controller\CrudController;

/**
 * Hook for saving content element "bullets"
 */
class CeBullets implements RequestPreProcessInterface
{

    /**
     * Pre process the request
     *
     * @param array $body save request
     * @param bool $finished
     * @param \TYPO3\CMS\FrontendEditing\Controller\CrudController $parentObject
     * @return array
     */
    public function preProcess(array &$body, &$finished, CrudController &$parentObject)
    {
        $record = $parentObject->getRecord();

        // only allowed for bullet element
        if ($parentObject->getTable() === 'tt_content'
            && $parentObject->getField() === 'bodytext'
            && $record['CType'] === 'bullets'
        ) {
            $finished = true;

            $domDocument = new \DOMDocument();
            $domDocument->loadHTML('<?xml encoding="utf-8" ?>' . $body['content']);

            $liCollection = $domDocument->getElementsByTagName('li');
            $tempLiElements = [];
            foreach ($liCollection as $class) {
                $value = trim($class->nodeValue);
                if (!empty($value)) {
                    $tempLiElements[] = $value;
                }
            }

            $body['content'] = implode(PHP_EOL, $tempLiElements);
        }

        return $body;
    }
}
