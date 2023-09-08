<?php
declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\FrontendEditing\Controller\FrontendEditingModuleController;

defined('TYPO3') or die();

$boot = static function (): void {
    ExtensionManagementUtility::addModule(
        'web',
        'FrontendEditing',
        'after:layout',
        null,
        [
            'routeTarget' => FrontendEditingModuleController::class . '::showAction',
            'access' => 'user,group',
            'name' => 'web_FrontendEditing',
            'icon' => 'EXT:frontend_editing/Resources/Public/Icons/ext_icon.png',
            'labels' => 'LLL:EXT:frontend_editing/Resources/Private/Language/locallang_mod.xlf',
        ]
    );
};

$boot();
unset($boot);
