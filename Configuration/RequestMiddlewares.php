<?php

use TYPO3\CMS\FrontendEditing\Middleware\FrontendEditingSetupSimulator;
use TYPO3\CMS\FrontendEditing\Middleware\FrontendEditingInitiator;

/**
 * An array consisting of implementations of middlewares for a middleware stack to be registered
 */
return [
    'frontend' => [
        'typo3/frontendediting/setup-simulator' => [
            'target' => FrontendEditingSetupSimulator::class,
            'after' => [
                'typo3/cms-frontend/page-resolver',
            ],
            'before' => [
                'typo3/cms-frontend/preview-simulator',
            ],
        ],
        'typo3/frontendediting/initiator' => [
            'target' => FrontendEditingInitiator::class,
            'after' => [
                'typo3/cms-frontend/shortcut-and-mountpoint-redirect',
            ],
            'before' => [
                'typo3/cms-frontend/content-length-headers',
            ],
        ],
    ],
];
