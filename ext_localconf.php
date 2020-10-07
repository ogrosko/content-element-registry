<?php

use Digitalwerk\ContentElementRegistry\Utility\ContentElementRegistryUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

defined('TYPO3_MODE') or die();

call_user_func(
    function ($extKey) {

        $iconRegistry = $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Imaging\IconRegistry::class
        );
        $contentElementsRegistry = \Digitalwerk\ContentElementRegistry\Core\ContentElementRegistry::getInstance();

        /** @var \Digitalwerk\ContentElementRegistry\ContentElement\AbstractContentElementRegistryItem $contentElement */
        foreach ($contentElementsRegistry->getContentElements() as $contentElement) {
            //Register CE icon
            $iconRegistry->registerIcon(
                $contentElement->getIconIdentifier(),
                \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
                [
                    'source' => $contentElement->getIconSrcPath(),
                ]
            );

            //Register CE wizard item
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
                ContentElementRegistryUtility::convertArrayToTypoScript(
                    $contentElement->getWizardPageTSconfig(),
                    'mod.wizards.newContentElement.wizardItems'
                )
            );

            //Register CE rendering definition
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
                $extKey,
                'setup',
                ContentElementRegistryUtility::convertArrayToTypoScript($contentElement->getTypoScriptConfiguration())
            );


            //Register CE preview template
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem'][$contentElement->getCType()] =
                \Digitalwerk\ContentElementRegistry\Hook\ContentElementPreviewRenderer::class;
        }
    },
    'content_element_registry'
);
