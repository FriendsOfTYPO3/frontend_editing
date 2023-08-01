<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Frontend Editing',
    'description' => 'Enable editors to work with the content in the most intuitive way possible',
    'category' => 'fe',
    'state' => 'stable',
    'clearCacheOnLoad' => true,
    'author' => 'TYPO3 Community',
    'author_email' => 'typo3cms@typo3.org',
    'author_company' => '',
    'version' => '3.2.4',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-11.5.99',
            'rte_ckeditor' => '11.5.0-11.5.99',
            'viewpage' => '11.5.0-11.5.99',
        ],
        'conflicts' => [
            'feedit' => '',
        ],
        'suggests' => [],
    ],
];
