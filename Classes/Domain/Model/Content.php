<?php

namespace TYPO3\CMS\FrontendEditing\Domain\Model;


class Content extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

	/**
	 * Creation date timestamp
	 *
	 * @var int
	 */
	protected $crdate;

	/**
	 * Last edited date timestamp
	 *
	 * @var int
	 */
	protected $tstamp;

	/**
	 * header
	 *
	 * @var string
	 */
	protected $header = '';

	/**
	 * sorting
	 *
	 * @var string
	 */
	protected $sorting = '';

	/**
	 * contentType
	 *
	 * @var string
	 */
	protected $contentType = '';

	/**
	 * Get the creation date
	 *
	 * @return int
	 */
	public function getCrdate()
	{
		return $this->crdate;
	}

	/**
	 * Get the timestamp
	 *
	 * @return int
	 */
	public function getTstamp()
	{
		return $this->tstamp;
	}


	/**
	 * Returns the header
	 *
	 * @return string $header
	 */
	public function getHeader() {
		return $this->header;
	}

	/**
	 * Sets the header
	 *
	 * @param string $header
	 * @return void
	 */
	public function setHeader($header) {
		$this->header = $header;
	}

	/**
	 * Returns the sorting
	 *
	 * @return string $sorting
	 */
	public function getSorting() {
		return $this->sorting;
	}

	/**
	 * Sets the sorting
	 *
	 * @param string $sorting
	 * @return void
	 */
	public function setSorting($sorting) {
		$this->sorting = $sorting;
	}

	/**
	 * Returns the contentType
	 *
	 * @return string $contentType
	 */
	public function getContentType() {
		return $this->contentType;
	}

	/**
	 * Sets the contentType
	 *
	 * @param string $contentType
	 * @return void
	 */
	public function setContentType($contentType) {
		$this->contentType = $contentType;
	}

}