<?php
namespace Digitalwerk\ContentElementRegistry\Command;

use Digitalwerk\ContentElementRegistry\Utility\CreateContentElementUtility;
use Digitalwerk\ContentElementRegistry\Utility\GeneralCreateCommandUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateContentElementAdvance
 * @package Digitalwerk\ContentElementRegistry\Command
 */
class CreateContentElementAdvance extends Command
{

    protected function configure()
    {
        $this->setDescription('Create content element with some fields.');
        $this->addArgument('name', InputArgument::REQUIRED,'Enter name of CE. Format: [NewContentElement]');
        $this->addArgument('title', InputArgument::REQUIRED,'Enter title of new CE. Format: [title-of-new-CE]');
        $this->addArgument('description', InputArgument::REQUIRED,'Enter description of new CE. Format: [description-of-new-CE]');
        $this->addArgument('fields',InputArgument::OPTIONAL ,'Enter fields of new CE. Format: [name,type,title-of-field/name2,type,title,title-of-field2/]
        fields types => [fal, textarea, input, radio, select, check]');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $title = $input->getArgument('title');
        $description = $input->getArgument('description');
        $fields = $input->getArgument('fields');

//        Content element class path($CeClass) and Content element class's template ($CeClassContent)
        $CeClass = "public/typo3conf/ext/dw_boilerplate/Classes/ContentElement/".$name.".php";
        $CeClassContent = '<?php
declare(strict_types=1);
namespace Digitalwerk\DwBoilerplate\ContentElement;

use Digitalwerk\ContentElementRegistry\ContentElement\AbstractContentElementRegistryItem;

/**
 * Class '.$name.'
 * @package Digitalwerk\DwBoilerplate\ContentElement
 */
class '.$name.' extends AbstractContentElementRegistryItem
{
    /**
     * @var array
     */
    protected $columnsMapping = [
        '.CreateContentElementUtility::addFieldsToClassMapping($fields, $name).'
    ];

    /**
     * '.$name.' constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();
        $this->addPalette(
            \'default\',
            \'' . GeneralCreateCommandUtility::addFieldsToPalette($fields, $name, 'contentElementsAndInlineRelationFields', '') . '\'
        );
    }

    /**
     * @return array
     */
    public function getColumnsOverrides()
    {
        return [
'.CreateContentElementUtility::getDefaultFieldsWithAnotherTitle('tt_content', $name, $name, $fields).'
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

' . GeneralCreateCommandUtility::importClassToModel($fields, 'contentElementsAndInlineRelationFields') . '
use Digitalwerk\ContentElementRegistry\Domain\Model\ContentElement;

/**
 * Class '.$name.'
 * @package Digitalwerk\DwBoilerplate\Domain\Model\ContentElement
 */
class '.$name.' extends ContentElement
{
    ' . GeneralCreateCommandUtility::addConstantsToModel($fields, 'contentElementsAndInlineRelationFields') . '
    
    ' . GeneralCreateCommandUtility::addFieldsToModel($fields, 'contentElementsAndInlineRelationFields', $name) . '
}';

        $ttContent = 'public/typo3conf/ext/dw_boilerplate/Configuration/TCA/Overrides/tt_content_'.$name.'.php';
        $ttContentAddContent = '<?php
defined(\'TYPO3_MODE\') or die();

/**
 * tt_content new fields
 */
$'.lcfirst($name).'Columns = [
    ' . GeneralCreateCommandUtility::addColumnsToTCA('tt_content', $name, $name, $fields, '\Digitalwerk\DwBoilerplate\Domain\Model\ContentElement\\' . $name, 'contentElementsAndInlineRelationFields', '', 'dw_boilerplate', '    ') . '
];
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(\'tt_content\', $'.lcfirst($name).'Columns);  
';
//        Content element flexform
        $CEFlexForm = "public/typo3conf/ext/dw_boilerplate/Configuration/FlexForms/ContentElement/dwboilerplate_" . strtolower($name) . '.xml';
        $CEFlexFormContent = '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
<T3DataStructure>
    <meta>
        <langDisable>1</langDisable>
    </meta>
    <sheets>
        <sDEF>
            <ROOT>
                <type>array</type>
                    <el>
                        ' . GeneralCreateCommandUtility::addFieldsToFlexForm($fields, $name, 'contentElementsAndInlineRelationFields', true) . '
                    </el>
            </ROOT>
        </sDEF>
    </sheets>
</T3DataStructure>
';



//        Flexform creating
        if (!file_exists('public/typo3conf/ext/dw_boilerplate/Configuration/FlexForms/ContentElement')) {
            mkdir('public/typo3conf/ext/dw_boilerplate/Configuration/FlexForms/ContentElement/', 0777, true);
        }
        if (CreateContentElementUtility::areAFlexFormOnlyInCEFields($fields, 'contentElementField')) {
            file_put_contents($CEFlexForm, $CEFlexFormContent);
        }


//        Add translations (title, description) to public/typo3conf/ext/dw_boilerplate/Resources/Private/Language/locallang_db.xlf
        GeneralCreateCommandUtility::addTitleToTranslation(
            'public/typo3conf/ext/dw_boilerplate/Resources/Private/Language/locallang_db.xlf',
            'tt_content.dwboilerplate_'. strtolower($name) . '.title',
            $title
        );
        GeneralCreateCommandUtility::addTitleToTranslation(
            'public/typo3conf/ext/dw_boilerplate/Resources/Private/Language/locallang_db.xlf',
            'tt_content.dwboilerplate_'. strtolower($name) . '.description',
            $description
        );

//        Add translations (fields titles) to public/typo3conf/ext/dw_boilerplate/Resources/Private/Language/locallang_db.xlf
        GeneralCreateCommandUtility::addFieldsTitleToTranslation(
            'public/typo3conf/ext/dw_boilerplate/Resources/Private/Language/locallang_db.xlf',
            'tt_content',
            $name,
            $name,
            $fields,
            'contentElementsAndInlineRelationFields',
            'DwBoilerplate'
        );

//        Created new files
        file_put_contents($CeClass, $CeClassContent);
        file_put_contents($CeTemplate, $CeTemplateContent);
        file_put_contents($CeModel, $CeModelContent);

        if ($fields !== '-' && CreateContentElementUtility::areAllFieldsDefault($fields) === false) {
            file_put_contents($ttContent, $ttContentAddContent);
        }
        copy("public/typo3conf/ext/content_element_registry/Resources/Public/Icons/CEDefaultIcon.svg",$CeIcon);
        copy("public/typo3conf/ext/dw_boilerplate/Resources/Public/Images/dummy.jpg", $CePreviewImage);

//        Element created message
        $output->writeln('<bg=green;options=bold>Content element '.$name.' was created.</>');

//        Message with sql fields
        if ($fields !== '-' && CreateContentElementUtility::areAllFieldsDefault($fields) === false) {
            GeneralCreateCommandUtility::importStringInToFileAfterString(
                'public/typo3conf/ext/dw_boilerplate/ext_tables.sql',
                [
                    '    ' . GeneralCreateCommandUtility::addFieldsToTable($fields, $name, 'contentElementsAndInlineRelationFields'). ", \n"
                ],
                [
                    "# Table structure for table 'tt_content'",
                    "#",
                    "CREATE TABLE tt_content (",
                ]
            );
            $output->writeln('<bg=red;options=bold>• Update/Compare Typo3 database.</>');
        }
        $output->writeln('<bg=red;options=bold>• Fill template: public/typo3conf/ext/dw_boilerplate/Resources/Private/Templates/ContentElements</>');
        $output->writeln('<bg=red;options=bold>• Change Content element Icon.</>');
        $output->writeln('<bg=red;options=bold>• Change Content element Preview image.</>');
    }
}
