<?php
namespace TYPO3\CMS\FrontendEditing\Utility\RequestPreProcess;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\FrontendEditing\Controller\CrudController;

/**
 * Hook for cleaning content
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class Cleanup implements RequestPreProcessInterface
{

    /**
     * Pre process the request
     *
     * @param array $request save request
     * @param bool $finished
     * @param \TYPO3\CMS\FrontendEditing\Controller\CrudController $parentObject
     * @return array
     */
    public function preProcess(array &$request, &$finished, CrudController &$parentObject)
    {
        $request['content'] = $this->modifyContent($request['content']);
        return $request;
    }

    /**
     * Cleanup
     *
     * @param string $content
     * @return string
     */
    private function modifyContent($content)
    {
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

        return $content;
    }
}
