<?php
namespace Pixelant\Aloha\Hook\RequestPreProcess;

/* **************************************************************
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
 * Hook for cleaning content
 *
 * @package TYPO3
 * @subpackage tx_aloha
 */
class Plaintext implements \Pixelant\Aloha\Hook\RequestPreProcessInterface {

	/**
	 * Preprocess the request
	 *
	 * @param array $request save request
	 * @param boolean $finished
	 * @param \Pixelant\Aloha\Controller\SaveController $parentObject
	 * @return array
	 */
	public function preProcess(array &$request, &$finished, \Pixelant\Aloha\Controller\SaveController &$parentObject) {
		// only allowed for "special" field "bodytext-plaintext"
		if ($parentObject->getTable() === 'tt_content' && $parentObject->getField() == 'bodytext-plaintext') {

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
	private function modifyContent($content) {

		// @TODO: Maybe give possibility for fields to have html tags
		$fieldAllowedTags = '';

		$content = trim($content);
		$content = strip_tags(urldecode(html_entity_decode($content)), $fieldAllowedTags);

		return $content;
	}
}

?>