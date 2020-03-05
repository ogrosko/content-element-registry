<?php
namespace Digitalwerk\ContentElementRegistry\Command\CreateCommand\Render;

use Digitalwerk\ContentElementRegistry\Command\CreateCommand\RenderCreateCommand;
use Digitalwerk\ContentElementRegistry\Utility\GeneralCreateCommandUtility;

/**
 * Class Register
 * @package Digitalwerk\ContentElementRegistry\Command\CreateCommand\Render
 */
class RegisterRender
{
    /**
     * @var RenderCreateCommand
     */
    protected $render = null;

    public function __construct(RenderCreateCommand $render)
    {
        $this->render = $render;
    }

    public function pageTypeToExtTables()
    {
        $pageTypeName = $this->render->getName();
        $extensionName = $this->render->getExtensionName();

        GeneralCreateCommandUtility::importStringInToFileAfterString(
            'public/typo3conf/ext/' . $extensionName . '/ext_tables.php',
            [
                "        Digitalwerk\DwPageTypes\Utility\PageTypeUtility::addPageDoktype(" . $pageTypeName . "::getDoktype()); \n"
            ],
            'call_user_func(',
            1
        );

        GeneralCreateCommandUtility::importStringInToFileAfterString(
            'public/typo3conf/ext/' . $extensionName . '/ext_tables.php',
            [
                "\nuse " . $this->render->getModelNamespace() . "\\" . $pageTypeName . ";"
            ],
            '',
            -1

        );
    }

    public function pluginFlexForm()
    {
        if ($this->render->getFields()) {
            $pluginName = $this->render->getName();
            $extensionName = $this->render->getExtensionName();
            $pluginIconEdited = 'EXT:' . $extensionName . '/Resources/Public/Icons/' . $pluginName . '.svg';
            GeneralCreateCommandUtility::importStringInToFileAfterString(
                'public/typo3conf/ext/dw_page_types/Configuration/TCA/Overrides/tt_content.php',
                [
                    "\nBoilerplateUtility::addPluginFlexForm('" . $extensionName . "', '" . $pluginName . "');\n"
                ],
                "'" . $pluginIconEdited . "'",
                1

            );
        }
    }

    public function plugin()
    {
        $pluginName = $this->render->getName();
        $extensionName = $this->render->getExtensionName();
        $pluginIconEdited = 'EXT:' . $extensionName . '/Resources/Public/Icons/' . $pluginName . '.svg';
        $pluginTitle = $this->render->getTitle();
        $controllerName = $this->render->getControllerName();
        $actionName = $this->render->getActionName();

        GeneralCreateCommandUtility::importStringInToFileAfterString(
            'public/typo3conf/ext/dw_page_types/Configuration/TCA/Overrides/tt_content.php',
            [
                "
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
'Digitalwerk.DwPageTypes',
'" . $pluginName . "',
'" . str_replace('-',' ',$pluginTitle) . "',
'" . $pluginIconEdited . "'
);
"
            ],
            'defined(\'TYPO3_MODE\') or die();',
            0
        );

        GeneralCreateCommandUtility::importStringInToFileAfterString(
            'public/typo3conf/ext/dw_page_types/ext_localconf.php',
            [
                "
        /**
         * " . str_replace('-',' ',$pluginTitle) . "
        */
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
         'Digitalwerk.DwPageTypes',
          '" . $pluginName . "',
          ['" . $controllerName . "' => '". strtolower($actionName) . "'],
          ['" . $controllerName . "' => '']
        );
"
            ],
            'call_user_func(',
            1

        );
    }
}
