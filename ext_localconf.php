<?php

defined('TYPO3_MODE') or die();

(static function ($extKey) {
    $iconRegistry = $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Core\Imaging\IconRegistry::class
    );
    $contentElementsRegistry = \Digitalwerk\ContentElementRegistry\Core\ContentElementRegistry::getInstance();

    if (\version_compare(\TYPO3\CMS\Core\Utility\VersionNumberUtility::getCurrentTypo3Version(), '10.0.0', '<')) {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(
            \Digitalwerk\ContentElementRegistry\Utility\ContentElementRegistryUtility::convertArrayToTypoScript(
                $contentElementsRegistry->getBaseTypoScriptPersistenceConfig(),
                'config.tx_extbase.persistence.classes'
            )
        );
    }

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
            \Digitalwerk\ContentElementRegistry\Utility\ContentElementRegistryUtility::convertArrayToTypoScript(
                $contentElement->getWizardPageTSconfig(),
                'mod.wizards.newContentElement.wizardItems'
            )
        );

        //Register CE rendering definition
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
            $extKey,
            'setup',
            \Digitalwerk\ContentElementRegistry\Utility\ContentElementRegistryUtility::convertArrayToTypoScript($contentElement->getTypoScriptConfiguration())
        );

        if (\version_compare(\TYPO3\CMS\Core\Utility\VersionNumberUtility::getCurrentTypo3Version(), '10.0.0', '<')) {
            //Add TypoScript setup for Extbase persistence mapping
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(
                \Digitalwerk\ContentElementRegistry\Utility\ContentElementRegistryUtility::convertArrayToTypoScript(
                    $contentElement->getTypoScriptPersistenceConfig(),
                    'config.tx_extbase.persistence.classes'
                )
            );
        }

        //Register CE preview template
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem'][$contentElement->getCType()] =
            \Digitalwerk\ContentElementRegistry\Hook\ContentElementPreviewRenderer::class;
    }
})('content_element_registry');
