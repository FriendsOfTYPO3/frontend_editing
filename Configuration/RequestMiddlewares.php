<?php
/**
 * An array consisting of implementations of middlewares for a middleware stack to be registered
 *
 */
return [
    'frontend' => [
        'typo3/frontendediting/initiator' => [
            'target' => \TYPO3\CMS\FrontendEditing\Middleware\FrontendEditingInitiator::class,
            'after' => [
                'typo3/cms-adminpanel/initiator',
                'typo3/cms-frontend/page-resolver',
            ],
        ],
        'typo3/frontendediting/aspect' => [
            'target' => \TYPO3\CMS\FrontendEditing\Middleware\FrontendEditingAspect::class,
            'after' => [
                'typo3/frontendediting/initiator',
            ],
            'before' => [
                'typo3/cms-frontend/prepare-tsfe-rendering',
            ],
        ],
    ],

    'backend' => [
        'typo3/frontendediting/backeenduser-redirect-to-fe' => [
            'target' => \TYPO3\CMS\FrontendEditing\Middleware\BackendUserRedirectToFrontend::class,
            'after' => [
                'typo3/cms-backend/authentication'
            ],
        ],
    ],
];
