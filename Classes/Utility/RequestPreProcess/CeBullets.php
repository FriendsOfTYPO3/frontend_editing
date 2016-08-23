<?php
namespace TYPO3\CMS\FrontendEditing\Utility\RequestPreProcess;

use TYPO3\CMS\FrontendEditing\Controller\SaveController;

/**
 * Hook for saving content element "bullets"
 *
 * @package TYPO3
 * @subpackage tx_aloha
 */
class CeBullets implements \TYPO3\CMS\FrontendEditing\Utility\RequestPreProcess\RequestPreProcessInterface {

    /**
     * Preprocess the request
     *
     * @param array $request save request
     * @param boolean $finished
     * @param \TYPO3\CMS\FrontendEditing\Controller\SaveController $parentObject
     * @return array
     */
    public function preProcess(array &$request, &$finished, SaveController &$parentObject) {
        $record = $parentObject->getRecord();

        // only allowed for bullet element
        if ($parentObject->getTable() === 'tt_content'
            && $parentObject->getField() === 'bodytext'
            && $record['CType'] === 'bullets'
        ) {
            $finished = true;

            $domDocument = new \DOMDocument();
            $domDocument->loadHTML('<?xml encoding="utf-8" ?>' . $request['content']);

            $liCollection = $domDocument->getElementsByTagName('li');
            $tempLiElements = [];
            foreach ($liCollection as $class) {
                $value = trim($class->nodeValue);
                if (!empty($value)) {
                    $tempLiElements[] = $value;
                }
            }
            $request['content'] = implode(PHP_EOL, $tempLiElements);
        }

        return $request;
    }
}