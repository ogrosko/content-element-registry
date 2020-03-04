<?php
namespace Digitalwerk\ContentElementRegistry\Command\CreateCommand;

use Digitalwerk\ContentElementRegistry\Utility\CreateCommand\FlexFormUtility;
use Digitalwerk\ContentElementRegistry\Utility\CreateCommand\TranslationUtility;
use Digitalwerk\ContentElementRegistry\Utility\FieldsUtility;
use Digitalwerk\ContentElementRegistry\Utility\GeneralCreateCommandUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Plugin
 * @package Digitalwerk\ContentElementRegistry\Command\CreateCommand
 */
class Plugin extends Command
{

    protected function configure()
    {
        $this->setDescription('Create basic content element.');
        $this->addArgument('name', InputArgument::REQUIRED,'Enter name of Plugin.');
        $this->addArgument('title', InputArgument::REQUIRED,'Enter title of Plugin.');
        $this->addArgument('description', InputArgument::REQUIRED,'Enter description of Plugin.');
        $this->addArgument('controller', InputArgument::REQUIRED,'Enter controller name of Plugin.');
        $this->addArgument('action', InputArgument::REQUIRED,'Enter action name of Plugin in controller.');
        $this->addArgument('fields', InputArgument::REQUIRED,'Enter fields of fields.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pluginName = $input->getArgument('name');
        $pluginTitle = $input->getArgument('title');
        $pluginDescription = $input->getArgument('description');
        $controllerName = $input->getArgument('controller');
        $actionName = $input->getArgument('action');
        $fields = $input->getArgument('fields');
        $extensionName = 'dw_page_types';

        $fields = GeneralUtility::makeInstance(FieldsUtility::class)->generateObject($fields, '');

        $render = GeneralUtility::makeInstance(Render::class);
        $render->setExtensionName($extensionName);
        $render->setFields($fields);
        $render->setName($pluginName);
        $render->setStaticName($pluginName);
        $render->setElementType('Plugin');
        $render->setOutput($output);
        $render->setInput($input);

        $render->flexForm()->pluginTemplate();

        $render->translation()->addStringToTranslation(
            'public/typo3conf/ext/' . $extensionName . '/Resources/Private/Language/locallang_db.xlf',
            "plugin." . strtolower($pluginName) . ".title",
            $pluginTitle
        );
        $render->translation()->addStringToTranslation(
            'public/typo3conf/ext/' . $extensionName . '/Resources/Private/Language/locallang_db.xlf',
            "plugin." . strtolower($pluginName) . ".description",
            $pluginDescription
        );

        $pluginController = 'public/typo3conf/ext/dw_page_types/Classes/Controller/' . $controllerName  . 'Controller.php';
        $pluginControllerContent = '<?php
declare(strict_types=1);
namespace Digitalwerk\DwPageTypes\Controller;

use Digitalwerk\DwBoilerplate\Controller\ActionController;

/**
 * Class ' . $controllerName . 'Controller
 * @package Digitalwerk\DwPageTypes\Controller
 */
class ' . $controllerName . 'Controller extends ActionController
{
    /**
     * ' . ucfirst($actionName) . ' action
     */
    public function ' . $actionName . 'Action()
    {

    }
}';

        $pluginTemplate = 'public/typo3conf/ext/dw_page_types/Resources/Private/Templates/' . $controllerName . '/' . ucfirst($actionName) . '.html';
        $pluginTemplateContent = '<html xmlns="http://www.w3.org/1999/xhtml" lang="en"
      xmlns:f="http://typo3.org/ns/TYPO3/Fluid/ViewHelpers"
      xmlns:v="http://typo3.org/ns/FluidTYPO3/Vhs/ViewHelpers"
      data-namespace-typo3-fluid="true">

<f:layout name="Default" />

<f:section name="Main">

</f:section>

</html>';
        $pluginIcon = 'public/typo3conf/ext/dw_page_types/Resources/Public/Icons/' . $pluginName . '.svg';
        $pluginIconEdited = 'EXT:dw_page_types/Resources/Public/Icons/' . $pluginName . '.svg';
        copy("public/typo3conf/ext/content_element_registry/Resources/Public/Icons/CEDefaultIcon.svg", $pluginIcon);

        $pluginPreviewImage = "public/typo3conf/ext/dw_boilerplate/Resources/Public/Images/ContentElementPreviews/plugins_".strtolower($pluginName).".png";
        copy("public/typo3conf/ext/dw_boilerplate/Resources/Public/Images/dummy.jpg", $pluginPreviewImage);

//        Register icon
        GeneralCreateCommandUtility::importStringInToFileAfterString(
            'public/typo3conf/ext/dw_page_types/ext_localconf.php',
            [
                "                '" . $pluginName . "',\n"
            ],
            '\Digitalwerk\DwBoilerplate\Utility\BoilerplateUtility::registerIcons(',
            1

        );

//        Configure plugin
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


//        Register plugin
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

//        Add plugin flexform
        if ($fields !== '-') {
            GeneralCreateCommandUtility::importStringInToFileAfterString(
                'public/typo3conf/ext/dw_page_types/Configuration/TCA/Overrides/tt_content.php',
                [
                    "\nBoilerplateUtility::addPluginFlexForm('dw_page_types', '" . $pluginName . "');\n"
                ],
                "'" . $pluginIconEdited . "'",
                1

            );
        }

        if (!file_exists('public/typo3conf/ext/dw_page_types/Resources/Private/Templates/' . $controllerName)) {
            mkdir('public/typo3conf/ext/dw_page_types/Resources/Private/Templates/' . $controllerName, 0777, true);
        }
        file_put_contents($pluginTemplate, $pluginTemplateContent);

        if (!file_exists('public/typo3conf/ext/dw_page_types/Classes/Controller/' . $controllerName . 'Controller.php')) {
            mkdir('public/typo3conf/ext/dw_page_types/Resources/Private/Templates/' . $controllerName, 0777, true);
            file_put_contents($pluginController, $pluginControllerContent);
        } else {
            GeneralCreateCommandUtility::importStringInToFileAfterString(
                'public/typo3conf/ext/dw_page_types/Classes/Controller/' . $controllerName . 'Controller.php',
                [
                    "
    /**
    * " . ucfirst($actionName) . " action
    */
    public function " . $actionName . "Action()
    {

    }
                    "
                ],
                "class " . $controllerName . "Controller extends ActionController",
                1

            );
        }
//        Add plugin to wizzard
        GeneralCreateCommandUtility::importStringInToFileAfterString(
            'public/typo3conf/ext/dw_boilerplate/Configuration/TSconfig/Page/Includes/Mod.tsconfig',
            [
"                        " . strtolower($pluginName) . " {
                            iconIdentifier = ". $pluginName . "
                            title = LLL:EXT:dw_page_types/Resources/Private/Language/locallang_db.xlf:plugin." . strtolower($pluginName) . ".title
                            description = LLL:EXT:dw_page_types/Resources/Private/Language/locallang_db.xlf:plugin." . strtolower($pluginName) . ".description
                            tt_content_defValues {
                                CType = list
                                list_type = dwpagetypes_" . strtolower($pluginName) . "
                            }
                        }\n"
            ],
            "plugins {",
            1

        );

        $output->writeln('<bg=green;options=bold>Plugin ' . $pluginName . ' was created.</>');
        $output->writeln('<bg=red;options=bold>• Fill template: public/typo3conf/ext/dw_page_types/Resources/Private/Templates/' . $controllerName . '/' . ucfirst($actionName) . '.html</>');
        $output->writeln('<bg=red;options=bold>• Change Plugin Icon.</>');
        $output->writeln('<bg=red;options=bold>• Change Plugin Preview image.</>');
    }
}
