<?php
namespace Digitalwerk\ContentElementRegistry\Command\CreateCommand;

use Digitalwerk\ContentElementRegistry\Utility\FieldsCreateCommandUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Plugin
 * @package Digitalwerk\ContentElementRegistry\Command\CreateCommand
 */
class PluginCreateCommand extends Command
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

        $fields = GeneralUtility::makeInstance(FieldsCreateCommandUtility::class)->generateObject($fields, '');

        $render = GeneralUtility::makeInstance(RenderCreateCommand::class);
        $render->setExtensionName($extensionName);
        $render->setFields($fields);
        $render->setName($pluginName);
        $render->setStaticName($pluginName);
        $render->setElementType('Plugin');
        $render->setOutput($output);
        $render->setInput($input);
        $render->setControllerName($controllerName);
        $render->setActionName($actionName);
        $render->setTitle($pluginTitle);

        $render->flexForm()->pluginTemplate();
        $render->controller()->template();
        $render->template()->pluginTemplate();
        $render->typoScript()->addPluginToWizard();
        $render->register()->plugin();
        $render->register()->pluginFlexForm();
        $render->icon()->copyPluginDefaultIcon();
        $render->previewImage()->copyPluginDefault();
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

        $output->writeln('<bg=green;options=bold>Plugin ' . $pluginName . ' was created.</>');
        $output->writeln('<bg=red;options=bold>• Fill template: public/typo3conf/ext/dw_page_types/Resources/Private/Templates/' . $controllerName . '/' . ucfirst($actionName) . '.html</>');
        $output->writeln('<bg=red;options=bold>• Change Plugin Icon.</>');
        $output->writeln('<bg=red;options=bold>• Change Plugin Preview image.</>');
    }
}
