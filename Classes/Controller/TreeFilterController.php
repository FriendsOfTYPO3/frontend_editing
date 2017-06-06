<?php
declare(strict_types=1);
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

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Tree\Pagetree\DataProvider;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Used to filter pages in tree on FE in left bar
 *
 * Class TreeFilterController
 * @package TYPO3\CMS\FrontendEditing\Controller
 */
class TreeFilterController
{
    public function filterAction(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $page = (int)$request->getQueryParams()['page'];
        $mounts = array_map('intval', $this->getBeUser()->returnWebmounts());

        if ($this->getBeUser()->isAdmin() || in_array($page, $mounts, true)) {
            $searchWord = $request->getParsedBody()['searchWord'];

            /** @var $dataProvider DataProvider */
            $dataProvider = GeneralUtility::makeInstance(DataProvider::class);

            $nodeCollection = $dataProvider->getTreeMounts($searchWord);

            $data = [
                'success' => true,
                'nodeCollection' => $nodeCollection->toArray()
            ];

            $response->getBody()->write(json_encode($data));
        }


        return $response;
    }

    /**
     * @return BackendUserAuthentication
     */
    protected function getBeUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
