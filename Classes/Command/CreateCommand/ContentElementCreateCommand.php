<?php
namespace Digitalwerk\ContentElementRegistry\Command\CreateCommand;

use Digitalwerk\ContentElementRegistry\Utility\FieldsCreateCommandUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ContentElement
 * @package Digitalwerk\ContentElementRegistry\Command\CreateCommand
 */
class ContentElementCreateCommand extends Command
{
    protected function configure()
    {
        $this->addArgument('table', InputArgument::REQUIRED,'Enter table of CE');
        $this->addArgument('name', InputArgument::REQUIRED,'Enter name of CE. Format: [NewContentElement]');
        $this->addArgument('title', InputArgument::REQUIRED,'Enter title of new CE. Format: [title-of-new-CE]');
        $this->addArgument('description', InputArgument::REQUIRED,'Enter description of new CE. Format: [description-of-new-CE]');
        $this->setDescription('Create content element with some fields.');
        $this->addArgument('fields',InputArgument::REQUIRED ,'Enter fields of new CE. Format: [name,type,title-of-field/name2,type,title,title-of-field2/]
        fields types => [fal, textarea, input, radio, select, check]');
        $this->addArgument('inline-fields',InputArgument::IS_ARRAY ,'');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $contentElementRegistryConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->get('content_element_registry');
        $extensionName = explode(
            '/',
            explode(':',$contentElementRegistryConfiguration['contentElementsPaths'])[1]
        )[0];
        $name = $input->getArgument('name');
        $title = $input->getArgument('title');
        $description = $input->getArgument('description');
        $fields = $input->getArgument('fields');

        $extensionNameInNameSpace = str_replace(' ','',ucwords(str_replace('_',' ',$extensionName)));
        $inlineFields = $input->getArgument('inline-fields');
        $table = $input->getArgument('table');
        $namespaceToContentElementModel = 'Digitalwerk\\' . $extensionNameInNameSpace . '\Domain\Model\ContentElement';
        $relativePathToModel = 'dw_boilerplate/Classes/Domain/Model/ContentElement';
        $relativePathToClass = 'Digitalwerk\\' . $extensionNameInNameSpace . '\ContentElement\\' . $name;

        GeneralUtility::makeInstance(ExtensionFolderAndFileStructureCreateCommand::class, $extensionName)->checkContentElementCreateCommand();
        $fields = GeneralUtility::makeInstance(FieldsCreateCommandUtility::class)->generateObject($fields, $table);

        $render = GeneralUtility::makeInstance(RenderCreateCommand::class);
        $render->setExtensionName($extensionName);
        $render->setFields($fields);
        $render->setInlineRelativePath($relativePathToModel);
        $render->setName($name);
        $render->setTable($table);
        $render->setInlineFields($inlineFields);
        $render->setModelNamespace($namespaceToContentElementModel);
        $render->setStaticName($name);
        $render->setElementType('ContentElement');
        $render->setRelativePathToClass($relativePathToClass);
        $render->setOutput($output);
        $render->setInput($input);

        $render->contentElementClass()->template();
        $render->model()->contentElementAndInlinetemplate();
        $render->template()->contentElementTemplate();
        $render->tca()->contentElementTemplate();
        $render->icon()->copyContentElementDefaultIcon();
        $render->previewImage()->copyContentElementDefault();
        $render->inline()->render();
        $render->sqlDatabase()->fields();
        $render->flexForm()->contentElementTemplate();

        $render->translation()->addStringToTranslation(
            'public/typo3conf/ext/' . $extensionName . '/Resources/Private/Language/locallang_db.xlf',
            $table . '.' . str_replace('_', '', $extensionName) . '_'. strtolower($name) . '.title',
            $title
        );
        $render->translation()->addStringToTranslation(
            'public/typo3conf/ext/' . $extensionName . '/Resources/Private/Language/locallang_db.xlf',
            $table .'.' . str_replace('_', '', $extensionName) . '_'. strtolower($name) . '.description',
            $description
        );
        $render->translation()->addFieldsTitleToTranslation(
            'public/typo3conf/ext/' . $extensionName . '/Resources/Private/Language/locallang_db.xlf'
        );

        $output->writeln('<bg=red;options=bold>• Fill template: public/typo3conf/ext/dw_boilerplate/Resources/Private/Templates/ContentElements</>');
        $output->writeln('<bg=red;options=bold>• Change Content element Icon.</>');
        $output->writeln('<bg=red;options=bold>• Change Content element Preview image.</>');
        $output->writeln('<bg=green;options=bold>Content element '.$name.' was created.</>');
    }
}
