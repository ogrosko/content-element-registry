<?php
defined('TYPO3_MODE') or die();

$contentElementsRegistry = \Digitalwerk\ContentElementRegistry\Core\ContentElementRegistry::getInstance();

$ceGroup = '';
/** @var \Digitalwerk\ContentElementRegistry\ContentElement\AbstractContentElementRegistryItem $contentElement */
foreach ($contentElementsRegistry->getContentElements() as $contentElement) {
    //Add CE CType select group
    if ($contentElement->getGroupName() !== $ceGroup) {
        $ceGroup = $contentElement->getGroupName();
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem(
            'tt_content',
            'CType',
            [
                $contentElement->getGroupLabel(),
                '--div--'
            ],
            'text',
            'before'
        );
    }

    //Add CE CType in tt_content TCA select item
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem(
        'tt_content',
        'CType',
        [
            $contentElement->getTitle(),
            $contentElement->getCType(),
            $contentElement->getIconIdentifier()
        ],
        'text',
        'before'
    );

    // Add CE palettes
    foreach ($contentElement->getPalettes() as $paletteName => $palette) {
        $GLOBALS['TCA']['tt_content']['palettes'][$paletteName] = $palette;
    }

    //Add CE type
    $GLOBALS['TCA']['tt_content']['types'][$contentElement->getCType()] = [
        'showitem' => $contentElement->getTCAShowItemConfig(),
        'columnsOverrides' => $contentElement->getColumnsOverrides(),
    ];
}
