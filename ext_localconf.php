<?php

use Digitalwerk\ContentElementRegistry\Core\ContentElementRegistry;
use Digitalwerk\ContentElementRegistry\Hook\ContentElementPreviewRenderer;
use Digitalwerk\ContentElementRegistry\Utility\ContentElementRegistryUtility;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3_MODE') or die();

call_user_func(
    function ($extKey) {

        $iconRegistry = $iconRegistry = GeneralUtility::makeInstance(
            IconRegistry::class
        );
        $contentElementsRegistry = ContentElementRegistry::getInstance();

        foreach ($contentElementsRegistry->getContentElements() as $contentElement) {
            //Register CE icon
            $iconRegistry->registerIcon(
                $contentElement->getIconIdentifier(),
                SvgIconProvider::class,
                [
                    'source' => $contentElement->getIconSrcPath(),
                ]
            );

            //Register CE wizard item
            ExtensionManagementUtility::addPageTSConfig(
                ContentElementRegistryUtility::convertArrayToTypoScript(
                    $contentElement->getWizardPageTSconfig(),
                    'mod.wizards.newContentElement.wizardItems'
                )
            );

            //Register CE rendering definition
            ExtensionManagementUtility::addTypoScript(
                $extKey,
                'setup',
                ContentElementRegistryUtility::convertArrayToTypoScript($contentElement->getTypoScriptConfiguration())
            );


            //Register CE preview template
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem'][$contentElement->getCType()] =
                ContentElementPreviewRenderer::class;
        }
    },
    'content_element_registry'
);
