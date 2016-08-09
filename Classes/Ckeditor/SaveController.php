<?php
namespace TYPO3\CMS\FrontendEditing\Ckeditor;

use Pixelant\Aloha\Utility\Helper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Save the changes by using TCEmain
 *
 * @package TYPO3
 * @subpackage tx_aloha
 */
class SaveController
{

    /**
     * @var \TYPO3\CMS\Core\DataHandling\DataHandler
     */
    protected $tce;

    /**
     * @var \TYPO3\CMS\Core\FrontendEditing\FrontendEditingController
     */
    protected $frontendEditingController;

    /**
     * If set, a javascript reload is added to the response
     *
     * @var boolean
     */
    protected $forceReload = FALSE;

    protected $table;
    protected $uid;
    protected $field;

    /**
     * Save method, can be none, direct or intermediate
     * @var string
     */
    protected $saveMethod;

    public function __construct()
    {
        $this->tce = GeneralUtility::makeInstance('TYPO3\CMS\Core\DataHandling\DataHandler');
        $this->tce->stripslashes_values = 0;

        $this->frontendEditingController = GeneralUtility::makeInstance('TYPO3\CMS\Core\FrontendEditing\FrontendEditingController');

        $configurationArray = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['aloha']);
        $this->saveMethod = $configurationArray['saveMethod'];

        if (!isset($GLOBALS['LANG'])) {
            // DataHandler uses $GLOBALS['LANG'] when saving records, some users didn't have it set....
            \TYPO3\CMS\Frontend\Utility\EidUtility::initLanguage();
        }
    }

    /**
     * Initial function which is called by an additional page type
     * Calls correct function to edit/hide/delete/... record
     *
     * @param string $content
     * @param array $conf plugin configuration
     * @return string
     */
    public function start($content, $conf)
    {
        $request = GeneralUtility::_POST();
        $response = '';

        // aborting save
        if ($this->saveMethod === 'none') {
            return Helper::ll('response.action.save-method-none');
        }

        try {
            // @todo check if this workaround can be solved differently
            $GLOBALS['PAGES_TYPES']['default']['allowedTables'] = 'pages,tt_content';

            if ($request['action'] === 'discardSavings') {
                $response = $this->discardSavings();
            } elseif ($request['action'] === 'commitSavings') {
                $response = $this->commitSavings();
            } elseif ($request['action'] === 'updateSaveState') {
                $response = $this->updateSaveState();
            } else {
                $this->init($request);

                switch ($request['action']) {
                    case 'save':
                        if ($this->saveMethod == 'direct') {
                            $response = $this->directSave($request);
                        } elseif ($this->saveMethod == 'intermediate') {
                            $response = $this->intermediateSave($request);
                        } else {
                            throw new \Exception(Helper::ll('error.saveMethod'));
                        }
                        break;
                    case 'up':
                        $response = $this->move('up');
                        break;
                    case 'down':
                        $response = $this->move('down');
                        break;
                    case 'hide':
                        $response = $this->changeVisibility(1);
                        break;
                    case 'unhide':
                        $response = $this->changeVisibility(0);
                        break;
                    case 'delete':
                        $response = $this->delete();
                        break;
                    default:
                        $errorMsg = sprintf(Helper::ll('error.response-action'), $request['action']);
                        throw new \Exception($errorMsg);
                }
            }

            // Add JS reload if needed
            if ($this->forceReload) {
                $response .= '<script type="text/javascript">window.location.reload();</script>';
            }

        } catch (\Exception $e) {
            $response = $e->getMessage();
            header('HTTP/1.1 404 Not Found');
        }

        return $response;
    }

    private function commitSavings()
    {
        $elements = $GLOBALS['BE_USER']->uc['aloha'][$GLOBALS['TSFE']->id];

        if (!is_array($elements) || count($elements) == 0) {
            $response = Helper::ll('response.action.nothing-to-save');
        } else {

            foreach ($elements as $element) {
                $this->directSave(unserialize($element), TRUE);
            }

            $GLOBALS['BE_USER']->uc['aloha'][$GLOBALS['TSFE']->id] = array();
            $GLOBALS['BE_USER']->writeUC();

            $response = Helper::ll('response.action.save') . '
						<script>
							window.alohaQuery("#count").text("0").removeClass("tobesaved");
							window.parent.alohaQuery("#count").text("0").removeClass("tobesaved");
						</script>';
        }

        return $response;
    }

    private function updateSaveState()
    {

        $countOfElements = \Pixelant\Aloha\Utility\Integration::getCountOfUnsavedElements($GLOBALS['TSFE']->id);
        $response = '<script>
						window.alohaQuery("#count").text("' . $countOfElements . '").' . ($countOfElements > 0 ? 'add' : 'remove') . 'Class("tobesaved");
						window.alohaQuery("#aloha-saveButton").show();
						window.parent.alohaQuery("#count").text("' . $test . $countOfElements . '").' . ($countOfElements > 0 ? 'add' : 'remove') . 'Class("tobesaved");
						window.parent.alohaQuery("#aloha-saveButton").show();
					</script>';
        return $response;
    }

    private function intermediateSave(array $request)
    {

        $GLOBALS['BE_USER']->uc['aloha'][$GLOBALS['TSFE']->id][$request['identifier']] = serialize($request);
        $GLOBALS['BE_USER']->writeUC();

        $countOfElements = \Pixelant\Aloha\Utility\Integration::getCountOfUnsavedElements($GLOBALS['TSFE']->id);

        $response = Helper::ll('response.action.intermediate-save') .
            '<script>
				window.alohaQuery("#count").text("' . $countOfElements . '").' . ($countOfElements > 0 ? 'add' : 'remove') . 'Class("tobesaved");
				window.alohaQuery("#aloha-saveButton").show();
				window.parent.alohaQuery("#count").text("' . $test . $countOfElements . '").' . ($countOfElements > 0 ? 'add' : 'remove') . 'Class("tobesaved");
				window.parent.alohaQuery("#aloha-saveButton").show();
			</script>';
        return $response;
    }

    private function discardSavings()
    {
        $elements = $GLOBALS['BE_USER']->uc['aloha'][$GLOBALS['TSFE']->id];
        if (!is_array($elements) || count($elements) == 0) {
            $response = Helper::ll('response.action.discard-savings-nothing-to-save');
        } else {

            $GLOBALS['BE_USER']->uc['aloha'][$GLOBALS['TSFE']->id] = array();
            $GLOBALS['BE_USER']->writeUC();

            $this->forceReload = TRUE;

            $response = Helper::ll('response.action.discard-savings') . '<script>
							window.alohaQuery("#count").text("0").removeClass("tobesaved");
							window.parent.alohaQuery("#count").text("0").removeClass("tobesaved");
							</script>';
        }

        return $response;
    }


    /**
     * Hide/Unhide record
     *
     * @param integer $visibility
     * @return string
     */
    private function changeVisibility($visibility)
    {
        $this->forceReload = TRUE;

        if ($visibility == 0) {
            $this->frontendEditingController->doUnhide($this->table, $this->uid);
            return Helper::ll('response.action.unhide');
        } elseif ($visibility == 1) {
            $this->frontendEditingController->doHide($this->table, $this->uid);
            return Helper::ll('response.action.hide');
        }
    }

    /**
     * Delete a record
     *
     * @return string
     */
    private function delete()
    {
        $this->forceReload = TRUE;
        $this->frontendEditingController->doDelete($this->table, $this->uid);

        return Helper::ll('response.action.delete');
    }

    /**
     * Move a table, either up or down (set in $direction)
     *
     * @param string $direction
     * @throws \Exception
     * @return string
     */
    private function move($direction)
    {
        $this->forceReload = TRUE;

        if ($direction === 'down') {
            $this->frontendEditingController->doDown($this->table, $this->uid);
            return Helper::ll('response.action.moveDown');
        } elseif ($direction === 'up') {
            $this->frontendEditingController->doUp($this->table, $this->uid);
            return Helper::ll('response.action.moveUp');
        } else {
            throw new \Exception(sprintf(Helper::ll('error.move-action.wrong-direction'), htmlspecialchars($direction)));
        }
    }

    /**
     * True main function which starts to call tcemain
     *
     * @param array $request POST request
     * @return string
     */
    public function directSave(array $request, $initAgain = FALSE)
    {
        // PIXELANT HACK - this function needs to be public
        // @todo do that nice again

        if ($initAgain) {
            $this->init($request);
        }

        if (is_array($GLOBALS['TYPO3_CONF_VARS']['Aloha']['Classes/Save/Save.php']['requestPreProcess'])) {
            $finished = FALSE;
            foreach ($GLOBALS['TYPO3_CONF_VARS']['Aloha']['Classes/Save/Save.php']['requestPreProcess'] as $classData) {
                if (!$finished) {
                    $hookObject = GeneralUtility::getUserObj($classData);
                    if (!($hookObject instanceof \Pixelant\Aloha\Hook\RequestPreProcessInterface)) {
                        throw new \UnexpectedValueException(
                            $classData . ' must implement interface \Pixelant\Aloha\Hook\RequestPreProcessInterface',
                            1274563549
                        );
                    }
                    $request = $hookObject->preProcess($request, $finished, $this);
                }
            }
        }

        /*
        Aloha automatically encodes entities, but typo3 automatically encodes them too,
        so we have to decode them from Aloha otherwise we would encode twice.
        CHANGED from html_entity_decode to urldecode, after problem with encoding on some servers which broke content and flexform.
        */
        $htmlEntityDecode = TRUE;

        $request['content'] = \Pixelant\Aloha\Utility\Integration::rteModification($this->table, $this->field, $this->uid, $GLOBALS['TSFE']->id, $request['content']);

        if ($htmlEntityDecode) {
            $request['content'] = urldecode($request['content']);
            // Try to remove invalid utf-8 characters so content won't break if there are invalid characters in content
            $request['content'] = iconv("UTF-8", "UTF-8//IGNORE", $request['content']);
        }

        if (!empty($request)) {
            $data = array(
                $this->table => array(
                    $this->uid => array(
                        $this->field => $request['content']
                    )
                )
            );
        }

        $this->tce->start($data, array());
        $this->tce->process_datamap();

        return Helper::ll('response.action.save');
    }

    /**
     * Initialize everything
     *
     * @param array $request POST request
     * @throws \Exception
     * @return void
     */
    private function init(array $request)
    {
        // request is only allowed for POST request and a BE_USER is available
        if (empty($request)) {
            throw new \BadFunctionCallException(Helper::ll('error.request.no-post'));
        } elseif (!\Pixelant\Aloha\Utility\Access::isEnabled()) {
            throw new \BadFunctionCallException(Helper::ll('error.request.not-allowed'));
        }

        $split = explode('--', $request['identifier']);

        if (count($split) != 3) {
            throw new \Exception(Helper::ll('error.request.identifier'));
        } elseif (empty($split[0])) {
            throw new \Exception(Helper::ll('error.request.table'));
        } elseif (empty($split[1])) {
            throw new \Exception(Helper::ll('error.request.field'));
        } elseif (!ctype_digit($split[2])) {
            throw new \Exception(Helper::ll('error.request.uid'));
        }

        $this->table = $split[0];
        $this->field = $split[1];
        $this->uid = (int)$split[2];
        $this->content = $request['content'];
        $this->record = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', $this->table, 'uid=' . $this->uid);
    }

    /**
     * Wrapper function to get uid of record
     *
     * @return integer
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * Wrapper function to get field name
     *
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Wrapper function to set field name
     *
     * @param string $field
     * @return void
     */
    public function setField($field)
    {
        $this->field = $field;
    }

    /**
     * Wrapper function to get the tablename
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Wrapper function to get record
     *
     * @return array
     */
    public function getRecord()
    {
        return $this->record;
    }
}