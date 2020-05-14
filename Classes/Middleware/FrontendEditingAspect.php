<?php
declare(strict_types = 1);
namespace TYPO3\CMS\FrontendEditing\Middleware;

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
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\VisibilityAspect;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\FrontendEditing\Utility\FrontendEditingUtility;

/**
 * This middleware checks if frontend_editing is enabled
 */
class FrontendEditingAspect implements MiddlewareInterface
{
    /**
     * Dispatches the request to the corresponding eID class or eID script
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (FrontendEditingUtility::isEnabled()) {
            $showHiddenItems = GeneralUtility::_GET('show_hidden_items') ?? false;
            $context = GeneralUtility::makeInstance(Context::class);
            $context->setAspect(
                'visibility',
                GeneralUtility::makeInstance(VisibilityAspect::class, true, $showHiddenItems)
            );
        }
        return $handler->handle($request);
    }
}
