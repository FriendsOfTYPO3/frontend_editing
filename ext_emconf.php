<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Frontend Editing',
    'description' => 'Enable editors to work with the content in the most intuitive way possible',
    'category' => 'fe',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 1,
    'author' => 'TYPO3 Community',
    'author_email' => 'typo3cms@typo3.org',
    'author_company' => '',
    'version' => '1.9.12',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.0-10.4.99',
            'rte_ckeditor' => '8.7.0-10.4.99'
        ],
        'conflicts' => [
            'feedit' => '',
        ],
        'suggests' => [
            'cs_seo' => '2.1.0-2.1.99'
        ],
    ],
];
