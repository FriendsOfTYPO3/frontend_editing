<?php

declare(strict_types=1);

namespace TYPO3\CMS\FrontendEditing\Backend\Form\FormDataProvider;

use TYPO3\CMS\Backend\Form\FormDataProviderInterface;

/**
 * Frontend editing-specific resolving of databaseRow field content for type=text, especially rich text configuration.
 * This class contains a lot of copy and paste from TYPO3\CMS\Backend\Form\FormDataProvider\TcaText
 */
class TcaFrontendRichtextConfiguration implements FormDataProviderInterface
{
    /**
     * Handle text field configuration for frontend editing, especially rich text
     *
     * @param array $result Given result array
     * @return array Modified result array
     */
    public function addData(array $result)
    {
        foreach ($result['processedTca']['columns'] as $fieldName => $fieldConfig) {
            if (
                empty($fieldConfig['config']['type'])
                || $fieldConfig['config']['type'] !== 'text'
                || !isset($fieldConfig['config']['enableFrontendRichtext'])
            ) {
                continue;
            }

            $fieldConfig['config']['enableRichtext'] = (bool)$fieldConfig['config']['enableFrontendRichtext'];

            $fieldConfig['config']['richtextConfiguration'] =
                $fieldConfig['config']['frontendRichtextConfiguration'];

            unset($fieldConfig['config']['frontendRichtextConfiguration']);

            $result['processedTca']['columns'][$fieldName] = $fieldConfig;
        }

        return $result;
    }
}
