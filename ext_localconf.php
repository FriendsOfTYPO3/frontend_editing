<?php
defined('TYPO3_MODE') or die();

// Add additional stdWrap properties
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['stdWrap'][$_EXTKEY] = \TYPO3\CMS\FrontendEditing\Hooks\EditIcons::class;

// Register the edit panel view
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/classes/class.frontendedit.php']['edit'] = \TYPO3\CMS\FrontendEditing\FrontendEditingPanel::class;

// Pre processors
$GLOBALS['TYPO3_CONF_VARS']['Ckeditor']['Classes/Save/Save.php']['requestPreProcess'][$_EXTKEY . '-cleanup'] =
    \TYPO3\CMS\FrontendEditing\Utility\RequestPreProcess\Cleanup::class;
$GLOBALS['TYPO3_CONF_VARS']['Ckeditor']['Classes/Save/Save.php']['requestPreProcess'][$_EXTKEY . '-CeHeader'] =
    \TYPO3\CMS\FrontendEditing\Utility\RequestPreProcess\CeHeader::class;
$GLOBALS['TYPO3_CONF_VARS']['Ckeditor']['Classes/Save/Save.php']['requestPreProcess'][$_EXTKEY . '-cobjbullets'] =
    \TYPO3\CMS\FrontendEditing\Utility\RequestPreProcess\CeBullets::class;

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