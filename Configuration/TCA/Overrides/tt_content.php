<?php

use Digitalwerk\ContentElementRegistry\Core\ContentElementRegistry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

$contentElementsRegistry = ContentElementRegistry::getInstance();

$ceGroup = '';
foreach ($contentElementsRegistry->getContentElements() as $contentElement) {
    //Add CE CType select group
    if ($contentElement->getGroupName() !== $ceGroup) {
        $ceGroup = $contentElement->getGroupName();
        ExtensionManagementUtility::addTcaSelectItem(
            'tt_content',
            'CType',
            [
                'label' => $contentElement->getGroupLabel(),
                'value' => '--div--'
            ],
            'text',
            'before'
        );
    }

    //Add CE CType in tt_content TCA select item
    ExtensionManagementUtility::addTcaSelectItem(
        'tt_content',
        'CType',
        [
            'label' => $contentElement->getTitle(),
            'value' => $contentElement->getCType(),
            'icon' => $contentElement->getIconIdentifier()
        ],
        'text',
        'before'
    );

    // Add CE palettes
    foreach ($contentElement->getPalettes() as $paletteName => $palette) {
        $GLOBALS['TCA']['tt_content']['palettes'][$paletteName] = $palette;
    }

    // Add CE FlexForm
    if ($contentElement->flexFormDefinitionExists()) {
        $GLOBALS['TCA']['tt_content']['columns']['pi_flexform']['config']['ds']["*,{$contentElement->getIdentifier()}"] =
            $contentElement->getFlexFormFormDefinition();
    }

    //Add CE type
    $GLOBALS['TCA']['tt_content']['types'][$contentElement->getCType()] = [
        'showitem' => $contentElement->getTCAShowItemConfig(),
        'columnsOverrides' => $contentElement->getColumnsOverrides(),
    ];
}


$tmpColumns = [
    'tx_contentelementregistry_relations' => [
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
];

ExtensionManagementUtility::addTCAcolumns('tt_content', $tmpColumns);
