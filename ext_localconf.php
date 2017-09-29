<?php
defined('TYPO3_MODE') or die();

// Extend the <core:contentEditable> viewhelper by the one from EXT:frontend_editing
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['core'][] = 'TYPO3\\CMS\\FrontendEditing\\ViewHelpers';

// Disable cHash check when browsing the frontend in frontend editing
if (TYPO3\CMS\Core\Utility\GeneralUtility::_GET('frontend_editing')) {
    $GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFoundOnCHashError'] = false;

    // Add userTsConfig for displaying certain options
    // when browsing as 'frontend_editing'
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig('
        # Activate admin panel
        admPanel {
            enable.edit = 1
            enable.preview = 1
            enable.cache = 0
            override.edit = 1
            override.edit.displayFieldIcons = 1
            override.edit.displayIcons = 0
            override.preview = 1
            override.preview.simulateDate = 0
            override.preview.simulateUserGroup = 0
            override.preview.showHiddenPages = 1
            override.preview.showHiddenRecords = 1
            hide = 1
        }
    ');
}

/**
 * Hooks
 */
// Register the edit panel view
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/classes/class.frontendedit.php']['edit'] =
    \TYPO3\CMS\FrontendEditing\EditingPanel\FrontendEditingPanel::class;

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

// Hook to initialize BE user
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/index_ts.php']['postBeUser']['frontend_editing'] =
    \TYPO3\CMS\FrontendEditing\Hook\PostInitializationBEUserHook::class . '->initializeBackendUser';

// Hook for the rendering for TCA save buttons
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Backend\Template\Components\Buttons\SplitButton::class] = [
    'className' => \TYPO3\CMS\FrontendEditing\Xclass\TcaButtons::class
];

/**
 * Pre processors
 */
// Save core content element "Bullets"
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['FrontendEditing']['requestPreProcess']['frontend_editing-cobjbullets'] =
    \TYPO3\CMS\FrontendEditing\RequestPreProcess\CeBullets::class;

// Save core content element "Table"
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['FrontendEditing']['requestPreProcess']['frontend_editing-cobjtable'] =
    \TYPO3\CMS\FrontendEditing\RequestPreProcess\CeTable::class;

// Save headers, field header, will also affect field header_layout
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['FrontendEditing']['requestPreProcess']['frontend_editing-CeHeader'] =
    \TYPO3\CMS\FrontendEditing\RequestPreProcess\CeHeader::class;

// Add UserTsConfig settings
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig(
    '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:frontend_editing/Configuration/UserTsConfig/userTsConfig.ts">'
);

// Register BE user avatar provider on FE
if (TYPO3_MODE === 'FE' &&
    \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_branch) <
    \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger('9.0')
) {
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['backend']['avatarProviders']['frontendEditingAvatarProvider'] = [
        'provider' => \TYPO3\CMS\FrontendEditing\Provider\FrontendEditingAvatarProvider::class
    ];
}

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
