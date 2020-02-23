<?php
namespace Digitalwerk\ContentElementRegistry\Command\CreateCommand\Render;

use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Render;
use Digitalwerk\ContentElementRegistry\Utility\GeneralCreateCommandUtility;

/**
 * Class Icon
 * @package Digitalwerk\ContentElementRegistry\Command\CreateCommand\Render
 */
class Icon
{
    /**
     * @var Render
     */
    protected $render = null;

    public function __construct(Render $render)
    {
        $this->render = $render;
    }

    public function copyContentElementDefaultIcon()
    {
        $extensionName = $this->render->getExtensionName();
        $name = $this->render->getName();
        copy(
            'public/typo3conf/ext/content_element_registry/Resources/Public/Icons/CEDefaultIcon.svg',
            'public/typo3conf/ext/' . $extensionName . '/Resources/Public/Icons/ContentElement/' . str_replace('_', '', $extensionName) . '_' . strtolower($name) . '.svg'
        );
    }

    public function registerIcon()
    {
        $staticName = $this->render->getStaticName();
        $name = $this->render->getName();
        $extensionName = $this->render->getExtensionName();

        GeneralCreateCommandUtility::importStringInToFileAfterString(
            'public/typo3conf/ext/' . $extensionName . '/ext_localconf.php',
            [
                "                'ContentElement/" . str_replace('_', '', $extensionName) . "_" . strtolower($staticName) . "_" . strtolower($name) . "', \n"
            ],
            [
                "'GridElement/2ColumnGrid',",
                "'GridElement/AccordionContainer',",
                "'GridElement/AccordionContainerItem',",
            ]
        );
    }

    public function copyAndRegisterInlineDefaultIcon()
    {
        $extensionName = $this->render->getExtensionName();
        copy(
            'public/typo3conf/ext/content_element_registry/Resources/Public/Icons/CEDefaultIcon.svg',
            'public/typo3conf/ext/' . $extensionName . '/Resources/Public/Icons/ContentElement/' .
            str_replace('_', '', $extensionName) . '_' .
            strtolower($this->render->getStaticName()) . '_'.
            strtolower($this->render->getName()) . '.svg'
        );

        $this->registerIcon();
    }

    public function copyPageTypeDefaultIcon()
    {
        $extensionName = $this->render->getExtensionName();
        $doktype = $this->render->getDoktype();

        copy(
            'public/typo3conf/ext/content_element_registry/Resources/Public/Icons/CEDefaultIcon.svg',
            'public/typo3conf/ext/' . $extensionName . '/Resources/Public/Icons/' . str_replace('_', '-', $extensionName) . '-' . $doktype . '.svg'
        );
        copy(
            'public/typo3conf/ext/content_element_registry/Resources/Public/Icons/CEDefaultIcon.svg',
            'public/typo3conf/ext/' . $extensionName . '/Resources/Public/Icons/' . str_replace('_', '-', $extensionName) . '-' . $doktype . '-not-in-menu.svg'
        );
    }
}
