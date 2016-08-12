<?php

namespace TYPO3\CMS\FrontendEditing\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SaveController extends ActionController
{
    /**
     * @var \TYPO3\CMS\Extbase\Mvc\View\JsonView
     */
    protected $view;

    /**
     * @var string
     */
    protected $defaultViewObjectName = \TYPO3\CMS\Extbase\Mvc\View\JsonView::class;

    /**
     * @var \TYPO3\CMS\Core\DataHandling\DataHandler
     */
    protected $dataHandler;

    /**
     * @var \TYPO3\CMS\Core\FrontendEditing\FrontendEditingController
     */
    protected $frontendEditingController;

    protected $table;
    protected $uid;
    protected $field;
    protected $content;
    protected $record;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->dataHandler = new \TYPO3\CMS\Core\DataHandling\DataHandler();
        $this->dataHandler->stripslashes_values = 0;

        $this->frontendEditingController = new \TYPO3\CMS\Core\FrontendEditing\FrontendEditingController();

        // $configurationArray = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['aloha']);
        // $this->saveMethod = $configurationArray['saveMethod'];

        if (!isset($GLOBALS['LANG'])) {
            // DataHandler uses $GLOBALS['LANG'] when saving records, some users didn't have it set....
            \TYPO3\CMS\Frontend\Utility\EidUtility::initLanguage();
        }
    }

    /**
     * Resolves and checks the current action method name
     *
     * @return string Method name of the current action
     */
    protected function resolveActionMethodName()
    {
        $actionName = '';
        switch ($this->request->getMethod()) {
            case 'HEAD':
            case 'GET':
                $actionName = 'save';
                break;
            case 'POST':
                $actionName = 'save';
                break;
            case 'PUT':
                $actionName = 'update';
                break;
            case 'DELETE':
                $actionName = 'delete';
                break;
            default:
                $this->throwStatus(400, null, 'Bad Request.');
        }

        return $actionName . 'Action';
    }

    /**
     * @throws \Exception
     */
    protected function createRequestMapping() {

        $body = GeneralUtility::_POST();

        // request is only allowed for POST request and a BE_USER is available
        if (empty($request)) {
            //throw new \BadFunctionCallException(Helper::ll('error.request.no-post'));
        } elseif (!\TYPO3\CMS\FrontendEditing\Utility\Access::isEnabled()) {
            //throw new \BadFunctionCallException(Helper::ll('error.request.not-allowed'));
        }

        /*$split = explode('--', $request['identifier']);

        if (count($split) != 3) {
            throw new \Exception(Helper::ll('error.request.identifier'));
        } elseif (empty($split[0])) {
            throw new \Exception(Helper::ll('error.request.table'));
        } elseif (empty($split[1])) {
            throw new \Exception(Helper::ll('error.request.field'));
        } elseif (!ctype_digit($split[2])) {
            throw new \Exception(Helper::ll('error.request.uid'));
        }*/

        $this->table = $body['table'];
        $this->field = $body['field'];
        $this->uid = $body['identifier'];
        $this->content = $body['content'];
        $this->record = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', $this->table, 'uid=' . $this->uid);
    }

    /**
     * @return void
     */
    public function saveAction()
    {
        try {
            $this->createRequestMapping();

            /*if (is_array($GLOBALS['TYPO3_CONF_VARS']['Aloha']['Classes/Save/Save.php']['requestPreProcess'])) {
                $finished = false;
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
            }*/

            //Aloha automatically encodes entities, but typo3 automatically encodes them too,
            //so we have to decode them from Aloha otherwise we would encode twice.
            //CHANGED from html_entity_decode to urldecode, after problem with encoding on some servers which broke content and flexform.

            $htmlEntityDecode = false; // true

            $this->content = \TYPO3\CMS\FrontendEditing\Utility\Integration::rteModification($this->table, $this->field, $this->uid, $GLOBALS['TSFE']->id, $this->content);

            if ($htmlEntityDecode) {
                $this->content = urldecode($this->content);
                // Try to remove invalid utf-8 characters so content won't break if there are invalid characters in content
                $this->content = iconv("UTF-8", "UTF-8//IGNORE", $this->content);
            }

            //if (!empty($request)) {
                $data = array(
                    $this->table => array(
                        $this->uid => array(
                            $this->field => $this->content
                        )
                    )
                );
            //}

            $this->dataHandler->start($data, array());
            $this->dataHandler->process_datamap();

        } catch (\Exception $exception) {
            var_dump($exception->getMessage());die;
        }

        //var_dump('asdasd');die;
        //$this->view->assign('tags', $this->tagRepository->findAll());
    }

    /**
     * @return void
     */
    public function updateAction()
    {

    }
}
