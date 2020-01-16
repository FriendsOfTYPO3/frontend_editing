<?php
defined('TYPO3_MODE') or die();

// Add BE User setting
$GLOBALS['TYPO3_USER_SETTINGS']['columns']['frontend_editing'] = [
    'label' => 'LLL:EXT:frontend_editing/Resources/Private/Language/locallang.xlf:settings.field.frontend_editing',
    'type' => 'check',
    'default' => 0
];

// Add overlay option User setting
$GLOBALS['TYPO3_USER_SETTINGS']['columns']['frontend_editing_overlay'] = [
    'label' => 'LLL:EXT:frontend_editing/Resources/Private/Language/' .
        'locallang.xlf:settings.field.frontend_editing_overlay',
    'type' => 'check',
    'default' => 0
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToUserSettings(
    'frontend_editing, frontend_editing_overlay',
    'after:edit_RTE'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'frontend_editing',
    'Configuration/TypoScript/FluidStyledContent9',
    'Editable Fluid Styled Content v9'
);
