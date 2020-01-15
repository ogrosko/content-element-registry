<?php

return [
    'dw:contentElement' => [
        'class' => \Digitalwerk\DwBoilerplate\Command\CreateContentElement::class,
        'schedulable' => false,
    ],
    'dw:contentElementBasic' => [
        'class' => \Digitalwerk\DwBoilerplate\Command\CreateContentElementBasic::class,
        'schedulable' => false,
    ],
    'dw:contentElementAdvance' => [
        'class' => \Digitalwerk\DwBoilerplate\Command\CreateContentElementAdvance::class,
        'schedulable' => false,
    ],
    'dw:contentElementAdvanceIRRE' => [
        'class' => \Digitalwerk\DwBoilerplate\Command\CreateContentElementAdvanceIRRE::class,
        'schedulable' => false,
    ],
    'dw:pageType' => [
        'class' => \Digitalwerk\DwBoilerplate\Command\CreatePageType::class,
        'schedulable' => false,
    ],
    'dw:plugin' => [
        'class' => \Digitalwerk\DwBoilerplate\Command\CreatePlugin::class,
        'schedulable' => false,
    ],
];
