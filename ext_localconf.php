<?php
defined('TYPO3_MODE') or die();

/**
 * Hooks
 */
// Add additional stdWrap properties
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['stdWrap'][$_EXTKEY] = \TYPO3\CMS\FrontendEditing\Hooks\EditIcons::class;

// Register the edit panel view
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/classes/class.frontendedit.php']['edit'] = \TYPO3\CMS\FrontendEditing\FrontendEditingPanel::class;

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

// Check link params in rte of text and textpic content elements
$GLOBALS['TYPO3_CONF_VARS']['Aloha']['Classes/Save/Save.php']['requestPreProcess'][$_EXTKEY . '-CeRteLinks'] =
    \TYPO3\CMS\FrontendEditing\Utility\RequestPreProcess\CeRteLinks::class;

/**
 * Frontend plugin
 */
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'TYPO3\CMS.' . $_EXTKEY,
    'frontend_editing',
    [
        'Save' => 'save'
    ],
    [
        'Save' => 'save'
    ]
);