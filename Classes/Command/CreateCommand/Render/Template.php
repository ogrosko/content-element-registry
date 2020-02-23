<?php
namespace Digitalwerk\ContentElementRegistry\Command\CreateCommand\Render;

use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Render;

/**
 * Class Template
 * @package Digitalwerk\ContentElementRegistry\Command\CreateCommand\Render
 */
class Template
{
    /**
     * @var null
     */
    protected $render = null;

    public function __construct(Render $render)
    {
        $this->render = $render;
    }

    public function template()
    {
        file_put_contents(
            'public/typo3conf/ext/' . $this->render->getExtensionName() . '/Resources/Private/Templates/ContentElements/' . $this->render->getName() . '.html',
            '<html xmlns="http://www.w3.org/1999/xhtml" lang="en"
      xmlns:f="http://typo3.org/ns/TYPO3/Fluid/ViewHelpers"
      xmlns:v="http://typo3.org/ns/FluidTYPO3/Vhs/ViewHelpers"
      data-namespace-typo3-fluid="true">

<f:layout name="ContentElements/{contentElement.layout}" />

<f:section name="Main">
</f:section>

<f:section name="Preview">
</f:section>

</html>'
        );
    }
}
