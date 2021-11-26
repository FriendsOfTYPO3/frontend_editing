<?php

use TYPO3\CMS\FrontendEditing\Middleware\FrontendEditingInitiator;
use TYPO3\CMS\FrontendEditing\Middleware\FrontendEditingAspect;
use TYPO3\CMS\FrontendEditing\Middleware\FrontendEditing;
/**
 * An array consisting of implementations of middlewares for a middleware stack to be registered
 */
return [
    'frontend' => [
        'typo3/frontendediting/initiator' => [
            'target' => FrontendEditingInitiator::class,
            'after' => [
                'typo3/cms-adminpanel/initiator',
                'typo3/cms-frontend/page-resolver',
            ],
        ],
        'typo3/frontendediting/aspect' => [
            'target' => FrontendEditingAspect::class,
            'after' => [
                'typo3/frontendediting/initiator',
            ],
            'before' => [
                'typo3/cms-frontend/prepare-tsfe-rendering',
            ],
        ],
        'typo3/frontendediting/frontendediting' => [
            'target' => FrontendEditing::class,
            'after' => [
                'typo3/cms-frontend/prepare-tsfe-rendering',
            ],
            'before' => [
                'typo3/cms-frontend/shortcut-and-mountpoint-redirect',
            ],
        ],
    ],
];
