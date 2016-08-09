<?php

namespace TYPO3\CMS\FrontendEditing\Hooks;

/**
 * Hook to enable additional stdWrap function "aloha"
 *
 * @package TYPO3
 * @subpackage tx_aloha
 */
class EditIcons implements \TYPO3\CMS\Frontend\ContentObject\ContentObjectStdWrapHookInterface
{

    /**
     * Implement a new stdWrap function to get aloha icons
     *
     * @param string $content
     * @param array $configuration
     * @param tslib_cObj $parentObject
     * @return string
     */
    public function stdWrapProcess($content, array $configuration, \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer &$parentObject)
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
     * @param tslib_cObj $parentObject
     * @return string
     */
    public function stdWrapPreProcess($content, array $configuration, \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer &$parentObject)
    {
        return $content;
    }

    /**
     * Only needed to meet the requirements of the interface
     *
     * @param string $content
     * @param array $configuration
     * @param tslib_cObj $parentObject
     * @return string
     */
    public function stdWrapOverride($content, array $configuration, \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer &$parentObject)
    {
        return $content;
    }

    /**
     * Hook for modifying $content after core's stdWrap has processed anything but debug
     *
     * @param string $content
     * @param array $configuration
     * @param tslib_cObj $parentObject
     * @return string
     */
    public function stdWrapPostProcess($content, array $configuration, \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer &$parentObject)
    {
        if ($configuration['frontendEditingPostProcess'] == 1) { //&& Tx_Aloha_Utility_Access::isEnabled()) {
            //$alohaIntegration = t3lib_div::makeInstance('Tx_Aloha_Aloha_Integration');
            //$content = $alohaIntegration->start($content, $configuration['frontendEditingPostProcess.'], $parentObject);
        }
        return $content;
    }
}
