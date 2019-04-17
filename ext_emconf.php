<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Content element registry',
    'description' => 'Helper for creating typo3 content elements',
    'category' => 'fe',
    'author' => 'Ondrej Grosko',
    'author_email' => 'ondrej@digitalwerk.agency',
    'author_company' => 'Digitalwerk',
    'state' => 'beta',
    'version' => '0.0.4',
    'constraints' => [
        'depends' => [
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
