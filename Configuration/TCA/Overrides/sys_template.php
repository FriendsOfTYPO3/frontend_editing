<?php
defined('TYPO3_MODE') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'frontend_editing',
    'Configuration/TypoScript',
    'Frontend Editing'
);
