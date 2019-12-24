<?php
declare(strict_types=1);

namespace TYPO3\CMS\FrontendEditing\Xclass\Controller\ContentElement;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Controller\ContentElement\NewContentElementController as Typo3NewContentElementController;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Extend TYPO3 NewContentElementController
 * @package TYPO3\CMS\FrontendEditing\Xclass\Controller\ContentElement
 */
class NewContentElementController extends Typo3NewContentElementController
{
    /**
     * Initialize
     */
    public function __construct()
    {
        parent::__construct();

        // If id wasn't set, re-init with ID from TSFE in order to generate correct PAGE TS
        // Due to new routing in TYPO3 v9 there is no 'id' in get params on FE
        if (isset($GLOBALS['TYPO3_REQUEST']) && $this->id === 0) {
            $this->init($this->simulateRequestWithGetParams());
        }
    }

    /**
     * Simulate request with id and language params
     *
     * @return ServerRequestInterface
     */
    protected function simulateRequestWithGetParams(): ServerRequestInterface
    {
        try {
            $languageUid = GeneralUtility::makeInstance(Context::class)->getAspect('language')->getId();
        } catch (AspectNotFoundException $exception) {
            $languageUid = 0;
        }

        return $GLOBALS['TYPO3_REQUEST']->withQueryParams([
            'id' => $GLOBALS['TSFE']->id,
            'sys_language_uid' => $languageUid,
        ]);
    }
}
