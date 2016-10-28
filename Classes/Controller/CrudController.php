<?php

namespace TYPO3\CMS\FrontendEditing\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\FrontendEditing\Utility\RequestPreProcess\RequestPreProcessInterface;

class CrudController extends ActionController
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
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $uid;

    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var string
     */
    protected $fieldConfiguration;

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

        if (!isset($GLOBALS['LANG'])) {
            // DataHandler uses $GLOBALS['LANG'] when saving records
            \TYPO3\CMS\Frontend\Utility\EidUtility::initLanguage();
        }
    }

    /**
     * @return \TYPO3\CMS\Core\DataHandling\DataHandler
     */
    public function getDataHandler()
    {
        return $this->dataHandler;
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param string $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getRecord()
    {
        return $this->record;
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
                $actionName = 'read';
                break;
            case 'PUT':
            case 'POST':
                $actionName = 'save';
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
     * Get the field configuration from a field list
     *
     * @param string $fieldList
     * @return array
     */
    private function getFieldConfiguration($fieldList)
    {
        $fieldConfiguration = array_unique(GeneralUtility::trimExplode(',', $fieldList, true));
        return $fieldConfiguration;
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
            $this->throwStatus(
                400,
                null,
                'A body is missing in the request!'
            );
        }

        // Check body data
        if (empty($body['table'])) {
            $this->throwStatus(
                400,
                null,
                'Property "table" is missing from the body!'
            );
        } elseif (empty($body['field'])) {
            $this->throwStatus(
                400,
                null,
                'Property "field" is missing from the body!'
            );
        } elseif (empty($body['uid'])) {
            $this->throwStatus(
                400,
                null,
                'Property "uid" is missing from the body!'
            );
        } elseif (!isset($body['content'])) {
            $this->throwStatus(
                400,
                null,
                'Property "content" is missing from the body!'
            );
        }

        // Set the basic properties of editing
        $this->table = $body['table'];
        // If content is rendered from "css_styled_content"
        // Then find out which database field to save data into
        if (strpos($body['field'], ' ') !== false) {
            $this->fieldConfiguration = $this->getFieldConfiguration($body['field']);
            // Get the actual database to store the data into
            $fieldNameArray = explode(' ', $this->fieldConfiguration[1]);
            $this->field = $fieldNameArray[0];
        } else {
            // If content is rendered from "fluid_styled_content"
            $this->field = $body['field'];
        }
        $this->uid = $body['uid'];
        $this->record = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', $this->table, 'uid=' . $this->uid);

        $requestPreProcessArray =
            $GLOBALS['TYPO3_CONF_VARS']['Ckeditor']['Classes/Save/Save.php']['requestPreProcess'];
        if (is_array($requestPreProcessArray)) {
            $finished = false;
            foreach ($requestPreProcessArray as $classData) {
                if (!$finished) {
                    $hookObject = GeneralUtility::getUserObj($classData);
                    if (!($hookObject instanceof RequestPreProcessInterface)) {
                        throw new \UnexpectedValueException(
                            $classData . ' must implement interface ' . RequestPreProcessInterface::class,
                            1274563547
                        );
                    }
                    $body = $hookObject->preProcess($body, $finished, $this);
                }
            }
        }

        $this->content = $body['content'];
    }

    /**
     * Main method for saving/updating records
     *
     * @return array
     * @throws \Exception
     */
    public function saveAction()
    {
        try {
            $htmlEntityDecode = true;

            $this->content = \TYPO3\CMS\FrontendEditing\Utility\Integration::rteModification(
                $this->table,
                $this->field,
                $this->uid,
                $GLOBALS['TSFE']->id,
                $this->content
            );

            if ($htmlEntityDecode) {
                $this->content = urldecode($this->content);
                // Try to remove invalid utf-8 characters so content
                // won't break if there are invalid characters in content
                $this->content = iconv('UTF-8', 'UTF-8//IGNORE', $this->content);
            }

            if (empty($this->table)) {
                $this->throwStatus(
                    400,
                    'Missing table name',
                    'A table name is missing, no possibility to save the data!'
                );
            }

            $data = [
                $this->table => [
                    $this->uid => [
                        $this->field => $this->content
                    ]
                ]
            ];

            $this->dataHandler->start($data, []);
            $this->dataHandler->process_datamap();

            $message = [
                'success' => true,
                'message' => $this->uid
            ];
        } catch (\Exception $exception) {
            $this->throwStatus(
                500,
                $exception->getFile(),
                $exception->getMessage()
            );
        }

        return json_encode($message);
    }

    /**
     * @param string $uid
     * @param string $table
     */
    public function deleteAction($uid, $table)
    {
    }

    /**
     * @param string $uid
     * @param string $table
     */
    public function readAction($uid, $table)
    {
    }
}
