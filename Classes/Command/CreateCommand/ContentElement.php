<?php
namespace Digitalwerk\ContentElementRegistry\Command\CreateCommand;

use BaconQrCode\Common\Mode;
use Digitalwerk\ContentElementRegistry\Utility\CreateCommand\ClassUtility;
use Digitalwerk\ContentElementRegistry\Utility\CreateCommand\ModelUtility;
use Digitalwerk\ContentElementRegistry\Utility\CreateCommand\SQLUtility;
use Digitalwerk\ContentElementRegistry\Utility\CreateCommand\TCAUtility;
use Digitalwerk\ContentElementRegistry\Utility\CreateCommand\TranslationUtility;
use Digitalwerk\ContentElementRegistry\Utility\GeneralCreateCommandUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ContentElement
 * @package Digitalwerk\ContentElementRegistry\Command\CreateCommand
 */
class ContentElement extends Command
{

    protected function configure()
    {
        $this->addArgument('name', InputArgument::REQUIRED,'Enter name of CE. Format: [NewContentElement]');
        $this->addArgument('title', InputArgument::REQUIRED,'Enter title of new CE. Format: [title-of-new-CE]');
        $this->addArgument('description', InputArgument::REQUIRED,'Enter description of new CE. Format: [description-of-new-CE]');
        $this->setDescription('Create content element with some fields.');
        $this->addArgument('fields',InputArgument::OPTIONAL ,'Enter fields of new CE. Format: [name,type,title-of-field/name2,type,title,title-of-field2/]
        fields types => [fal, textarea, input, radio, select, check]');
        $this->addArgument('inline-fields',InputArgument::IS_ARRAY ,'');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
//        $helper = $this->getHelper('question');
//
//        $question = new Question('Enter name of Content element Class (etc. NewContentElement), without spaces: ');
//        $name = $helper->ask($input, $output, $question);
//
//        $question = new Question('Enter title of Content element (etc. New-content-element), without spaces: ');
//        $title = $helper->ask($input, $output, $question);
//
//        $question = new Question('Enter description of Content element (etc. New-content-element-description), without spaces: ');
//        $description = $helper->ask($input, $output, $question);

        $name = $input->getArgument('name');
        $title = $input->getArgument('title');
        $description = $input->getArgument('description');
        $fields = $input->getArgument('fields');
        $inlineFields = $input->getArgument('inline-fields');
        $table = 'tt_content';
        $extensionName = 'dw_boilerplate';

//        Content element class path($CeClass) and Content element class's template ($CeClassContent)
        $CeClass = "public/typo3conf/ext/dw_boilerplate/Classes/ContentElement/" . $name . ".php";
        $CeClassContent = '<?php
declare(strict_types=1);
namespace Digitalwerk\DwBoilerplate\ContentElement;

use Digitalwerk\ContentElementRegistry\ContentElement\AbstractContentElementRegistryItem;

/**
 * Class ' . $name . '
 * @package Digitalwerk\DwBoilerplate\ContentElement
 */
class ' . $name . ' extends AbstractContentElementRegistryItem
{
    /**
     * @var array
     */
    protected $columnsMapping = [
        ' . ClassUtility::addFieldsToClassMapping($fields, $name, $table, '        ') . '
    ];

    /**
     * ' . $name . ' constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();
        $this->addPalette(
            \'default\',
            \'' . GeneralCreateCommandUtility::addFieldsToPalette($fields, $name, $table, '            ') . '\'
        );
    }

    /**
     * @return array
     */
    public function getColumnsOverrides()
    {
        return [
' . TCAUtility::getDefaultFieldsWithAnotherTitle('tt_content', $name, $name, $fields) . '
        ];
    }
}';

//        Content element template path($CeTemplate) and Content element template's template ($CeTemplateContent)
        $CeTemplate = "public/typo3conf/ext/dw_boilerplate/Resources/Private/Templates/ContentElements/".$name.".html";
        $CeTemplateContent = '<html xmlns="http://www.w3.org/1999/xhtml" lang="en"
      xmlns:f="http://typo3.org/ns/TYPO3/Fluid/ViewHelpers"
      xmlns:v="http://typo3.org/ns/FluidTYPO3/Vhs/ViewHelpers"
      data-namespace-typo3-fluid="true">

<f:layout name="ContentElements/{contentElement.layout}" />

<f:section name="Main">
</f:section>

<f:section name="Preview">
</f:section>

</html>
';

//        Content element default icon
        $CeIcon = "public/typo3conf/ext/dw_boilerplate/Resources/Public/Icons/ContentElement/dwboilerplate_".strtolower($name).".svg";
        $CePreviewImage = "public/typo3conf/ext/dw_boilerplate/Resources/Public/Images/ContentElementPreviews/common_dwboilerplate_".strtolower($name).".png";

//        Content element model path($CeModel) and Content element model's template ($CeModelContent)
        $CeModel = "public/typo3conf/ext/dw_boilerplate/Classes/Domain/Model/ContentElement/".$name.".php";
        $CeModelContent = '<?php
declare(strict_types=1);
namespace Digitalwerk\DwBoilerplate\Domain\Model\ContentElement;

' . ModelUtility::importClassToModel($fields, $table) . '
use Digitalwerk\ContentElementRegistry\Domain\Model\ContentElement;

/**
 * Class '.$name.'
 * @package Digitalwerk\DwBoilerplate\Domain\Model\ContentElement
 */
class '.$name.' extends ContentElement
{
    ' . ModelUtility::addConstantsToModel($fields, $table) . '
    
    ' . ModelUtility::addFieldsToModel($fields, $table, '', 'Domain\Model\ContentElement\\' . $name) . '
}';

        $ttContent = 'public/typo3conf/ext/dw_boilerplate/Configuration/TCA/Overrides/tt_content_'.$name.'.php';
        $ttContentAddContent = '<?php
defined(\'TYPO3_MODE\') or die();

/**
 * tt_content new fields
 */
$'.lcfirst($name).'Columns = [
    ' . TCAUtility::addColumnsToTCA($table, $name, $name, $fields, '\Digitalwerk\DwBoilerplate\Domain\Model\ContentElement\\' . $name, '    ', 'dw_boilerplate', '    ','\Digitalwerk\DwBoilerplate\ContentElement\\' . $name) . '
];
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(\'tt_content\', $'.lcfirst($name).'Columns);  
';

//        Add translations (title, description) to public/typo3conf/ext/dw_boilerplate/Resources/Private/Language/locallang_db.xlf
        TranslationUtility::addStringToTranslation(
            'public/typo3conf/ext/dw_boilerplate/Resources/Private/Language/locallang_db.xlf',
            'tt_content.dwboilerplate_'. strtolower($name) . '.title',
            $title
        );
        TranslationUtility::addStringToTranslation(
            'public/typo3conf/ext/dw_boilerplate/Resources/Private/Language/locallang_db.xlf',
            'tt_content.dwboilerplate_'. strtolower($name) . '.description',
            $description
        );

//        Add translations (fields titles) to public/typo3conf/ext/dw_boilerplate/Resources/Private/Language/locallang_db.xlf
        TranslationUtility::addFieldsTitleToTranslation(
            'public/typo3conf/ext/dw_boilerplate/Resources/Private/Language/locallang_db.xlf',
            $table,
            $name,
            $name,
            $fields,
            'DwBoilerplate'
        );

//        Created new files
        file_put_contents($CeClass, $CeClassContent);
        $resultCheckAndAddInlineFields = (new \Digitalwerk\ContentElementRegistry\Utility\CreateCommand\InlineUtility)->checkAndAddInlineFields($name, $name, $fields, $inlineFields);
        file_put_contents($CeTemplate, $CeTemplateContent);
        file_put_contents($CeModel, $CeModelContent);

        if ($fields !== '-' && GeneralCreateCommandUtility::areAllFieldsDefault($fields, $table) === false) {
            file_put_contents($ttContent, $ttContentAddContent);
        }
        copy("public/typo3conf/ext/content_element_registry/Resources/Public/Icons/CEDefaultIcon.svg",$CeIcon);
        copy("public/typo3conf/ext/dw_boilerplate/Resources/Public/Images/dummy.jpg", $CePreviewImage);

//        Element created message
        $output->writeln('<bg=green;options=bold>Content element '.$name.' was created.</>');

//        Message with sql fields
        if ($fields !== '-' && GeneralCreateCommandUtility::areAllFieldsDefault($fields, $table) === false) {
            GeneralCreateCommandUtility::importStringInToFileAfterString(
                'public/typo3conf/ext/dw_boilerplate/ext_tables.sql',
                [
                    '    ' . SQLUtility::addFieldsToSQLTable($fields, $name, $table). ", \n"
                ],
                [
                    "# Table structure for table 'tt_content'",
                    "#",
                    "CREATE TABLE tt_content (",
                ]
            );
            $output->writeln('<bg=red;options=bold>• Update/Compare Typo3 database.</>');
        }
        if ($resultCheckAndAddInlineFields) {
            $output->writeln('<bg=red;options=bold>• Update/Compare Typo3 database. (inline fields)</>');
            $output->writeln('<bg=red;options=bold>• Change Content element inline relations Icon.</>');
        }
        $output->writeln('<bg=red;options=bold>• Fill template: public/typo3conf/ext/dw_boilerplate/Resources/Private/Templates/ContentElements</>');
        $output->writeln('<bg=red;options=bold>• Change Content element Icon.</>');
        $output->writeln('<bg=red;options=bold>• Change Content element Preview image.</>');
    }
}
