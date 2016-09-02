<?php
namespace TYPO3\CMS\FrontendEditing\Utility\RequestPreProcess;

use TYPO3\CMS\FrontendEditing\Controller\CrudController;

/**
 * Hook for saving content element "rte with links"
 */
class CeRteLinks implements RequestPreProcessInterface
{

    /**
     * Pre process the request
     *
     * @param array $request save request
     * @param bool $finished
     * @param \TYPO3\CMS\FrontendEditing\Controller\CrudController $parentObject
     * @return array
     */
    public function preProcess(array &$request, &$finished, CrudController &$parentObject)
    {
        $record = $parentObject->getRecord();
        // Only allowed for text and textpic element (at least for now)
        if ($parentObject->getTable() === 'tt_content'
            && $parentObject->getField() === 'bodytext'
            && ($record['CType'] === 'text' || $record['CType'] === 'textpic')
        ) {
            $content = $this->removeUnwantedLinkVars($request['content']);
            // Send links through RteHtmlParser
            $parseHTML = new \TYPO3\CMS\Core\Html\RteHtmlParser();
            $content = $parseHTML->TS_links_rte($content);
            $request['content'] = $content;
        }

        return $request;
    }

    /**
     * Remove unwanted link variables
     *
     * @param string $content
     * @return string
     */
    protected function removeUnwantedLinkVars($content)
    {
        $domDocument = new \DOMDocument();
        $domDocument->loadHTML('<?xml encoding="utf-8" ?>' . $content);
        $anchorCollection = $domDocument->getElementsByTagName('a');

        foreach ($anchorCollection as $anchor) {
            $t3link = $anchor->getAttribute('t3link');
            if ($t3link) {
                $t3link_slashed = addcslashes($t3link, '/?[](){}^$-".*\\');
                $title = $anchor->getAttribute('title');
                $content = preg_replace(
                    '/<a.*?t3link="' . $t3link_slashed . '".*?>(.*?)<\/a>/',
                    '<LINK ' . $t3link . ' " ' . $title . '">$1</LINK>',
                    $content
                );
            }
        }

        return $content;
    }
}
