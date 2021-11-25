<?php

declare(strict_types=1);

namespace TYPO3\CMS\FrontendEditing\Middleware;

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
use TYPO3\CMS\FrontendEditing\Utility\CompatibilityUtility;

class FrontendEditing implements \Psr\Http\Server\MiddlewareInterface
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
        if (CompatibilityUtility::typo3VersionIsGreaterThan('11')) {
            /** @var TypoScriptFrontendController $controller */
            $controller = $GLOBALS['TSFE'];
            $response = new Response();
            $controller->content = $handler->handle($request)->getBody()->__toString();
            $response = $controller->applyHttpHeadersToResponse($response);
            $_params = ['parentObject' => &$controller];
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output'] ?? [] as $_funcRef) {
                GeneralUtility::callUserFunction($_funcRef, $_params, $controller);
            }
            $response->getBody()->write($controller->content);
            return $response;
        }

        return $handler->handle($request);
    }
}
