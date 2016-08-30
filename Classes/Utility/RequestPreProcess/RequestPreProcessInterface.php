<?php
namespace TYPO3\CMS\FrontendEditing\Utility\RequestPreProcess;

use \TYPO3\CMS\FrontendEditing\Controller\SaveController;

/**
 * Interface RequestPreProcessInterface
 */
interface RequestPreProcessInterface
{
    /**
     * Pre process the request
     *
     * @param array $body save body
     * @param bool $finished
     * @param \TYPO3\CMS\FrontendEditing\Controller\SaveController $parentObject
     * @return array
     */
    public function preProcess(
        array &$body,
        &$finished,
        SaveController &$parentObject
    );
}
