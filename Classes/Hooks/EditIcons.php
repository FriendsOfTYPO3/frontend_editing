<?php

namespace TYPO3\CMS\FrontendEditing\Hooks;

use TYPO3\CMS\Frontend\ContentObject\ContentObjectStdWrapHookInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Hook to enable additional stdWrap function "aloha"
 *
 * @package TYPO3
 * @subpackage tx_aloha
 */
class EditIcons implements ContentObjectStdWrapHookInterface
{

    /**
     * Implement a new stdWrap function to get aloha icons
     *
     * @param string $content
     * @param array $configuration
     * @param ContentObjectRenderer $parentObject
     * @return string
     */
    public function stdWrapProcess($content, array $configuration, ContentObjectRenderer &$parentObject)
    {
        //var_dump($content);
        //var_dump($configuration);die;
        if ($configuration['frontendEditingProcess'] == 1) { //&& Tx_Aloha_Utility_Access::isEnabled()) {
            $alohaIntegration = new \TYPO3\CMS\FrontendEditing\Ckeditor\Integration();
            $content = $alohaIntegration->start($content, $configuration['frontendEditingProcess.'], $parentObject);
        }

        return $content;
    }

    /**
     * Only needed to meet the requirements of the interface
     *
     * @param string $content
     * @param array $configuration
     * @param ContentObjectRenderer $parentObject
     * @return string
     */
    public function stdWrapPreProcess($content, array $configuration, ContentObjectRenderer &$parentObject)
    {
        return $content;
    }

    /**
     * Only needed to meet the requirements of the interface
     *
     * @param string $content
     * @param array $configuration
     * @param ContentObjectRenderer $parentObject
     * @return string
     */
    public function stdWrapOverride($content, array $configuration, ContentObjectRenderer &$parentObject)
    {
        return $content;
    }

    /**
     * Hook for modifying $content after core's stdWrap has processed anything but debug
     *
     * @param string $content
     * @param array $configuration
     * @param ContentObjectRenderer $parentObject
     * @return string
     */
    public function stdWrapPostProcess($content, array $configuration, ContentObjectRenderer &$parentObject)
    {
        if ($configuration['frontendEditingPostProcess'] == 1) { //&& Tx_Aloha_Utility_Access::isEnabled()) {
            // $alohaIntegration = t3lib_div::makeInstance('Tx_Aloha_Aloha_Integration');
            // $content =
            // $alohaIntegration->start($content, $configuration['frontendEditingPostProcess.'], $parentObject);
        }
        return $content;
    }
}
