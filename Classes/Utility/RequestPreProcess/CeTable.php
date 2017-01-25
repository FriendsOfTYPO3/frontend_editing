<?php
namespace TYPO3\CMS\FrontendEditing\Utility\RequestPreProcess;

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

use TYPO3\CMS\FrontendEditing\Controller\CrudController;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

/**
 * Hook for saving content element "table"
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class CeTable implements RequestPreProcessInterface
{

    /**
     * Pre process the request
     *
     * @param array $body save body
     * @param boolean $finished
     * @param \TYPO3\CMS\FrontendEditing\Controller\CrudController $parentObject
     * @return array
     */
    public function preProcess(array &$body, &$finished, CrudController &$parentObject)
    {
        $record = $parentObject->getRecord();

        // Only allowed for element "table"
        if ($parentObject->getTable() === 'tt_content'
            && $parentObject->getField() === 'bodytext'
            && $record['CType'] === 'table'
        ) {
            $finished = true;

            $domDocument = new \DOMDocument();
            $domDocument->loadHTML('<?xml encoding="utf-8" ?>' . $body['content']);

            $xPath = new \DOMXpath($domDocument);

            $trCollection = $xPath->query('//table/*/tr');
            $tmpCollection = array();
            if (!is_null($trCollection)) {
                foreach ($trCollection as $element) {
                    $singleLine = array();

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
            $body['content'] = implode(LF, $tmpCollection);

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

                $oldNode = $xpath->query('//field[@index=\'acctables_caption\']//value')->item(0);
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

        return $body;
    }
}
