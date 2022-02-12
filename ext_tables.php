<?php

defined('TYPO3_MODE') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
    'web',
    'FrontendEditing',
    'after:layout',
    null,
    [
        'routeTarget' => \TYPO3\CMS\FrontendEditing\Controller\FrontendEditingModuleController::class . '::showAction',
        'access' => 'user,group',
        'name' => 'web_FrontendEditing',
        'icon' => 'EXT:frontend_editing/Resources/Public/Icons/ext_icon.png',
        'labels' => 'LLL:EXT:frontend_editing/Resources/Private/Language/locallang_mod.xlf',
    ]
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'frontend_editing',
    'Configuration/TypoScript/FluidStyledContent9',
    'Editable Fluid Styled Content v9'
);
