<?php

declare(strict_types=1);

namespace TYPO3\CMS\FrontendEditing\Controller\Event\Handler;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\FrontendEditing\Configuration\BackendConfigurationManager;
use TYPO3\CMS\FrontendEditing\Controller\Event\PrepareFieldUpdateEvent;
use TYPO3\CMS\FrontendEditing\Controller\Event\PrepareFieldUpdateEventHandlerInterface;

/**
 * Process field data using TypoScript.
 */
class TypoScriptPrepareFieldUpdateEventHandler implements PrepareFieldUpdateEventHandlerInterface
{
    /**
     * @var BackendConfigurationManager
     */
    protected $configurationManager;

    /**
     * @param BackendConfigurationManager $configurationManager
     */
    public function __construct(BackendConfigurationManager $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    /**
     * Processes incoming values using stdWrap from TypoScript.
     *
     * Two configuration arrays are used (in order of preference):
     *
     * `config.tx_frontendediting.contentPersistPreProcessing` contains type and field-specific configurations.
     * `contentPersistPreProcessing.<table>.<type>.<field>` accepts any stdWrap configuratoion property.
     * Setting `<type>` to "*" indicates a wildcard catching all types not explicitly set.
     * `<type>` "0" (zero as string) is the default type of any TYPO3 record.
     *
     * `config.tx_frontendediting.contentPersistPreProcessingPatterns` will be used if no configuration matches are
     * found in `contentPersistPreProcessing`.
     * `contentPersistPreProcessingPatterns.<rte-preset>` accepts any stdWrap configuratoion property.
     * `<rte-preset>` is any RTE preset string, as set in a field's TCA configuration `frontendConfiguration` or
     * `frontendRichtextConfiguration`.
     *
     * @param PrepareFieldUpdateEvent $event
     */
    public function __invoke(PrepareFieldUpdateEvent $event): void
    {
        $setup = $this->getTypoScriptSetup()['contentPersistPreProcessing.'] ?? [];

        $typeValue = BackendUtility::getTCAtypeValue($event->getTable(), $event->getRecord());

        if (
            !is_array($setup[$event->getTable() . '.'][$typeValue . '.'])
            && is_array($setup[$event->getTable()]['*.'])
        ) {
            $typeValue = '*';
        }

        if (is_array($setup[$event->getTable() . '.'][$typeValue . '.'])) {
            $stdWrapConfiguration = $setup[$event->getTable() . '.'][$typeValue . '.'][$event->getField() . '.'];
        }

        if (!is_array($stdWrapConfiguration)) {
            $tableTca = $GLOBALS['TCA'][$event->getTable()];
            $fieldConfig = $tableTca['columns'][$event->getField()]['config'];

            ArrayUtility::mergeRecursiveWithOverrule(
                $fieldConfig,
                $tableTca['types'][$typeValue]['columnsOverrides'][$event->getField()]['config'] ?? []
            );

            $rteEnabled = (bool)$fieldConfig['enableFrontendRichtext'] ?? $fieldConfig['enableRichtext'];
            $rtePreset = $fieldConfig['frontendRichtextConfiguration'] ?? $fieldConfig['richtextConfiguration'];

            if (!$rteEnabled || !$rtePreset) {
                return;
            }

            $stdWrapConfiguration =
                $this->getTypoScriptSetup()['contentPersistPreProcessingPatterns.'][$rtePreset . '.'];

            if (!is_array($stdWrapConfiguration)) {
                return;
            }
        }

        /** @var ContentObjectRenderer $contentObjectRenderer */
        $contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class, $parentObject);

        $event->setContent(
            $contentObjectRenderer->stdWrap(
                $event->getContent(),
                $stdWrapConfiguration
            )
        );
    }

    /**
     * Return the TypoScript configuration in config.tx_frontendediting
     *
     * @return array
     */
    protected function getTypoScriptSetup(): array
    {
        return $this->configurationManager->getTypoScriptSetup()['config.']['tx_frontendediting.'] ?? [];
    }
}
