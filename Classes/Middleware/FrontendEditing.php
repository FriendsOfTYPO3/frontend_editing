<?php

declare(strict_types=1);

namespace TYPO3\CMS\FrontendEditing\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\ResponseFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\FrontendEditing\Hook\FrontendEditingInitializationHook;

class FrontendEditing implements MiddlewareInterface
{
    /** @var ResponseFactory */
    private $responseFactory;

    /** @var RequestFactory */
    private $requestFactory;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        RequestFactoryInterface $requestFactory
    ) {
        $this->responseFactory = $responseFactory;
        $this->requestFactory = $requestFactory;
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var TypoScriptFrontendController $controller */
        $controller = $GLOBALS['TSFE'];
        $response = new Response();
        $controller->content = $handler->handle($request)->getBody()->__toString();
        $response = $controller->applyHttpHeadersToResponse($response);
        GeneralUtility::makeInstance(FrontendEditingInitializationHook::class)->main([], $controller);
        $response->getBody()->write($controller->content);
        return $response;

        return $handler->handle($request);
    }
}
