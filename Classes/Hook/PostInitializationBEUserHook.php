<?php

namespace TYPO3\CMS\FrontendEditing\Hook;

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
use TYPO3\CMS\Backend\FrontendBackendUserAuthentication;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Hook is called in TypoScriptFrontendController after BE user initialization
 * Used for BE user initialization if FEEDIT_BE_SESSION_KEY was passed
 */
class PostInitializationBEUserHook implements SingletonInterface
{
    /**
     * Change to true if tried to initialize session and fail,
     * so we don't get loop
     *
     * @var bool
     */
    private $attempt = false;

    /**
     * Initialize BE user if session key is valid
     */
    public function initializeBackendUser(array $params, TypoScriptFrontendController $parentOject)
    {
        if ($this->attempt === false
            && $params['BE_USER'] === null
            && GeneralUtility::_GP('FEEDIT_BE_SESSION_KEY')
        ) {
            $this->attempt = true;
            $sessionParts = GeneralUtility::trimExplode('-', GeneralUtility::_GP('FEEDIT_BE_SESSION_KEY'));
            $secret = md5(($sessionParts[0] . '/' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']));

            if ($secret === (string)$sessionParts[1]) {
                $_COOKIE[BackendUserAuthentication::getCookieName()] = $sessionParts[0];
                if (isset($_SERVER['HTTP_COOKIE'])) {
                    $_SERVER['HTTP_COOKIE'] .= ';' . BackendUserAuthentication::getCookieName() .
                        '=' . $sessionParts[0];
                }

                // From parent method, we need to make sure cookie is set
                // New backend user object
                $params['BE_USER'] = GeneralUtility::makeInstance(FrontendBackendUserAuthentication::class);
                $params['BE_USER']->forceSetCookie = true;
                $params['BE_USER']->dontSetCookie = false;
                $params['BE_USER']->start();

                $url = sprintf(
                    '%s://%s/',
                    !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http',
                    GeneralUtility::getIndpEnv('HTTP_HOST')
                );

                // Reload page, because BE user need to be initialized very early
                HttpUtility::redirect($url, HttpUtility::HTTP_STATUS_307);
            }
        }
    }
}
