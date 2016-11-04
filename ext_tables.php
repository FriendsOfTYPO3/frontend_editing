<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    $_EXTKEY,
    'Configuration/TypoScript',
    'TYPO3 Frontend Editing'
);

// Add BE User setting
$GLOBALS['TYPO3_USER_SETTINGS']['columns']['tx_frontend_editing_enable'] = [
    'label' => 'Enable frontend editing',
    'type' => 'check',
    'default' => 0,
];

// Add overlay option User setting
$GLOBALS['TYPO3_USER_SETTINGS']['columns']['tx_frontend_editing_overlay'] = [
    'label' => 'Enable overlay rightbar',
    'type' => 'check',
    'default' => 0,
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToUserSettings(
    'tx_frontend_editing_enable, tx_frontend_editing_overlay, ',
    'after:edit_RTE'
);
