<?php

return [
    'dw:contentElement' => [
        'class' => \Digitalwerk\ContentElementRegistry\Command\CreateContentElement::class,
        'schedulable' => false,
    ],
    'dw:contentElementBasic' => [
        'class' => \Digitalwerk\ContentElementRegistry\Command\CreateContentElementBasic::class,
        'schedulable' => false,
    ],
    'dw:contentElementAdvance' => [
        'class' => \Digitalwerk\ContentElementRegistry\Command\CreateContentElementAdvance::class,
        'schedulable' => false,
    ],
    'dw:contentElementAdvanceIRRE' => [
        'class' => \Digitalwerk\ContentElementRegistry\Command\CreateContentElementAdvanceIRRE::class,
        'schedulable' => false,
    ],
    'dw:pageType' => [
        'class' => \Digitalwerk\ContentElementRegistry\Command\CreatePageType::class,
        'schedulable' => false,
    ],
    'dw:plugin' => [
        'class' => \Digitalwerk\ContentElementRegistry\Command\CreatePlugin::class,
        'schedulable' => false,
    ],
];
