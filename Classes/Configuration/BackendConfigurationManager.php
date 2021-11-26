<?php

declare(strict_types=1);

namespace TYPO3\CMS\FrontendEditing\Configuration;

class BackendConfigurationManager extends \TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager
{
    /**
     * @inheritdoc
     */
    protected function getCurrentPageIdFromGetPostData(): int
    {
        // XHR requests have page ID in "page"
        return (int)\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('page') ?? parent::getCurrentPageIdFromGetPostData();
    }

    protected function getControllerConfiguration($extensionName, $pluginName): array
    {
        return $this->getSwitchableControllerActions($extensionName, $pluginName);
    }
}
