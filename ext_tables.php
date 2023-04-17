<?php

defined('TYPO3') or die();

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
