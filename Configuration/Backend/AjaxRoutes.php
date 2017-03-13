<?php
/**
 * Definitions for routes provided by EXT:frontend_editing
 * Contains all AJAX-based routes for entry points
 */
return [
    // Process content update requests
    'frontendediting_process' => [
        'path' => '/frontend-editing/process',
        'target' => \TYPO3\CMS\FrontendEditing\Controller\ReceiverController::class . '::processRequest'
    ],
    // Load CKEditor configuration per record on demand
    'frontendediting_editorconfiguration' => [
        'path' => '/frontend-editing/editor-configuration',
        'target' => \TYPO3\CMS\FrontendEditing\Controller\EditorController::class . '::getConfigurationAction'
    ],
];
