<?php
namespace TYPO3\CMS\FrontendEditing\Utility\RequestPreProcess;

interface RequestPreProcessInterface {

	/**
	 * Preprocess the request
	 *
	 * @param array $request save request
	 * @param boolean $finished
	 * @param \TYPO3\CMS\FrontendEditing\Controller\SaveController $parentObject
	 * @return array
	 */
	public function preProcess(array &$request, &$finished, \TYPO3\CMS\FrontendEditing\Controller\SaveController &$parentObject);

}

?>
