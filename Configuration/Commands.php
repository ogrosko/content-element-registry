<?php

return [
    'dw:contentElement' => [
        'class' => \Digitalwerk\ContentElementRegistry\Command\CreateCommand\ContentElement::class,
        'schedulable' => false,
    ],
    'dw:pageType' => [
        'class' => \Digitalwerk\ContentElementRegistry\Command\CreateCommand\PageType::class,
        'schedulable' => false,
    ],
    'dw:plugin' => [
        'class' => \Digitalwerk\ContentElementRegistry\Command\CreateCommand\Plugin::class,
        'schedulable' => false,
    ],
    'dw:run' => [
        'class' => \Digitalwerk\ContentElementRegistry\Command\CreateCommand\Run::class,
        'schedulable' => false,
    ],
];
