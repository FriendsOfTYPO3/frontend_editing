<?php
namespace TYPO3\CMS\FrontendEditing\EditingPanel;

use TYPO3\CMS\Backend\FrontendBackendUserAuthentication;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Type\Bitmask\JsConfirmation;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\View\AdminPanelView;

/**
 * View class for the edit panels in frontend editing.
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
        $this->cObj->start(array());
        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
    }

    /**
     * Generates the "edit panels" which can be shown for a page or records on a page when the Admin Panel is enabled for a backend users surfing the frontend.
     * With the "edit panel" the user will see buttons with links to editing, moving, hiding, deleting the element
     * This function is used for the cObject EDITPANEL and the stdWrap property ".editPanel"
     *
     * @param string $content A content string containing the content related to the edit panel. For cObject "EDITPANEL" this is empty but not so for the stdWrap property. The edit panel is appended to this string and returned.
     * @param array $conf TypoScript configuration properties for the editPanel
     * @param string $currentRecord The "table:uid" of the record being shown. If empty string then $this->currentRecord is used. For new records (set by $conf['newRecordFromTable']) it's auto-generated to "[tablename]:NEW
     * @param array $dataArr Alternative data array to use. Default is $this->data
     * @param string $table
     * @param array $allow
     * @param int $newUID
     * @param array $hiddenFields
     * @return string The input content string with the editPanel appended. This function returns only an edit panel appended to the content string if a backend user is logged in (and has the correct permissions). Otherwise the content string is directly returned.
     */
    public function editPanel(
        $content,
        array $conf,
        $currentRecord = '',
        array $dataArr = array(),
        $table = '',
        array $allow = array(),
        $newUID = 0,
        array $hiddenFields = array()
    ) {
        return $finalOut;
    }

    /**
     * Adds an edit icon to the content string. The edit icon links to EditDocumentController with proper parameters for editing the table/fields of the context.
     * This implements TYPO3 context sensitive editing facilities. Only backend users will have access (if properly configured as well).
     *
     * @param string $content The content to which the edit icons should be appended
     * @param string $params The parameters defining which table and fields to edit. Syntax is [tablename]:[fieldname],[fieldname],[fieldname],... OR [fieldname],[fieldname],[fieldname],... (basically "[tablename]:" is optional, default table is the one of the "current record" used in the function). The fieldlist is sent as "&columnsOnly=" parameter to EditDocumentController
     * @param array $conf TypoScript properties for configuring the edit icons.
     * @param string $currentRecord The "table:uid" of the record being shown. If empty string then $this->currentRecord is used. For new records (set by $conf['newRecordFromTable']) it's auto-generated to "[tablename]:NEW
     * @param array $dataArr Alternative data array to use. Default is $this->data
     * @param string $addUrlParamStr Additional URL parameters for the link pointing to EditDocumentController
     * @param string $table
     * @param int $editUid
     * @param string $fieldList
     * @return string The input content string, possibly with edit icons added (not necessarily in the end but just after the last string of normal content.
     */
    public function editIcons(
        $content,
        $params,
        array $conf = array(),
        $currentRecord = '',
        array $dataArr = array(),
        $addUrlParamStr = '',
        $table,
        $editUid,
        $fieldList
    ) {
        // Special content is about to be shown, so the cache must be disabled.
        $this->frontendController->set_no_cache('Display frontend edit icons', true);

        $content = sprintf(
            '<div contenteditable="true" data-table="%s" data-field="%s" data-uid="%s">%s</div>',
            $table,
            $fieldList,
            $editUid,
            $content
        );

        return $content;
    }

    /**
     * Returns TRUE if the input table/row would be hidden in the frontend, according to the current time and simulate user group
     *
     * @param string $table The table name
     * @param array $row The data record
     * @return bool
     */
    protected function isDisabled($table, array $row)
    {
        $status = false;
        if (
            $GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['disabled'] &&
            $row[$GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['disabled']] ||
            $GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['fe_group'] &&
            $this->frontendController->simUserGroup &&
            $row[$GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['fe_group']] == $this->frontendController->simUserGroup ||
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
