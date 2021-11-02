<?php

defined('TYPO3_MODE') or die();

// Extend the <core:contentEditable> viewhelper by the one from EXT:frontend_editing
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['core'][] = 'TYPO3\\CMS\\FrontendEditing\\ViewHelpers';

// Exclude the frontend editing from the cHash calculations
$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'frontend_editing';

// Copy configuration array so we can have our own.
$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['frontendTcaDatabaseRecord'] =
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'];

// Add processor for frontend-related RTE configuration
$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['frontendTcaDatabaseRecord'][
    \TYPO3\CMS\FrontendEditing\Backend\Form\FormDataProvider\TcaFrontendRichtextConfiguration::class
] = [
    'before' => [
        \TYPO3\CMS\Backend\Form\FormDataProvider\TcaText::class
    ]
];

// Add RTE presets for frontend use
$GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['bronly'] = 'EXT:frontend_editing/Configuration/RTE/BrOnly.yaml';
$GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['listonly'] = 'EXT:frontend_editing/Configuration/RTE/ListOnly.yaml';

/**
 * Hooks
 */
// Register the edit panel view
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/classes/class.frontendedit.php']['edit'] =
    \TYPO3\CMS\FrontendEditing\EditingPanel\FrontendEditingPanel::class;

// Register the preHeaderRenderHook for backend document template
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/backend.php']['renderPreProcess']['frontend_editing/pagetree-actions'] =
    \TYPO3\CMS\FrontendEditing\Hook\Backend\Template\DocumentTemplate::class . '->mainBackendModule';

// Hook to unset page setup before render the toolbars to speed up the render
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['configArrayPostProc']['frontend_editing'] =
    \TYPO3\CMS\FrontendEditing\Hook\FrontendEditingInitializationHook::class . '->unsetPageSetup';

// Hook to render toolbars
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output']['frontend_editing'] =
    \TYPO3\CMS\FrontendEditing\Hook\FrontendEditingInitializationHook::class . '->main';

// Hook content object render. Check if column is empty
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass']['CONTENT'] = [
    'CONTENT',
    \TYPO3\CMS\FrontendEditing\Hook\ContentObjectRendererHook::class
];

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClassDefault'] = [
    'CONTENT',
    \TYPO3\CMS\FrontendEditing\Hook\ContentObjectRendererHook::class
];

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typolinkProcessing']['typolinkModifyParameterForPageLinks'][] =
    \TYPO3\CMS\FrontendEditing\Hook\ModifyTypoLinkConfig::class;

/**
 * Pre processors
 */
\TYPO3\CMS\FrontendEditing\Utility\CompatibilityUtility::registerEventHandlerAsSignalSlot(
    \TYPO3\CMS\FrontendEditing\Controller\Event\PrepareFieldUpdateEvent::class,
    \TYPO3\CMS\FrontendEditing\Controller\Event\Handler\TypoScriptPrepareFieldUpdateEventHandler::class
);

/**
 * Custom icons
 */
$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);

$iconRegistry->registerIcon(
    'ext-news-wizard-icon',
    \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
    ['source' => 'EXT:news/Resources/Public/Icons/plugin_wizard.svg']
);
$iconRegistry->registerIcon(
    'grid-elements-2ColumnGrid',
    \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
    ['source' => 'EXT:theme_t3kit/Resources/Public/Icons/GridElements/2-column-grid.svg']
);
$iconRegistry->registerIcon(
    'grid-elements-3ColumnGrid',
    \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
    ['source' => 'EXT:theme_t3kit/Resources/Public/Icons/GridElements/3-column-grid.svg']
);
$iconRegistry->registerIcon(
    'grid-elements-4ColumnGrid',
    \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
    ['source' => 'EXT:theme_t3kit/Resources/Public/Icons/GridElements/4-column-grid.svg']
);
$iconRegistry->registerIcon(
    'grid-elements-adv1ColumnGrid',
    \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
    ['source' => 'EXT:theme_t3kit/Resources/Public/Icons/GridElements/adv1-column-grid.svg']
);
$iconRegistry->registerIcon(
    'grid-elements-adv2ColumnGrid',
    \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
    ['source' => 'EXT:theme_t3kit/Resources/Public/Icons/GridElements/adv2-column-grid.svg']
);
$iconRegistry->registerIcon(
    'grid-elements-adv3ColumnGrid',
    \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
    ['source' => 'EXT:theme_t3kit/Resources/Public/Icons/GridElements/adv3-column-grid.svg']
);
$iconRegistry->registerIcon(
    'grid-elements-adv4ColumnGrid',
    \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
    ['source' => 'EXT:theme_t3kit/Resources/Public/Icons/GridElements/adv4-column-grid.svg']
);
$iconRegistry->registerIcon(
    'grid-elements-collapsibleGroup',
    \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
    ['source' => 'EXT:theme_t3kit/Resources/Public/Icons/GridElements/collapsibleGroup.svg']
);
$iconRegistry->registerIcon(
    'grid-elements-collapsible',
    \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
    ['source' => 'EXT:theme_t3kit/Resources/Public/Icons/GridElements/collapsible.svg']
);
$iconRegistry->registerIcon(
    'grid-elements-parallax',
    \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
    ['source' => 'EXT:theme_t3kit/Resources/Public/Icons/GridElements/parallax.svg']
);
$iconRegistry->registerIcon(
    'grid-elements-simpleAccordion',
    \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
    ['source' => 'EXT:theme_t3kit/Resources/Public/Icons/GridElements/simpleAccordion.svg']
);
$iconRegistry->registerIcon(
    'grid-elements-sliderContainer',
    \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
    ['source' => 'EXT:theme_t3kit/Resources/Public/Icons/GridElements/sliderContainer.svg']
);
$iconRegistry->registerIcon(
    'grid-elements-tabGroup',
    \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
    ['source' => 'EXT:theme_t3kit/Resources/Public/Icons/GridElements/tabGroup.svg']
);
$iconRegistry->registerIcon(
    'grid-elements-tab',
    \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
    ['source' => 'EXT:theme_t3kit/Resources/Public/Icons/GridElements/tab.svg']
);
