<?php

use Digitalwerk\ContentElementRegistry\Core\ContentElementRegistry;
use Digitalwerk\ContentElementRegistry\Hook\ContentElementPreviewRenderer;
use Digitalwerk\ContentElementRegistry\Utility\ContentElementRegistryUtility;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3') or die();

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
        }
    },
    'content_element_registry'
);
