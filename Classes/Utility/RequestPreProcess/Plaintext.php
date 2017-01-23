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
 * Hook for for saving content element "text"
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class Plaintext implements RequestPreProcessInterface
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
        // Only allowed for "special" field "bodytext-plaintext"
        if ($parentObject->getTable() === 'tt_content' &&
            $parentObject->getField() == 'bodytext-plaintext'
        ) {
            $request['content'] = $this->modifyContent($request['content']);
            $parentObject->setField('bodytext');
        }
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
        // @TODO: Maybe give possibility for fields to have html tags
        $fieldAllowedTags = '';

        $content = trim($content);
        $content = strip_tags(
            urldecode(
                html_entity_decode($content)
            ),
            $fieldAllowedTags
        );

        return $content;
    }
}
