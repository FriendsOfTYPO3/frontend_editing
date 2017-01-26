<?php

namespace TYPO3\CMS\FrontendEditing\Domain\Repository;

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

/**
 * Content element repository
 *
 * @package TYPO3\CMS\FrontendEditing\Domain\Repository
 */
class ContentRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
	/**
	 * Find all content elements on a page
	 *
	 * @param $pageId
	 * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
	 */
	public function findAllOnPage($pageId) {
		$query = $this->createQuery();

		/** @var $querySettings \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings */
		$querySettings = $this->objectManager->get('TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings');
		$querySettings->setRespectStoragePage(FALSE);

		//@TODO: Decide on ordering to use for content elements
		$query->setQuerySettings($querySettings);

		return $query->matching($query->equals('pid', $pageId))->execute();
	}

}