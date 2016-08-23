<?php
namespace TYPO3\CMS\FrontendEditing\Utility\RequestPreProcess;

use TYPO3\CMS\FrontendEditing\Controller\SaveController;

/**
 * Hook for cleaning content
 *
 * @package TYPO3
 * @subpackage tx_aloha
 */
class Cleanup implements \TYPO3\CMS\FrontendEditing\Utility\RequestPreProcess\RequestPreProcessInterface {

    /**
     * Preprocess the request
     *
     * @param array $request save request
     * @param boolean $finished
     * @param \TYPO3\CMS\FrontendEditing\Controller\SaveController $parentObject
     * @return array
     */
    public function preProcess(array &$request, &$finished, SaveController &$parentObject) {
        $request['content'] = $this->modifyContent($request['content']);
        return $request;
    }

    /**
     * Cleanup
     *
     * @param string $content
     * @return string
     */
    private function modifyContent($content) {
        $content = trim($content);
        $lengthOfContent = strlen($content);
        $cleanUpWords = ['<br />', '<br>', '<br/>', '<br style="">'];

        foreach ($cleanUpWords as $cleanupWord) {
            $length = strlen($cleanupWord);

            // Clean from the beginning
            if (substr($content, 0, $length) === $cleanupWord) {
                $content = substr($content, $length + 1, $lengthOfContent);
            }
            // Clean from the end
            if (substr($content, 0, ($length * -1)) === $cleanupWord) {
                $newLengthOfContent = $lengthOfContent - $length;
                $content = substr($content, 0, $newLengthOfContent);
            }

        }
var_dump($content);
        return $content;
    }
}