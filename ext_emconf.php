<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Content element registry',
    'description' => 'Helper for creating typo3 content elements',
    'category' => 'fe',
    'author' => 'Ondrej Grosko',
    'author_email' => 'ondrej@digitalwerk.agency',
    'author_company' => 'Digitalwerk',
    'state' => 'stable',
    'version' => '10.1.0',
    'constraints' => [
        'depends' => [
            'typo3' => '<12',
            'php' => '<=8',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'Digitalwerk\\ContentElementRegistry\\' => 'Classes'
        ]
    ],
];
