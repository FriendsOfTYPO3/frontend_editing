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

    /**
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var string
     */
    protected $field;

    /**
     * @var string
     */
    protected $content;

    /**
     * @var string
     */
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

        $this->createRequestMapping();

        return $actionName . 'Action';
    }

    /**
     * Create the necessary data mapping for further usage for editing
     *
     * @throws \Exception
     */
    protected function createRequestMapping()
    {
        $body = GeneralUtility::_POST();

        // Request is only allowed for POST request and a BE_USER is available
        if (!isset($GLOBALS['BE_USER'])) {
            throw new \Exception('This action is only allowed logged in to the backend!');
        } elseif (empty($body)) {
            throw new \Exception('A body is missing in the request!');
        }

        // Check body data
        if (empty($body['table'])) {
            throw new \Exception('Property "table" is missing from the body!');
        } elseif (empty($body['field'])) {
            throw new \Exception('Property "field" is missing from the body!');
        } elseif (empty($body['identifier'])) {
            throw new \Exception('Property "identifier" is missing from the body!');
        } elseif (empty($body['content'])) {
            throw new \Exception('Property "content" is missing from the body!');
        }

        $this->table = $body['table'];
        $this->field = $body['field'];
        $this->identifier = $body['identifier'];
        $this->content = $body['content'];
        $this->record = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', $this->table, 'uid=' . $this->identifier);
    }

    /**
     * Main method for saving records
     *
     * @return void
     * @throws \Exception
     */
    public function saveAction()
    {
        try {
            /*if (is_array($GLOBALS['TYPO3_CONF_VARS']['Aloha']['Classes/Save/Save.php']['requestPreProcess'])) {
                $finished = false;
                foreach (
                    $GLOBALS['TYPO3_CONF_VARS']['Aloha']['Classes/Save/Save.php']['requestPreProcess'] as $classData
                ) {
                    if (!$finished) {
                        $hookObject = GeneralUtility::getUserObj($classData);
                        if (!($hookObject instanceof \Pixelant\Aloha\Hook\RequestPreProcessInterface)) {
                            throw new \UnexpectedValueException(
                                $classData .
                                    ' must implement interface \Pixelant\Aloha\Hook\RequestPreProcessInterface',
                                1274563549
                            );
                        }
                        $request = $hookObject->preProcess($request, $finished, $this);
                    }
                }
            }*/

            $htmlEntityDecode = false; // true

            $this->content = \TYPO3\CMS\FrontendEditing\Utility\Integration::rteModification(
                $this->table,
                $this->field,
                $this->identifier,
                $GLOBALS['TSFE']->id,
                $this->content
            );

            if ($htmlEntityDecode) {
                $this->content = urldecode($this->content);
                // Try to remove invalid utf-8 characters so content
                // won't break if there are invalid characters in content
                $this->content = iconv('UTF-8', 'UTF-8//IGNORE', $this->content);
            }

            $data = [
                $this->table => [
                    $this->identifier => [
                        $this->field => $this->content
                    ]
                ]
            ];

            $this->dataHandler->start($data, array());
            $this->dataHandler->process_datamap();
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }

        //$this->throwStatus(200, null, 'Content saved!');
        return json_encode(['success' => true]);
    }

    /**
     * @return void
     */
    public function updateAction()
    {
    }
}
