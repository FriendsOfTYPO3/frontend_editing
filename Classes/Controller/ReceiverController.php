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
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\FrontendEditing\Controller\Event\PrepareFieldUpdateEvent;

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
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * ReceiverController constructor.
     *
     * @param EventDispatcher $eventDispatcher
     */
    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Main entrypoint, dispatches to the appropriate methods
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function processRequest(ServerRequestInterface $request): ResponseInterface
    {
        $this->response = new Response();

        $table = $request->getParsedBody()['table'];
        $uid = (int)$request->getParsedBody()['uid'];

        switch ($request->getMethod()) {
            case 'DELETE':
                $this->deleteAction($table, $uid);
                break;

            // modifying existing or creating new records
            case 'POST':
                $action = $request->getQueryParams()['action'];
                switch ($action) {
                    case 'new':
                        $data = [];
                        parse_str($request->getParsedBody()['data'], $data);
                        $this->newAction($data['edit'], $data['defVals'], (int)$request->getQueryParams()['page']);
                        break;
                    case 'hide':
                        $this->hideAction(
                            $table,
                            $uid,
                            (bool)$request->getParsedBody()['hide']
                        );
                        break;
                    case 'move':
                        // Check if colPos is set
                        $colPos = isset($request->getParsedBody()['colPos']) ?
                            (int)$request->getParsedBody()['colPos'] : -2;

                        // Check if page is set
                        $page = isset($request->getQueryParams()['page']) ?
                            (int)$request->getQueryParams()['page'] : 0;

                        $this->moveAction(
                            $table,
                            $uid,
                            (int)$request->getParsedBody()['beforeUid'],
                            $page,
                            $colPos,
                            $request->getParsedBody()['defVals'] ?? []
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
        return $this->response;
    }

    /**
     * Update a record through the data handler
     *
     * @param string $table
     * @param int $uid
     * @param string $field
     * @param string $content
     * @throws \UnexpectedValueException
     */
    protected function updateAction(string $table, int $uid, string $field, string $content)
    {
        $field = $this->sanitizeFieldName($field);

        $record = BackendUtility::getRecord($table, $uid);

        $content = $this->eventDispatcher->dispatch(
            new PrepareFieldUpdateEvent($table, $field, $content, $record)
        )->getContent();

        $data = [
            $table => [
                $uid => [
                    $field => $content
                ]
            ]
        ];

        try {
            $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
            $dataHandler->start($data, []);
            $dataHandler->process_datamap();
        } catch (\Exception $exception) {
            // If editing a Site root the DataHandler is loading additional configuration which
            // may be faulty. So suppress the Exception and continue because the update action is valid
        }
        $translateKey = sprintf(
            'notifications.update.%s.',
            $table === 'pages' ? 'pages' : 'content'
        );

        if (empty($dataHandler->errorLog)) {
            $this->writeSuccessMessage(LocalizationUtility::translate(
                $translateKey . 'success',
                'FrontendEditing',
                [$uid]
            ));
        } else {
            $this->writeErrorMessage(LocalizationUtility::translate(
                $translateKey . 'fail',
                'FrontendEditing',
                [$uid]
            ));
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
     * Create a new record.
     *
     * Submitted info is based on a query string as described at
     * https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/Examples/EditLinks/Index.html
     *
     * @param array $edit A an array as parsed from `tt_content[-1]=new`
     * @param array $defVals Default content as an array parsed from `tt_content[title]=title`
     * @param int $pid Page ID
     *
     * @throws \InvalidArgumentException
     */
    protected function newAction(array $edit, array $defVals, int $pid)
    {
        /** @var DataHandler $dataHandler */
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);

        if (count($edit) === 0) {
            throw new \InvalidArgumentException(
                'Missing element information (zero items in $edit)',
                1579267630
            );
        }

        $table = array_keys($edit)[0];

        if (count($edit[$table]) === 0) {
            throw new \InvalidArgumentException(
                'Missing element information (zero items in $edit[' . $table . '])',
                1579267718
            );
        }

        if ((int)array_keys($edit[$table])[0] < 0) {
            $defVals[$table]['pid'] = array_keys($edit[$table])[0];
        } else {
            $defVals[$table]['pid'] = $pid;
        }
        $uid = 'NEW' . uniqid();

        $data = [
            $table => [
                $uid => $defVals[$table]
            ]
        ];

        $dataHandler->start($data, []);
        $dataHandler->process_datamap();

        if (empty($dataHandler->errorLog)) {
            $this->writeSuccessMessage(
                LocalizationUtility::translate(
                    'notifications.new.content.success',
                    'FrontendEditing'
                )
            );
        } else {
            $this->writeErrorMessage(
                LocalizationUtility::translate(
                    'notifications.new.content.fail',
                    'FrontendEditing'
                )
            );
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
            $data[$table][$uid][$tcaCtrl['enablecolumns']['disabled']] = $hide ? 1 : 0;
            $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
            $dataHandler->start($data, []);
            $dataHandler->process_datamap();

            if (empty($dataHandler->errorLog)) {
                $this->writeSuccessMessage('Content ' . ($hide ? 'hidden' : 'visible') . ' (' . $uid . ')');
            } else {
                $this->writeErrorMessage(
                    'Content could not be set ' . ($hide ? 'hidden' : 'visible') . ' (' . $uid . ')'
                );
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
            $this->writeSuccessMessage('The content "' . $uid .
                '" is currently edited by someone else. Do you want to save this?');
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
    public function moveAction(
        string $table,
        int $uid,
        int $beforeUid = 0,
        int $pid = 0,
        int $columnPosition = -2,
        array $defaultValues = [],
        int $container = 0
    ) {
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

        // Add default values to datamap array
        $data[$table][$uid] = isset($data[$table][$uid])
            ? array_merge($data[$table][$uid], $defaultValues) : $defaultValues;

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
