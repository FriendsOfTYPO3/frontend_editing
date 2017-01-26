<?php

namespace TYPO3\CMS\FrontendEditing\Domain\Repository;


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