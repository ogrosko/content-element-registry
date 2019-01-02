<?php
defined('TYPO3_MODE') or die();

call_user_func(
    function ($extKey) {

        $iconRegistry = $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Imaging\IconRegistry::class
        );
        $contentElementsRegistry = \Digitalwerk\ContentElementRegistry\Core\ContentElementRegistry::getInstance();
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(
            $contentElementsRegistry->getBaseTypoScriptPersistenceConfig()
        );

        /** @var \Digitalwerk\ContentElementRegistry\ContentElement\AbstractContentElementRegistryItem $contentElement */
        foreach ($contentElementsRegistry->getContentElements() as $contentElement) {
            //Register CE icon
            $iconRegistry->registerIcon(
                $contentElement->getIconIdentifier(),
                \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
                [
                    'source' => $contentElement->getIconPath(),
                ]
            );

            //Register CE wizard item
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig($contentElement->getPageTSconfig());

            //Register CE rendering definition
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
                $extKey,
                'setup',
                $contentElement->getTypoScriptConfiguration()
            );

            //Add TypoScript setup for Extbase persistence mapping
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(
                $contentElement->getTypoScriptPersistenceConfig()
            );

            //Register CE preview template
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem'][$contentElement->getCType()] =
                \Digitalwerk\ContentElementRegistry\Hook\ContentElementPreviewRenderer::class;
        }
    },
    $_EXTKEY
);
