<?php

declare(strict_types=1);


namespace TYPO3\CMS\FrontendEditing\Middleware;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\FrontendEditing\Hook\FrontendEditingInitializationHook;
use TYPO3\CMS\FrontendEditing\Utility\CompatibilityUtility;

class FrontendEditing implements \Psr\Http\Server\MiddlewareInterface
{
    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (CompatibilityUtility::typo3VersionIsGreaterThan('11')) {
            GeneralUtility::makeInstance(FrontendEditingInitializationHook::class)
                ->main([], $GLOBALS['TSFE']);
        }
    }
}
