<?php
declare(strict_types = 1);

namespace TYPO3\CMS\FrontendEditing\Provider\Seo;

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
 * The Base SEO provider for ranking.
 */
class BaseSeoProvider
{

    /**
     * @var array
     */
    protected $scores;

    /**
     * @var int
     */
    protected $pageScore = 0;

    /**
     * @return array
     */
    public function getScores(): array
    {
        return $this->scores;
    }

    /**
     * @param array $scores
     */
    public function setScores(array $scores)
    {
        $this->scores = $scores;
    }

    /**
     * @return int
     */
    public function getPageScore(): int
    {
        return $this->pageScore;
    }

    /**
     * @param int $pageScore
     */
    public function setPageScore($pageScore)
    {
        $this->pageScore = $pageScore;
    }

    /**
     * Return an array with the SEO scores
     *
     * @param int $pageId
     * @return array
     */
    public function getSeoScores(int $pageId): array
    {
        return $this->getScores();
    }
}
