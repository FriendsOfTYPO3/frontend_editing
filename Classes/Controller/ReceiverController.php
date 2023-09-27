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

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\FrontendEditing\Controller\Event\PrepareFieldUpdateEvent;
use UnexpectedValueException;

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
    protected ResponseInterface $response;

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

        $table = isset($request->getParsedBody()['table']) ? $request->getParsedBody()['table'] : '';
        $uid = isset($request->getParsedBody()['uid']) ? (int)$request->getParsedBody()['uid'] : 0;

        switch ($request->getMethod()) {
            case 'DELETE':
                $this->deleteAction($table, $uid);
                break;

            // modifying existing or creating new records
            case 'POST':
                $action = (isset($request->getQueryParams()['action'])) ? $request->getQueryParams()['action'] : '';
                switch ($action) {
                    case 'hide':
                        $this->hideAction(
                            $table,
                            $uid,
                            (bool)$request->getParsedBody()['hide']
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
     * @throws UnexpectedValueException
     */
    protected function updateAction(string $table, int $uid, string $field, string $content): void
    {
        $field = $this->sanitizeFieldName($field);

        $record = BackendUtility::getRecord($table, $uid);

        /** @var EventDispatcher $eventDispatcher */
        $eventDispatcher = GeneralUtility::makeInstance(EventDispatcher::class);
        $content = $eventDispatcher->dispatch(
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
        } catch (Exception) {
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
    protected function deleteAction(string $table, int $uid): void
    {
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->BE_USER = $GLOBALS['BE_USER'];
        // Delete the record
        $dataHandler->deleteAction($table, $uid);
        $translateKey = 'notifications.delete.content.';
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
     * Hide a record through the data handler
     *
     * @param string $table
     * @param int $uid
     * @param bool $hide
     */
    protected function hideAction(string $table, int $uid, bool $hide): void
    {
        $tcaCtrl = $GLOBALS['TCA'][$table]['ctrl'];
        if (isset($tcaCtrl['enablecolumns']['disabled'])) {
            $data = [];
            $data[$table][$uid][$tcaCtrl['enablecolumns']['disabled']] = $hide ? 1 : 0;
            $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
            $dataHandler->start($data, []);
            $dataHandler->process_datamap();

            $translateKey = 'notifications.' . ($hide ? 'hidden' : 'visible') . '.content.';
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
    protected function lockedRecordAction(string $table, int $uid): void
    {
        $lockedRecord = BackendUtility::isRecordLocked($table, $uid);
        if ($lockedRecord) {
            $this->writeSuccessMessage(
                $lockedRecord['msg'],
                LocalizationUtility::translate(
                    'notifications.locked_record',
                    'FrontendEditing',
                    [$uid]
                )
            );
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
     * @param string|null $title
     */
    protected function writeSuccessMessage(string $message, string $title = null): void
    {
        $title = $title ?: $message;
        $message = [
            'success' => true,
            'title' => $title,
            'message' => $message
        ];
        $this->response->getBody()->write(json_encode($message));
    }

    /**
     * Ensure the message is printed and encoded as JSON
     *
     * @param string $message
     */
    protected function writeErrorMessage(string $message): void
    {
        $message = [
            'success' => false,
            'message' => $message
        ];
        $this->response->getBody()->write(json_encode($message));
    }
}
