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
    'dw:defaultFieldTypes' => [
        'class' => \Digitalwerk\ContentElementRegistry\Command\CreateCommand\DefaultFieldTypes::class,
        'schedulable' => false,
    ],
    'dw:plugin' => [
        'class' => \Digitalwerk\ContentElementRegistry\Command\CreateCommand\Plugin::class,
        'schedulable' => false,
    ],
];
