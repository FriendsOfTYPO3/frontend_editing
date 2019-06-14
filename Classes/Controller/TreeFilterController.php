<?php
declare(strict_types = 1);
namespace TYPO3\CMS\FrontendEditing\Controller;

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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Tree\Pagetree\DataProvider;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Used to filter pages in tree on FE in left bar
 *
 * Class TreeFilterController
 */
class TreeFilterController
{
    /**
     * Process ajax request for filtering
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function filterAction(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $page = (int)$request->getQueryParams()['page'];
        $mounts = array_map('intval', $this->getBeUser()->returnWebmounts());

        if ($this->getBeUser()->isAdmin() || in_array($page, $mounts, true)) {
            $searchWord = $request->getParsedBody()['searchWord'];
            $treeData = [
                'name' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'],
                'icon' => '/typo3/sysext/core/Resources/Public/Icons/T3Icons/apps/apps-pagetree-root.svg',
                'children' => []
            ];

            // Set temporary mounts to search only in one site root
            $this->getBeUser()->uc['pageTree_temporaryMountPoint'] = $page;

            /** @var $dataProvider DataProvider */
            $dataProvider = GeneralUtility::makeInstance(DataProvider::class);

            $nodeCollection = $dataProvider->getTreeMounts($searchWord);

            $this->buildTreeData($treeData['children'], $nodeCollection->toArray());
            $data = [
                'success' => true,
                'treeData' => $treeData
            ];
        } else {
            $data = [
                'success' => false
            ];
        }

        $response->getBody()->write(json_encode($data));

        return $response;
    }

    /**
     * Build tree for D3 library
     *
     * @param array $list
     * @param array $nodesCollection
     */
    protected function buildTreeData(array &$list, array $nodesCollection)
    {
        foreach ($nodesCollection as $node) {
            $treeItem = [
                'uid' => $node['realId'],
                'name' => $node['editableText'],
                'link' => '/index.php?id=' . $node['realId'],
                'icon' => $this->getIcon($node['spriteIconCode']),
                'isActive' => false,
                'expanded' => $node['expanded'],
                'children' => []
            ];

            if (!empty($node['children'])) {
                $this->buildTreeData($treeItem['children'], $node['children']);
            }

            $list[] = $treeItem;
        }
    }

    /**
     * Use a HTML parser DOMDocument to get icon path
     *
     * @param string $html
     * @return string
     */
    protected function getIcon(string $html): string
    {
        $doc = new \DOMDocument();
        $doc->loadHTML($html);
        $xpath = new \DOMXPath($doc);
        return $xpath->evaluate('string(//img/@src)');
    }

    /**
     * @return BackendUserAuthentication
     */
    protected function getBeUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
