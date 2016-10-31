<?php
defined('TYPO3_MODE') or die();

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Hooks
 */
// Register the edit panel view
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/classes/class.frontendedit.php']['edit'] =
    \TYPO3\CMS\FrontendEditing\EditingPanel\FrontendEditingPanel::class;

// Hook to render toolbars
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output'][$_EXTKEY] =
    'TYPO3\\CMS\\FrontendEditing\\Hook\\ContentPostProc->main';

/**
 * Pre processors
 */
$GLOBALS['TYPO3_CONF_VARS']['Ckeditor']['Classes/Save/Save.php']['requestPreProcess'][$_EXTKEY . '-cleanup'] =
    \TYPO3\CMS\FrontendEditing\Utility\RequestPreProcess\Cleanup::class;

// Save core content element "Bullets"
$GLOBALS['TYPO3_CONF_VARS']['Ckeditor']['Classes/Save/Save.php']['requestPreProcess'][$_EXTKEY . '-cobjbullets'] =
    \TYPO3\CMS\FrontendEditing\Utility\RequestPreProcess\CeBullets::class;

// Save core content element "Table"
$GLOBALS['TYPO3_CONF_VARS']['Ckeditor']['Classes/Save/Save.php']['requestPreProcess'][$_EXTKEY . '-cobjtable'] =
    \TYPO3\CMS\FrontendEditing\Utility\RequestPreProcess\CeTable::class;

// Save core content special element "Plaintext"
// Activated by setting field to to bodytext-plaintext, which in hook will be restored to bodytext
$GLOBALS['TYPO3_CONF_VARS']['Ckeditor']['Classes/Save/Save.php']['requestPreProcess'][$_EXTKEY . '-Plaintext'] =
    \TYPO3\CMS\FrontendEditing\Utility\RequestPreProcess\Plaintext::class;

// Save fluidcontent , field is targeted by pi_flexform-flexformfieldname
$GLOBALS['TYPO3_CONF_VARS']['Ckeditor']['Classes/Save/Save.php']['requestPreProcess'][$_EXTKEY . '-CeFluidContent'] =
    \TYPO3\CMS\FrontendEditing\Utility\RequestPreProcess\CeFluidContent::class;

// Save headers, field header, will also affect field header_layout
$GLOBALS['TYPO3_CONF_VARS']['Ckeditor']['Classes/Save/Save.php']['requestPreProcess'][$_EXTKEY . '-CeHeader'] =
    \TYPO3\CMS\FrontendEditing\Utility\RequestPreProcess\CeHeader::class;

/**
 * Typoscript settings
 */
// Add UserTsConfig settings
ExtensionManagementUtility::addUserTSConfig('<INCLUDE_TYPOSCRIPT: source="' .
    'FILE:EXT:frontend_editing/Configuration/TypoScript/UserTsConfig/userTsConfig.ts">');

// If rtehtmlarea is loaded and be user is logged in then reset the pageTSConfig
if (ExtensionManagementUtility::isLoaded('rtehtmlarea')) {
    ExtensionManagementUtility::addPageTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="' .
            'FILE:EXT:frontend_editing/Configuration/TypoScript/PageTsConfig/Proc/pageTsConfig.ts">'
    );
}

/**
 * Frontend plugins
 */
// Content crud actions
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'TYPO3\CMS.' . $_EXTKEY,
    'frontend_editing',
    [
        'Crud' => 'update, delete, moveContent'
    ],
    [
        'Crud' => 'update, delete, moveContent'
    ]
);

// Page tree crud actions
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'TYPO3\CMS.' . $_EXTKEY,
    'frontend_editing_page_tree',
    [
        'PageTreeCrud' => 'update, save, read, delete'
    ],
    [
        'PageTreeCrud' => 'update, save, read, delete'
    ]
);
