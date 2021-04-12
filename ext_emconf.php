<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Frontend Editing',
    'description' => 'Enable editors to work with the content in the most intuitive way possible',
    'category' => 'fe',
    'state' => 'alpha',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 1,
    'author' => 'TYPO3 Community',
    'author_email' => 'typo3cms@typo3.org',
    'author_company' => '',
    'version' => '2.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '9.4.0-10.4.99',
            'rte_ckeditor' => '9.4.0-10.4.99',
            'viewpage' => '9.4.0-10.4.99',
        ],
        'conflicts' => [
            'feedit' => '',
        ],
        'suggests' => [],
    ],
];
