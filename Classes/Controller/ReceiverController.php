<?php
declare(strict_types=1);
namespace TYPO3\CMS\FrontendEditing\Controller;

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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\FrontendEditing\RequestPreProcess\RequestPreProcessInterface;

/**
 * Main class for handling requests sent via Frontend Editing, and providing the information
 * properly to the DataHandler
 * Called from the Backend Endpoint
 */
class ReceiverController
{
    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * Main entrypoint, dispatches to the appropriate methods
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    public function processRequest(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $this->response = $response;

        $table = $request->getParsedBody()['table'];
        $uid = (int)$request->getParsedBody()['uid'];

        switch ($request->getMethod()) {
            case 'DELETE':
                $this->deleteAction($table, $uid);
                break;

            // modifying existing records
            case 'POST':
                $action = $request->getQueryParams()['action'];
                switch ($action) {
                    case 'hide':
                        $this->hideAction(
                            $table,
                            $uid,
                            (bool)$request->getParsedBody()['hide']
                        );
                        break;
                    case 'move':
                        $this->moveAction(
                            $table,
                            $uid,
                            (int)$request->getParsedBody()['beforeUid']
                        );
                        break;
                    case 'lockedRecord':
                        $this->lockedRecordAction(
                            $table,
                            $uid
                        );
                        break;
                    default:
                        $this->updateAction(
                            $table,
                            $uid,
                            $request->getParsedBody()['field'],
                            $request->getParsedBody()['content']
                        );
                }
                break;
            default:
                $this->writeErrorMessage('Invalid action');
        }
        return $response;
    }

    /**
     * Update a record through the data handler
     *
     * @param string $table
     * @param int $uid
     * @param string $fieldName
     * @param string $content
     * @throws \UnexpectedValueException
     */
    protected function updateAction(string $table, int $uid, string $fieldName, string $content)
    {
        $fieldName = $this->sanitizeFieldName($fieldName);

        if (isset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['FrontendEditing']['requestPreProcess'])) {
            $requestPreProcessArray = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['FrontendEditing']['requestPreProcess'];
            if (is_array($requestPreProcessArray)) {
                $record = BackendUtility::getRecord($table, $uid);
                $finished = false;
                foreach ($requestPreProcessArray as $className) {
                    $hookObject = GeneralUtility::makeInstance($className);
                    if (!($hookObject instanceof RequestPreProcessInterface)) {
                        throw new \UnexpectedValueException(
                            $className . ' must implement interface ' . RequestPreProcessInterface::class,
                            1274563547
                        );
                    }
                    $content = $hookObject->preProcess($table, $record, $fieldName, $content, $finished);
                    if ($finished) {
                        break;
                    }
                }
            }
        }

        $data = [
            $table => [
                $uid => [
                    $fieldName => $content
                ]
            ]
        ];

        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start($data, []);
        $dataHandler->process_datamap();

        if (empty($dataHandler->errorLog)) {
            $this->writeSuccessMessage('Content updated (' . $uid . ')');
        } else {
            $this->writeErrorMessage('Content could not be updated (' . $uid . ')');
        }
    }

    /**
     * Delete a record through the data handler
     *
     * @param string $table
     * @param int $uid
     */
    protected function deleteAction(string $table, int $uid)
    {
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->BE_USER = $GLOBALS['BE_USER'];
        // Delete the record
        $dataHandler->deleteAction($table, $uid);
        if (empty($dataHandler->errorLog)) {
            $this->writeSuccessMessage('Content deleted (' . $uid . ')');
        } else {
            $this->writeErrorMessage('Content could not be deleted (' . $uid . ')');
        }
    }

    /**
     * Hide a record through the data handler
     *
     * @param string $table
     * @param int $uid
     * @param bool $hide
     */
    protected function hideAction(string $table, int $uid, bool $hide)
    {
        $tcaCtrl = $GLOBALS['TCA'][$table]['ctrl'];
        if (isset($tcaCtrl['enablecolumns']['disabled'])) {
            $data = [];
            $data[$table][$uid][$tcaCtrl['enablecolumns']['disabled']] = $hide;
            $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
            $dataHandler->start($data, []);
            $dataHandler->process_datamap();

            if (empty($dataHandler->errorLog)) {
                $this->writeSuccessMessage('Content hidden (' . $uid . ')');
            } else {
                $this->writeErrorMessage('Content could not be hidden (' . $uid . ')');
            }
        } else {
            $this->writeErrorMessage('Table does not have a hidden field (' . $table . ')');
        }
    }

    /**
     * Check if the current record is locked
     *
     * @param string $table
     * @param int $uid
     */
    protected function lockedRecordAction(string $table, int $uid)
    {
        if (BackendUtility::isRecordLocked($table, $uid)) {
            $this->writeSuccessMessage('The content "' . $uid . '" is currently edited by someone else. Do you want to save this?');
        }
    }

    /**
     * Move a content to another position (columnPosition, colpos)
     * Will probably only work on tt_content (due to the special handling of colPos)
     *
     * @param string $table
     * @param int $uid
     * @param int $beforeUid
     * @param int $pid
     * @param int $columnPosition
     * @param int $container
     */
    public function moveAction(string $table, int $uid, int $beforeUid = 0, int $pid = 0, int $columnPosition = -2, int $container = 0)
    {
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $command = [];
        $data = [];

        // Add mapping for which id is should be move to
        if ($beforeUid) {
            $command[$table][$uid]['move'] = '-' . $beforeUid;
        } else {
            // Otherwise to another page (pid)
            $command[$table][$uid]['move'] = $pid;
        }

        if ($columnPosition > -2) {
            $data[$table][$uid]['colPos'] = $columnPosition;
        }

        if ($container) {
            $data[$table][$uid]['colPos'] = $container;
        }

        $dataHandler->start($data, $command);
        $dataHandler->process_cmdmap();
        $dataHandler->process_datamap();

        if (empty($dataHandler->errorLog)) {
            $this->writeSuccessMessage('Content moved (uid: ' . $uid . ')');
        } else {
            $this->writeErrorMessage('Content could not be moved (uid: ' . $uid . ')');
        }
    }

    /**
     * Get the field configuration from a field list
     * If content is rendered from "css_styled_content"
     * Then find out which database field to save data into
     *
     * @param string $fieldName
     * @return string
     */
    protected function sanitizeFieldName(string $fieldName): string
    {
        if (strpos($fieldName, ' ') !== false) {
            $fieldConfiguration = array_unique(GeneralUtility::trimExplode(',', $fieldName, true));
            // Get the actual database to store the data into
            $fieldNameArray = explode(' ', $fieldConfiguration[0]);
            $fieldName = $fieldNameArray[0];
        }
        return $fieldName;
    }

    /**
     * Ensure the message is printed and encoded as JSON
     *
     * @param string $message
     */
    protected function writeSuccessMessage(string $message)
    {
        $message = [
            'success' => true,
            'message' => $message
        ];
        $this->response->getBody()->write(json_encode($message));
    }

    /**
     * Ensure the message is printed and encoded as JSON
     *
     * @param string $message
     */
    protected function writeErrorMessage(string $message)
    {
        $message = [
            'success' => false,
            'message' => $message
        ];
        $this->response->getBody()->write(json_encode($message));
    }
}
