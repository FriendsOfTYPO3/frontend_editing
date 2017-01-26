<?php
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

use TYPO3\CMS\Backend\FrontendBackendUserAuthentication;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\FrontendEditing\Utility\Access;
use TYPO3\CMS\FrontendEditing\Utility\ContentEditable\ContentEditableWrapper;
use TYPO3\CMS\FrontendEditing\Utility\Helper;

/**
 * View class for the edit panels in frontend editing
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class FrontendEditingPanel
{
    /**
     * The Content Object Renderer
     *
     * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     */
    protected $cObj;

    /**
     * Property for accessing TypoScriptFrontendController centrally
     *
     * @var TypoScriptFrontendController
     */
    protected $frontendController;

    /**
     * Property for accessing DatabaseConnection centrally
     *
     * @var DatabaseConnection
     */
    protected $databaseConnection;

    /**
     * @var FrontendBackendUserAuthentication
     */
    protected $backendUser;

    /**
     * @var \TYPO3\CMS\Core\Imaging\IconFactory
     */
    protected $iconFactory;

    /**
     * Constructor for the edit panel
     *
     * @param DatabaseConnection $databaseConnection
     * @param TypoScriptFrontendController $frontendController
     * @param FrontendBackendUserAuthentication $backendUser
     */
    public function __construct(
        DatabaseConnection $databaseConnection = null,
        TypoScriptFrontendController $frontendController = null,
        FrontendBackendUserAuthentication $backendUser = null
    ) {
        $this->databaseConnection = $databaseConnection ?: $GLOBALS['TYPO3_DB'];
        $this->frontendController = $frontendController ?: $GLOBALS['TSFE'];
        $this->backendUser = $backendUser ?: $GLOBALS['BE_USER'];
        $this->cObj = GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class);
        $this->cObj->start([]);
        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
    }

    /**
     * Adds an edit icon to the content string. The edit icon links to EditDocumentController
     * with proper parameters for editing the table/fields of the context.
     * This implements TYPO3 context sensitive editing facilities.
     * Only backend users will have access (if properly configured as well).
     *
     * @inheritdoc
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
    ) {
        // We need to determine if we are having whole element or just one field for element
        // this only allows to edit all other tables just per field instead of per element
        if ($conf['beforeLastTag'] === 1) {
            $isEditableField = true;
        } elseif ($table === 'tt_content' || $conf['hasEditableFields'] === 1) {
            $isWholeElement = true;
        } else {
            // default fallback, for everything else with edit icons, we assume it is separate element and is editable
            $isWholeElement = true;
            $isEditableField = true;
        }

        $wrappedContent = $content;

        if (Access::isEnabled() && !Helper::httpRefererIsFromBackendViewModule() && $isEditableField) {
            $fields = explode(',', $fieldList);
            $wrappedContent = ContentEditableWrapper::wrapContentToBeEditable(
                $table,
                $fields[0],
                $editUid,
                $wrappedContent
            );
        }

        if (Access::isEnabled() && !Helper::httpRefererIsFromBackendViewModule() && $isWholeElement) {
            // Special content is about to be shown, so the cache must be disabled.
            $this->frontendController->set_no_cache('Display frontend edit icons', true);

            // wrap content with controls
            $wrappedContent = ContentEditableWrapper::wrapContent(
                $table,
                $editUid,
                $dataArr,
                $wrappedContent
            );

            // @TODO: should there be a config for dropzones like "if ((int)$conf['addDropzone'] > 0)"
            // Add a dropzone after content
            $wrappedContent = ContentEditableWrapper::wrapContentWithDropzone(
                $table,
                $editUid,
                $wrappedContent
            );
        }

        return $wrappedContent;
    }

    /**
     * Returns TRUE if the input table/row would be hidden in the frontend,
     * according to the current time and simulate user group
     *
     * @param string $table The table name
     * @param array $row The data record
     * @return bool
     */
    protected function isDisabled($table, array $row)
    {
        $status = false;
        if ($GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['disabled'] &&
            $row[$GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['disabled']] ||
            $GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['fe_group'] &&
            $this->frontendController->simUserGroup &&
            $row[$GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['fe_group']]
                == $this->frontendController->simUserGroup ||
            $GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['starttime'] &&
            $row[$GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['starttime']] > $GLOBALS['EXEC_TIME'] ||
            $GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['endtime'] &&
            $row[$GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['endtime']] &&
            $row[$GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['endtime']] < $GLOBALS['EXEC_TIME']
        ) {
            $status = true;
        }

        return $status;
    }
}
