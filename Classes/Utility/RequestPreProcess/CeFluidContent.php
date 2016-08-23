<?php
namespace Pixelant\Aloha\Hook\RequestPreProcess;

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Georg Ringer <typo3@ringerge.org>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

/**
 * Hook for saving content element "table"
 *
 * @package TYPO3
 * @subpackage tx_aloha
 */
class CeFluidContent implements \Pixelant\Aloha\Hook\RequestPreProcessInterface {

	/**
	 * Preprocess the request
	 *
	 * @param array $request save request
	 * @param boolean $finished
	 * @param \Pixelant\Aloha\Controller\SaveController $parentObject
	 * @return array
	 */
	public function preProcess(array &$request, &$finished, \Pixelant\Aloha\Controller\SaveController &$parentObject) {
		$record = $parentObject->getRecord();

		// when storing fluidcontent flexform fields, field is set by pi_flexform-flexformfieldname.
		list($field, $flexformField) = explode('-', $parentObject->getField(), 2);

		// only allowed for element "fluidcontent"
		if ($parentObject->getTable() === 'tt_content'
			&& $field == 'pi_flexform'
			&& $record['CType'] === 'fluidcontent_content'
		) {

			$parentObject->setField($field);

			$xml = new \SimpleXMLElement($record['pi_flexform']);

			// @TODO: Maybe give possibility for fields to have html tags
			$fieldAllowedTags = '<sup><sub>';
			foreach ($xml->xpath('//T3FlexForms/data/sheet/language/field[@index = "' . $flexformField . '"]') as $entry) {
				$content = trim($request['content']);
				$content = strip_tags(urldecode($content), $fieldAllowedTags);

				// Try to remove invalid characters so save won't break xml if there are invalid characters in string
				$content = iconv("UTF-8", "UTF-8//IGNORE", $content);

				$node = dom_import_simplexml($entry->value);
				$node->nodeValue = "";
				$node->appendChild($node->ownerDocument->createCDATASection($content));
			}

			$request['content'] = $xml->saveXml();

		}

		return $request;
	}

}

?>