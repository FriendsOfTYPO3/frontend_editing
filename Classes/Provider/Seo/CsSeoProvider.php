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

use Clickstorm\CsSeo\Domain\Model\Evaluation;
use Clickstorm\CsSeo\Domain\Repository\EvaluationRepository;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * SEO provider for Clickstorm SEO module: "[clickstorm] SEO" (cs_seo)
 */
class CsSeoProvider extends BaseSeoProvider
{
    /**
     * Return an array with the SEO scores for ClickStorm SEO module
     *
     * @param int $pageId
     * @return array
     */
    public function getSeoScores(int $pageId): array
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $evalutationRepository =  $objectManager->get(EvaluationRepository::class);

        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

        $typo3VersionNumber = VersionNumberUtility::convertVersionNumberToInteger(
            VersionNumberUtility::getNumericTypo3Version()
        );

        $uriParameters = ['page' => $pageId];

        if ($typo3VersionNumber < 9000000) {
            // @extensionScannerIgnoreLine
            $backendModuleUrl = $uriBuilder->buildUriFromModule(
                'web_CsSeoMod1',
                $uriParameters
            );
        } else {
            $backendModuleUrl = $uriBuilder->buildUriFromRoute(
                'web_CsSeoMod1',
                $uriParameters
            );
        }

        /** @var Evaluation $evaluation */
        $evaluation = $evalutationRepository->findByUidForeignAndTableName(
            $pageId,
            'pages'
        );

        if ($evaluation !== null) {
            $evaluationResults = $evaluation->getResults();
            // Make sure that there is results back
            if (is_array($evaluationResults)) {
                $this->setPageScore(
                    (int)$evaluationResults['Percentage']['count']
                );
            }
        }

        $scores = [
            'pageScore' => $this->getPageScore(),
            'backendModuleUrl' => $backendModuleUrl
        ];
        $this->setScores($scores);

        return $this->getScores();
    }
}
