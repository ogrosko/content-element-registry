<?php

return [
    'dw:contentElement' => [
        'class' => \Digitalwerk\ContentElementRegistry\Command\CreateCommand\ContentElementCreateCommand::class,
        'schedulable' => false,
    ],
    'dw:pageType' => [
        'class' => \Digitalwerk\ContentElementRegistry\Command\CreateCommand\PageTypeCreateCommand::class,
        'schedulable' => false,
    ],
    'dw:plugin' => [
        'class' => \Digitalwerk\ContentElementRegistry\Command\CreateCommand\PluginCreateCommand::class,
        'schedulable' => false,
    ],
    'dw:run' => [
        'class' => \Digitalwerk\ContentElementRegistry\Command\CreateCommand\RunCreateCommand::class,
        'schedulable' => false,
    ],
];
