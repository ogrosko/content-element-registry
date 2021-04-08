<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:content_element_registry/Resources/Private/Language/locallang_db.xlf:tx_contentelementregistry_domain_model_relation',
        'label' => 'title',
        'label_alt' => 'type',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'versioningWS' => true,
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'delete' => 'deleted',
        'sortby' => 'sorting',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'searchFields' => 'type,title,description,files',
        'iconfile' => 'EXT:content_element_registry/Resources/Public/Icons/tx_contentelementregistry_domain_model_relation.svg',
        'hideTable' => true,
        'type' => 'type',
        'typeicon_column' => 'type',
        'typeicon_classes' => [
            '' => 'actions-swap',
        ]
    ],
    'types' => [
        '0' => [
            'showitem' => 'type,
                       --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access, hidden, starttime, endtime, sys_language_uid, l10n_parent, l10n_diffsource'
        ],
    ],
    'columns' => [
        'sys_language_uid' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'special' => 'languages',
                'items' => [
                    [
                        'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.allLanguages',
                        -1,
                        'flags-multiple'
                    ]
                ],
                'default' => 0,
            ],
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'default' => 0,
                'items' => [
                    ['', 0],
                ],
                'foreign_table' => 'tx_contentelementregistry_domain_model_relation',
                'foreign_table_where' => 'AND tx_contentelementregistry_domain_model_relation.pid=###CURRENT_PID### AND tx_contentelementregistry_domain_model_relation.sys_language_uid IN (-1,0)',
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        't3ver_label' => [
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.versionLabel',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
            ],
        ],
        'hidden' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
                'items' => [
                    '1' => [
                        '0' => 'LLL:EXT:lang/Resources/Private/Language/locallang_core.xlf:labels.enabled'
                    ]
                ],
            ],
        ],
        'starttime' => [
            'exclude' => true,
            'behaviour' => [
                'allowLanguageSynchronization' => true
            ],
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 13,
                'eval' => 'datetime',
                'default' => 0,
            ],
        ],
        'endtime' => [
            'exclude' => true,
            'behaviour' => [
                'allowLanguageSynchronization' => true
            ],
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 13,
                'eval' => 'datetime',
                'default' => 0,
                'range' => [
                    'upper' => mktime(0, 0, 0, 1, 1, 2038)
                ],
            ],
        ],

        'type' => [
            'exclude' => true,
            'label' => 'LLL:EXT:content_element_registry/Resources/Private/Language/locallang_db.xlf:tx_contentelementregistry_domain_model_relation.type',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['No type specified', ''],
                ],
                'size' => 1,
                'maxitems' => 1,
                'eval' => ''
            ],
        ],
        'title' => [
            'l10n_mode' => 'prefixLangTitle',
            'exclude' => true,
            'label' => 'LLL:EXT:content_element_registry/Resources/Private/Language/locallang_db.xlf:tx_contentelementregistry_domain_model_relation.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'description' => [
            'l10n_mode' => 'prefixLangTitle',
            'exclude' => true,
            'label' => 'LLL:EXT:content_element_registry/Resources/Private/Language/locallang_db.xlf:tx_contentelementregistry_domain_model_relation.description',
            'config' => [
                'type' => 'text',
                'rows' => 5,
                'eval' => 'trim'
            ],
        ],
        'media' => [
            'exclude' => true,
            'label' => 'LLL:EXT:content_element_registry/Resources/Private/Language/locallang_db.xlf:tx_contentelementregistry_domain_model_relation.media',
            'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                'media',
                [
                    'overrideChildTca' => [
                        'types' => [
                            '0' => [
                                'showitem' => '
                                    --palette--;;imageoverlayPalette,
                                    --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => [
                                'showitem' => '
                                    --palette--;;imageoverlayPalette,
                                    --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
                                'showitem' => '
                                    --palette--;;imageoverlayPalette,
                                    --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_AUDIO => [
                                'showitem' => '
                                    --palette--;;audioOverlayPalette,
                                    --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO => [
                                'showitem' => '
                                    --palette--;;videoOverlayPalette,
                                    --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION => [
                                'showitem' => '
                                    --palette--;;imageoverlayPalette,
                                    --palette--;;filePalette'
                            ]
                        ],
                    ],
                    'maxitems' => 1
                ]
            ),

        ],
        'content_element' => [
            'config' => [
                'type' => 'passthrough'
            ],
        ],
        'inline_relation' => [
            'label' => 'Content relation (Label should be always rewrited by subType)',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_contentelementregistry_domain_model_relation',
                'foreign_field' => 'content_element',
                'foreign_sortby' => 'sorting',
                'maxitems' => 9999,
                'appearance' => [
                    'useSortable' => true,
                    'collapseAll' => 1,
                    'levelLinksPosition' => 'top',
                    'showSynchronizationLink' => 1,
                    'showPossibleLocalizationRecords' => 1,
                    'showAllLocalizationLink' => 1
                ],
            ],
        ],
    ],
];
