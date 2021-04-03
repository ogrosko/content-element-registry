<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Content element registry',
    'description' => 'Helper for creating typo3 content elements',
    'category' => 'fe',
    'author' => 'Ondrej Grosko',
    'author_email' => 'ondrej@digitalwerk.agency',
    'author_company' => 'Digitalwerk',
    'state' => 'stable',
    'version' => '1.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '>=10.4.999',
            'php' => '7.3.0-7.4.999',
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
