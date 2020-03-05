<?php
namespace Digitalwerk\ContentElementRegistry\Command\CreateCommand\Render;

use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Render;

/**
 * Class PreviewImage
 * @package Digitalwerk\ContentElementRegistry\Command\CreateCommand\Render
 */
class PreviewImage
{
    /**
     * @var Render
     */
    protected $render = null;

    /**
     * PreviewImage constructor.
     * @param Render $render
     */
    public function __construct(Render $render)
    {
        $this->render = $render;
    }

    public function copyContentElementDefault()
    {
        $extensionName = $this->render->getExtensionName();
        $name = $this->render->getName();
        copy(
            'public/typo3conf/ext/content_element_registry/Resources/Public/Images/NewContentElement1.png',
            'public/typo3conf/ext/' . $extensionName . '/Resources/Public/Images/ContentElementPreviews/common_' . str_replace('_', '', $extensionName) . '_' . strtolower($name) . '.png'
        );
    }

    public function copyPluginDefault()
    {
        copy(
            'public/typo3conf/ext/content_element_registry/Resources/Public/Images/NewContentElement1.png',
            "public/typo3conf/ext/dw_boilerplate/Resources/Public/Images/ContentElementPreviews/plugins_".strtolower($this->render->getName()).".png"
        );
    }
}
