<?php
declare(strict_types = 1);
namespace TYPO3\CMS\FrontendEditing\EditingPanel;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\FrontendEditing\Service\AccessService;
use TYPO3\CMS\FrontendEditing\Service\ContentEditableWrapperService;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;

/**
 * View class for the edit panels in frontend editing
 */
class FrontendEditingPanel
{
    /**
     * Property for accessing TypoScriptFrontendController centrally
     *
     * @var TypoScriptFrontendController
     */
    protected $frontendController;

    /**
     * Keep list of columns (colPos) which has content
     * so we know if element is first for this column or no
     *
     * @var string
     */
    public static $columnsWithContentList = '';

    /**
     * Constructor for the edit panel
     */
    public function __construct()
    {
        $this->frontendController = $GLOBALS['TSFE'];
    }

    /**
     * Needs to be implemented via the API but not in use
     *
     * @param string $content
     * @param array $conf
     * @param string $currentRecord
     * @param array $dataArray
     * @param string $table
     * @param array $allowedActions
     * @param string $newUid
     * @param string $fields
     * @return string
     */
    public function editPanel($content, $conf, $currentRecord, $dataArray, $table, $allowedActions, $newUid, $fields)
    {
        return $content;
    }

    /**
     * Adds an edit icon to the content string. The edit icon links to EditDocumentController
     * with proper parameters for editing the table/fields of the context.
     * This implements TYPO3 context sensitive editing facilities.
     * Only backend users will have access (if properly configured as well).
     * See TYPO3\CMS\Core\FrontendEditing\FrontendEditingController
     *
     * @param string $content
     * @param array $params
     * @param array $conf
     * @param array $currentRecord
     * @param array $dataArr
     * @param string $addUrlParamStr
     * @param string $table
     * @param string $editUid
     * @param string $fieldList
     * @return string
     */
    public function editIcons(
        $content,
        $params,
        array $conf,
        $currentRecord,
        array $dataArr,
        $addUrlParamStr,
        $table,
        $editUid,
        $fieldList
    ): string {
        $access = GeneralUtility::makeInstance(AccessService::class);
        if (!$access->isEnabled()) {
            return $content;
        }

        // We need to determine if we are having whole element or just one field for element
        // this only allows to edit all other tables just per field instead of per element
        $isEditableField = false;
        $isWholeElement = false;
        if ((int)$conf['beforeLastTag'] === 1) {
            $isEditableField = true;
        } elseif ($table === 'tt_content' || $conf['hasEditableFields'] === 1) {
            $isWholeElement = true;
        } else {
            // default fallback, for everything else with edit icons, we assume it is separate element and is editable
            $isWholeElement = true;
            $isEditableField = true;
        }

        /** @var ContentEditableWrapperService $wrapperService */
        $wrapperService = GeneralUtility::makeInstance(ContentEditableWrapperService::class);

        $pluginConfiguration = $this->getPluginConfiguration();
        // Check if customRecordEditing is present
        if (isset($pluginConfiguration['customRecordEditing'])
            && is_array($pluginConfiguration['customRecordEditing'])
        ) {
            $pageArguments = $this->frontendController->getPageArguments()->getArguments();
            foreach ($pluginConfiguration['customRecordEditing'] as $key => $customRecordEditing) {
                // Check that params match the custom editing configuration
                if (isset($pageArguments[$key])
                    && $pageArguments[$key]['action'] === $customRecordEditing['actionName']
                ) {
                    if ($dataArr !== null && $dataArr['list_type'] === $customRecordEditing['listTypeName']) {
                        $table = $customRecordEditing['tableName'];
                        $editUid = $pageArguments[$key][$customRecordEditing['recordName']];
                    }
                }
            }
        }

        if ($isEditableField) {
            $fields = GeneralUtility::trimexplode(',', $fieldList);
            $content = $wrapperService->wrapContentToBeEditable(
                $table,
                trim($fields[0]),
                (int)$editUid,
                $content
            );
        }

        if ($isWholeElement) {
            // Special content is about to be shown, so the cache must be disabled.
            $this->frontendController->set_no_cache('Display frontend edit icons', true);

            // wrap content with controls
            $content = $wrapperService->wrapContent(
                $table,
                (int)$editUid,
                $dataArr,
                $content
            );

            $isWrappedWithDropzone = false;
            $frontendEditingConfiguration = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['frontend_editing'];
            if (is_array($frontendEditingConfiguration['FrontendEditingPanel']['dropzoneModifiers'])) {
                foreach ($frontendEditingConfiguration['FrontendEditingPanel']['dropzoneModifiers'] as $classData) {
                    $hookObject = GeneralUtility::makeInstance($classData);
                    if (!$hookObject instanceof FrontendEditingDropzoneModifier) {
                        throw new \UnexpectedValueException(
                            $classData . ' must implement interface ' . FrontendEditingDropzoneModifier::class,
                            1493980015
                        );
                    }
                    $isWrappedWithDropzone = $hookObject->wrapWithDropzone(
                        $table,
                        (int)$editUid,
                        $dataArr,
                        $content
                    );
                }
            }
            if (!$isWrappedWithDropzone) {
                // @TODO: should there be a config for dropzones like "if ((int)$conf['addDropzone'] > 0)"
                // Add a dropzone after content
                $content = $wrapperService->wrapContentWithDropzone(
                    $table,
                    (int)$editUid,
                    $content,
                    (int)$dataArr['colPos']
                );

                // If it's first content element for this column wrap with dropzone before content too
                if (!GeneralUtility::inList(self::$columnsWithContentList, $dataArr['colPos'])) {
                    $content = $wrapperService->wrapContentWithDropzone(
                        $table,
                        0,
                        $content,
                        (int)$dataArr['colPos'],
                        [],
                        true
                    );
                    self::$columnsWithContentList .= ',' . $dataArr['colPos'];
                }
            }
        }

        return $content;
    }

    /**
     * Get plugin configuration from TypoScript
     *
     * @return array
     */
    protected function getPluginConfiguration(): array
    {
        /** @var TypoScriptService $typoScriptService */
        $typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class);
        $configuration = $typoScriptService->convertTypoScriptArrayToPlainArray(
            $this->frontendController->tmpl->setup
        );
        if (is_array($configuration['config']['tx_frontendediting'])) {
            $configuration = $configuration['config']['tx_frontendediting'];
        } else {
            $configuration = [];
        }
        return $configuration;
    }
}
