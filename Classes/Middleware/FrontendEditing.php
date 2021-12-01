<?php

declare(strict_types=1);

namespace TYPO3\CMS\FrontendEditing\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\FrontendEditing\Hook\FrontendEditingInitializationHook;
use TYPO3\CMS\FrontendEditing\Service\AccessService;

class FrontendEditing implements MiddlewareInterface
{
    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $accessService = GeneralUtility::makeInstance(AccessService::class);
        if ($accessService->isEnabled()) {
            /** @var TypoScriptFrontendController $controller */
            $controller = $GLOBALS['TSFE'];
            $response = new Response();
            $controller->content = $handler->handle($request)->getBody()->__toString();
            $response = $controller->applyHttpHeadersToResponse($response);
            GeneralUtility::makeInstance(FrontendEditingInitializationHook::class)->main([], $controller);
            $response->getBody()->write($controller->content);
            return $response;
        }

        return $handler->handle($request);
    }
}
