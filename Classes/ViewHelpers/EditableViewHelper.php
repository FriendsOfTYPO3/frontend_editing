<?php
namespace TYPO3\CMS\FrontendEditing\ViewHelpers;

use TYPO3\CMS\FrontendEditing\Utility\Access;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Viewhelper to enable aloha for records in fluid
 *
 * Example:
 * {namespace fe=TYPO3\CMS\FrontendEditing\ViewHelpers}
 *
 * <fe:editable table="tt_content" field="bodytext" uid="{item.uid}"">
 *     {item.bodytext}
 * </fe:editable>
 *
 */
class EditableViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * Render aloha integration for a single field
     *
     * @param string $table table of record
     * @param string $field database field of record
     * @param integer $uid uid of record
     * @return string
     */
    public function render($table, $field, $uid) {
        $content = $this->renderChildren();

        if (Access::isEnabled()) {
            /*$finalConfiguration = array(
                'alohaProcess' => 1,
                'alohaProcess.' => array(
                    'field' => $field,
                ),
                'stdWrapProcess' => 'Pixelant\Aloha\Hook\EditIcons->render',
            );

            // Add additional configuration
            foreach ($configuration as $key => $value) {
                $finalConfiguration['alohaProcess.'][$key] = $value;
            }

            // Since some templates don't have allow set, set allow by default for backward compatibility reasons
            if (!isset($finalConfiguration['alohaProcess.']['allow'])) {
                $finalConfiguration['alohaProcess.']['allow'] = 'all';
            }

            // @Todo: Caching
            $record = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', $table, 'uid=' . (int)$uid);

             @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $cObj
            $cObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer');
            $cObj->start($record, $table);

            $content = $cObj->stdWrap($content, $finalConfiguration);*/

            $content = sprintf(
                '<div contenteditable="true" data-table="%s" data-field="%s" data-uid="%s">%s</div>',
                $table,
                $field,
                $uid,
                $content
            );

            $contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
            $content = $contentObject->parseFunc($content, [], '< ' . 'lib.parseFunc_RTE');
            $content = html_entity_decode($content);
        }

        return $content;
    }

}
