<?php

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
 * SEO provider for Clickstorm SEO module: "[clickstorm] SEO" (cs_seo)
 * @package TYPO3\CMS\FrontendEditing\Provider
 */
class CsSeoProvider extends BaseSeoProvider
{
    /**
     * Return an array with the SEO scores for ClickStorm SEO module
     *
     * @return array
     */
    public function getSeoScores(): array
    {
        $scores = [
            'pageScore' => $this->getPageScore()
        ];
        $this->setScores($scores);

        return $this->getScores();
    }
}
