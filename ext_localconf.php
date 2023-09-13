<?php

defined('TYPO3') or die();

// TODO: cleanup and only if FEediting enabled?

$boot = static function (): void {
    // Extend the <core:contentEditable> viewhelper by the one from EXT:frontend_editing
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['core'][] = 'TYPO3\\CMS\\FrontendEditing\\ViewHelpers';

    // Exclude Frontend Editing parameters to prevent errors when $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['enforceValidation'] = true
    $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'fe_edit';
    $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'show_hidden_items';

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

    // Modify every link to save unparsed links but allow editors to still browse the website
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['typoLink_PostProc']['frontend_editing'] =
        \TYPO3\CMS\FrontendEditing\Hook\TypoLinkPostProcHook::class . '->modifyFinalLinkTag';

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
};

$boot();
unset($boot);
