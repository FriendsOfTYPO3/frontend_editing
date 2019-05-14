<?php
declare(strict_types = 1);
namespace TYPO3\CMS\FrontendEditing\RequestPreProcess;

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

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Hook for saving content element "table"
 *
 * Thanks for to Georg Ringer (mail@ringer.it) for the initial work.
 */
class CeTable implements RequestPreProcessInterface
{
    /**
     * Pre process the content
     *
     * @param string $table
     * @param array $record
     * @param string $fieldName
     * @param string $content
     * @param bool $isFinished
     * @return string the modified content
     */
    public function preProcess(
        string $table,
        array $record,
        string &$fieldName,
        string $content,
        bool &$isFinished
    ): string {
        // Only allowed for element "table"
        if ($table  === 'tt_content' && $fieldName === 'bodytext' && $record['CType'] === 'table') {
            $isFinished = true;

            $domDocument = new \DOMDocument();
            $domDocument->loadHTML('<?xml encoding="utf-8" ?>' . $content);

            $xPath = new \DOMXpath($domDocument);

            $trCollection = $xPath->query('//table/*/tr');
            $tmpCollection = [];
            if ($trCollection !== null) {
                foreach ($trCollection as $element) {
                    $singleLine = [];

                    $nodes = $element->childNodes;
                    foreach ($nodes as $node) {
                        $value = trim($node->nodeValue);
                        if (!empty($value)) {
                            $singleLine[] = $value;
                        }
                    }
                    $tmpCollection[] = implode('|', $singleLine);
                }
            }
            $content = implode(LF, $tmpCollection);

            // If record contains flexform data then process that also
            if (isset($record['pi_flexform'])) {
                $captionPath = $xPath->query('//table//caption[1]');
                $captionValue = '';
                foreach ($captionPath as $c) {
                    $captionValue = trim($c->nodeValue);
                }

                $doc = new \DOMDOcument;
                $doc->loadxml($record['pi_flexform']);

                $replacement = $doc->createDocumentFragment();
                $replacement->appendXML('<value index="vDEF">' . $captionValue . '</value>');

                $xpath = new \DOMXpath($doc);

                $oldNode = $xpath->query(
                    '//field[@index=\'acctables_caption\']//value'
                )->item(0);
                $oldNode->parentNode->replaceChild($replacement, $oldNode);
                $newPiFlexform = '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>' .
                    $doc->saveXml($doc->documentElement);

                $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
                /** @var QueryBuilder $queryBuilder */
                $queryBuilder = $connectionPool->getQueryBuilderForTable('tt_content');
                $queryBuilder
                    ->getRestrictions()
                    ->removeAll();
                $queryBuilder
                    ->update('tt_content')
                    ->where($queryBuilder->expr()->eq('uid', $record['uid']))
                    ->set('pi_flexform', $newPiFlexform)
                    ->execute();
            }
        }

        return $content;
    }
}
