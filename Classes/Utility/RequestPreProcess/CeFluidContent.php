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
 * Hook for saving content element "fluid content"
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class CeFluidContent implements RequestPreProcessInterface
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
        $record = $parentObject->getRecord();

        // When storing fluidcontent flexform fields,
        // field is set by pi_flexform-flexformfieldname.
        list($field, $flexformField) = explode('-', $parentObject->getField(), 2);

        // only allowed for element "fluidcontent"
        if ($parentObject->getTable() === 'tt_content'
            && $field === 'pi_flexform'
            && $record['CType'] === 'fluidcontent_content'
        ) {
            $parentObject->setField($field);

            $xml = new \SimpleXMLElement($record['pi_flexform']);

            // @TODO: Maybe give possibility for fields to have html tags
            $fieldAllowedTags = '<sup><sub>';
            foreach ($xml->xpath(
                '//T3FlexForms/data/sheet/language/field[@index = "' . $flexformField . '"]'
            ) as $entry) {
                $content = trim($request['content']);
                $content = strip_tags(urldecode($content), $fieldAllowedTags);

                // Try to remove invalid characters so save won't break xml if there are invalid characters in string
                $content = iconv('UTF-8', 'UTF-8//IGNORE', $content);

                $node = dom_import_simplexml($entry->value);
                $node->nodeValue = '';
                $node->appendChild($node->ownerDocument->createCDATASection($content));
            }

            $request['content'] = $xml->saveXml();
        }

        return $request;
    }
}
