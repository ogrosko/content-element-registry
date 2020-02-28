<?php
namespace Digitalwerk\ContentElementRegistry\Command\CreateCommand;

use Digitalwerk\ContentElementRegistry\Utility\FieldsUtility;
use Digitalwerk\ContentElementRegistry\Utility\GeneralCreateCommandUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class PageType
 * @package Digitalwerk\ContentElementRegistry\Command\CreateCommand
 */
class PageType extends Command
{

    protected function configure()
    {
        $this->setDescription('Create page type with some fields.');
        $this->addArgument('table', InputArgument::REQUIRED,'Enter table of PageType.');
        $this->addArgument('name', InputArgument::REQUIRED,'Enter name of PageType.');
        $this->addArgument('title', InputArgument::REQUIRED,'Enter title of PageType.');
        $this->addArgument('doktype', InputArgument::REQUIRED,'Enter doktype of PageType.');
        $this->addArgument('auto-header', InputArgument::REQUIRED,'Set true, if auto generating header is needed.');
        $this->addArgument('fields', InputArgument::REQUIRED,'Add new fields. format: "fieldName,fieldType,fieldTitle/"');
        $this->addArgument('inline-fields',InputArgument::IS_ARRAY ,'');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doktype = $input->getArgument('doktype');
        $pageTypeName = $input->getArgument('name');
        $pageTypeTitle = $input->getArgument('title');
        $autoHeader = $input->getArgument('auto-header');
        $fields = $input->getArgument('fields');
        $table = $input->getArgument('table');
        $inlineFields = $input->getArgument('inline-fields');
        $extensionName = 'dw_page_types';
        $namespaceToContentElementModel = 'Digitalwerk\DwPageTypes\Domain\Model';
        $relativePathToModel = 'dw_page_types/Classes/Domain/Model';


        $fields = GeneralUtility::makeInstance(FieldsUtility::class)->generateObject($fields, $table);

        $render = GeneralUtility::makeInstance(Render::class);
        $render->setExtensionName($extensionName);
        $render->setFields($fields);
        $render->setInlineRelativePath($relativePathToModel);
        $render->setName($pageTypeName);
        $render->setTable($table);
        $render->setInlineFields($inlineFields);
        $render->setModelNamespace($namespaceToContentElementModel);
        $render->setStaticName($pageTypeName);
        $render->setDoktype($doktype);
        $render->setInput($input);
        $render->setOutput($output);

        $render->icon()->copyPageTypeDefaultIcon();
        $render->model()->pageTypeTemplate();
        $render->tca()->pageTypeTemplate();
        $render->inline()->render();
        $render->typoScript()->pageTypeTypoScriptRegister();

        $render->translation()->addFieldsTitleToTranslation(
            'public/typo3conf/ext/' . $extensionName . '/Resources/Private/Language/locallang_db.xlf'
        );
        $render->translation()->addStringToTranslation(
            'public/typo3conf/ext/' . $extensionName . '/Resources/Private/Language/locallang_db.xlf',
            'page.type.'. $doktype . '.label',
            $pageTypeTitle
        );
        $render->register()->pageTypeToExtTables();
        $render->sqlDatabase()->fields();
        GeneralCreateCommandUtility::checkDefaultTemplateOptionalAndAddAutoHeader($autoHeader, $pageTypeName);

        $output->writeln('<bg=green;options=bold>Page type ' . $pageTypeName . ' was created.</>');
        $output->writeln('<bg=red;options=bold>• Update/Compare Typo3 database.</>');
        $output->writeln('<bg=red;options=bold>• Change PageType Icon.</>');
        if ($autoHeader === 'true') {
            $output->writeln('<bg=red;options=bold>• Fill auto header template: public/typo3conf/ext/dw_boilerplate/Resources/Private/Partials/PageType</>');
        }
    }
}
