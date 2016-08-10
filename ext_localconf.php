<?php
defined('TYPO3_MODE') or die();

// Add additional stdWrap properties
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['stdWrap'][$_EXTKEY] = \TYPO3\CMS\FrontendEditing\Hooks\EditIcons::class;

// Register the edit panel view
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/classes/class.frontendedit.php']['edit'] = \TYPO3\CMS\FrontendEditing\FrontendEditingPanel::class;
