<?php
namespace Digitalwerk\ContentElementRegistry\Command;

use Digitalwerk\ContentElementRegistry\Utility\CreateContentElementUtility;
use Digitalwerk\ContentElementRegistry\Utility\GeneralCreateCommandUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateContentElementAdvanceIRRE
 * @package Digitalwerk\ContentElementRegistry\Command
 */
class CreateContentElementAdvanceIRRE extends Command
{

    protected function configure()
    {
        $this->setDescription('Create content element with fields and inline relation (with some fields).');
        $this->addArgument('name', InputArgument::REQUIRED,'Enter name of CE. Format: [NewContentElement]');
        $this->addArgument('title', InputArgument::REQUIRED,'Enter title of new CE. Format: [title-of-new-CE]');
        $this->addArgument('description', InputArgument::REQUIRED,'Enter description of new CE. Format: [description-of-new-CE]');
        $this->addArgument('fields',InputArgument::REQUIRED ,'Enter fields of new CE. Format: [name,type,title-of-field/name2,type,title,title-of-field2/]
        fields types => [fal, textarea, input, radio, select, check, title, text, image, media, link, link_text]');
        $this->addArgument('IRREName', InputArgument::REQUIRED,'Enter name of IRRE. Format: [NewItem]');
        $this->addArgument('IRRETitle', InputArgument::REQUIRED,'Enter title of new IRRE. Format: [title-new-Irre]');
        $this->addArgument('IRREItemTitle', InputArgument::REQUIRED,'Enter item title of IRRE. Format: [New-item]');
        $this->addArgument('IRREFields',InputArgument::REQUIRED ,'Enter fields of new IRRE. Format: [name,type,title-of-field/name2,type,title,title-of-field2/]
        fields types => [fal, textarea, input, radio, select, check, title, text, image, media, link, link_text]');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $contentElementName = $input->getArgument('name');
        $contentElementTitle = $input->getArgument('title');
        $contentElementDescription = $input->getArgument('description');
        $contentElementFields = $input->getArgument('fields');

        $inlineRelationName = $input->getArgument('IRREName');
        $inlineRelationTitle = $input->getArgument('IRRETitle');
        $inlineRelationItemTitle = $input->getArgument('IRREItemTitle');
        $inlineRelationItemTitleWithoutSpaces = str_replace('-','', $inlineRelationItemTitle);
        $inlineRelationItemTitleWithoutSpacesAndFirstWordUppercase = str_replace(' ','', ucwords(str_replace('-',' ', $inlineRelationItemTitle)));
        $inlineRelationFields = $input->getArgument('IRREFields');


//        Content element class path($CeClass) and Content element class's template ($CeClassContent)
        $CeClass = "public/typo3conf/ext/dw_boilerplate/Classes/ContentElement/".$contentElementName.".php";
        $CeClassContent = '<?php
declare(strict_types=1);
namespace Digitalwerk\DwBoilerplate\ContentElement;

use Digitalwerk\ContentElementRegistry\ContentElement\AbstractContentElementRegistryItem;

/**
 * Class '.$contentElementName.'
 * @package Digitalwerk\DwBoilerplate\ContentElement
 */
class '.$contentElementName.' extends AbstractContentElementRegistryItem
{
    /**
     * Content relation type
     */
    const CONTENT_RELATION_'.strtoupper($inlineRelationItemTitleWithoutSpaces).' = \'dwboilerplate_'.strtolower($contentElementName).'_'.strtolower($inlineRelationItemTitleWithoutSpaces).'\';
    
    /**
     * @var array
     */
    protected $columnsMapping = [
        "tx_contentelementregistry_relations" => "'.lcfirst($inlineRelationName).'",
        '.CreateContentElementUtility::addFieldsToClassMapping($contentElementFields, $contentElementName).'
    ];

    /**
     * '.$contentElementName.' constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();
        $this->addPalette(
            \'default\',
            \'' . GeneralCreateCommandUtility::addFieldsToPalette($contentElementFields, $contentElementName, 'contentElementsAndInlineRelationFields', '') . ',
            --linebreak--, tx_contentelementregistry_relations\'
        );
    }

    /**
     * @return array
     */
    public function getColumnsOverrides()
    {
        return [
'.CreateContentElementUtility::getDefaultFieldsWithAnotherTitle('tt_content', $contentElementName, $contentElementName, $contentElementFields).'
            \'tx_contentelementregistry_relations\' => [
                \'label\' => \'LLL:EXT:dw_boilerplate/Resources/Private/Language/locallang_db.xlf:tt_content.dwboilerplate_'.strtolower($contentElementName).'.column.tx_contentelementregistry_relations\',
                \'config\' => [
                    \'overrideChildTca\' => [
                        \'columns\' => [
                            \'type\' => [
                                \'config\' => [
                                    \'items\' => [
                                        [\'LLL:EXT:dw_boilerplate/Resources/Private/Language/locallang_db.xlf:tt_content.dwboilerplate_'.strtolower($contentElementName).'_'.strtolower($inlineRelationItemTitleWithoutSpaces).'\', self::CONTENT_RELATION_'.strtoupper($inlineRelationItemTitleWithoutSpaces).'],
                                    ],
                                    \'default\' => self::CONTENT_RELATION_'.strtoupper($inlineRelationItemTitleWithoutSpaces).'
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}';

//        Content element template path($CeTemplate) and Content element template's template ($CeTemplateContent)
        $CeTemplate = "public/typo3conf/ext/dw_boilerplate/Resources/Private/Templates/ContentElements/".$contentElementName.".html";
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
        $CeIcon = "public/typo3conf/ext/dw_boilerplate/Resources/Public/Icons/ContentElement/dwboilerplate_".strtolower($contentElementName).".svg";
        $CePreviewImage = "public/typo3conf/ext/dw_boilerplate/Resources/Public/Images/ContentElementPreviews/common_dwboilerplate_".strtolower($contentElementName).".png";

//        Content element flexform
        $CEFlexForm = "public/typo3conf/ext/dw_boilerplate/Configuration/FlexForms/ContentElement/dwboilerplate_" . strtolower($contentElementName) . '.xml';
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
                    ' . GeneralCreateCommandUtility::addFieldsToFlexForm($contentElementFields, $contentElementName, 'contentElementsAndInlineRelationFields', true) . '
                 </el>
            </ROOT>
        </sDEF>
    </sheets>
</T3DataStructure>
';


//        Content element model path($CeModel) and Content element model's template ($CeModelContent)
        $IRRENameSpace = str_replace('/','', "Digitalwerk\DwBoilerplate\Domain\Model\ContentElement\/".$contentElementName);
        $CeModel = "public/typo3conf/ext/dw_boilerplate/Classes/Domain/Model/ContentElement/".$contentElementName.".php";
        $CeModelContent = '<?php
declare(strict_types=1);
namespace Digitalwerk\DwBoilerplate\Domain\Model\ContentElement;

use Digitalwerk\ContentElementRegistry\Domain\Model\ContentElement;
' . GeneralCreateCommandUtility::importClassToModel($contentElementFields, 'contentElementsAndInlineRelationFields', 'objectStorage') . '

/**
 * Class '.$contentElementName.'
 * @package Digitalwerk\DwBoilerplate\Domain\Model\ContentElement
 */
class '.$contentElementName.' extends ContentElement
{
    ' . GeneralCreateCommandUtility::addConstantsToModel($contentElementFields, 'contentElementsAndInlineRelationFields') . '

    ' . GeneralCreateCommandUtility::addFieldsToModel($contentElementFields, 'contentElementsAndInlineRelationFields', $contentElementName,
'/**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\\'.$IRRENameSpace.'\\'.$inlineRelationItemTitleWithoutSpacesAndFirstWordUppercase.'>
     */
    protected $'.lcfirst($inlineRelationName).' = null;

    /**
     * '.$inlineRelationName.' constructor.
     */
    public function __construct()
    {
        $this->'.lcfirst($inlineRelationName).' = new ObjectStorage();
    }

    /**
     * @return ObjectStorage
     */
    public function get'.$inlineRelationName.'(): ObjectStorage
    {
        return $this->'.lcfirst($inlineRelationName).';
    }') . '
}';

//        IRRE model path($IRREModel) and IRRE model's template ($IRREModelContent)
        $IRREModel = "public/typo3conf/ext/dw_boilerplate/Classes/Domain/Model/ContentElement/".$contentElementName.'/'.$inlineRelationItemTitleWithoutSpacesAndFirstWordUppercase.".php";
        $IRREModelContent = '<?php
declare(strict_types=1);
namespace '.$IRRENameSpace.';

' . GeneralCreateCommandUtility::importClassToModel($inlineRelationFields, 'contentElementsAndInlineRelationFields') . '
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class '.$inlineRelationItemTitleWithoutSpacesAndFirstWordUppercase.'
 * @package '.$IRRENameSpace.'
 */
class '.$inlineRelationItemTitleWithoutSpacesAndFirstWordUppercase.' extends AbstractEntity
{
    ' . GeneralCreateCommandUtility::addConstantsToModel($inlineRelationFields, 'contentElementsAndInlineRelationFields') . '
    
    ' . GeneralCreateCommandUtility::addFieldsToModel($inlineRelationFields, 'contentElementsAndInlineRelationFields', $contentElementName) . '
}';

        $ttContent = 'public/typo3conf/ext/dw_boilerplate/Configuration/TCA/Overrides/tt_content_'.$contentElementName.'.php';
        $ttContentAddContent = '<?php
defined(\'TYPO3_MODE\') or die();

/**
 * tt_content new fields
 */
$'.lcfirst($contentElementName).'Columns = [
    ' . GeneralCreateCommandUtility::addColumnsToTCA('tt_content', $contentElementName, $contentElementName, $contentElementFields, '\Digitalwerk\DwBoilerplate\Domain\Model\ContentElement\\' . $contentElementName, 'contentElementsAndInlineRelationFields', '    ', 'dw_boilerplate', '    ') . '
];
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(\'tt_content\', $'.lcfirst($contentElementName).'Columns);  
';

        $IRREtxContentelementregistryDomainModelRelation = 'public/typo3conf/ext/dw_boilerplate/Configuration/TCA/Overrides/tx_contentelementregistry_domain_model_relation_'.$inlineRelationItemTitleWithoutSpacesAndFirstWordUppercase.'.php';
        $IRREtxContentelementregistryDomainModelRelationContent = '<?php
defined(\'TYPO3_MODE\') or die();

$tempTca = [
    \'ctrl\' => [
        \'typeicon_classes\' => [
            Digitalwerk\DwBoilerplate\ContentElement\\'.$contentElementName.'::CONTENT_RELATION_'.strtoupper($inlineRelationItemTitleWithoutSpaces).' => Digitalwerk\DwBoilerplate\ContentElement\\'.$contentElementName.'::CONTENT_RELATION_'.strtoupper($inlineRelationItemTitleWithoutSpaces).',
        ],
    ],
    \'types\' => [
        Digitalwerk\DwBoilerplate\ContentElement\\'.$contentElementName.'::CONTENT_RELATION_'.strtoupper($inlineRelationItemTitleWithoutSpaces).' => [
            \'showitem\' => \'type, '.CreateContentElementUtility::addFieldsToIRRETypeTCA($inlineRelationFields, $inlineRelationItemTitleWithoutSpacesAndFirstWordUppercase).'
                           --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access, hidden, starttime, endtime, sys_language_uid, l10n_parent, l10n_diffsource\',
            \'columnsOverrides\' => [
'.CreateContentElementUtility::getDefaultFieldsWithAnotherTitle('tx_contentelementregistry_domain_model_relation', $contentElementName, $inlineRelationItemTitleWithoutSpacesAndFirstWordUppercase, $inlineRelationFields, '    ').'
            ],
        ],
    ],
    \'palettes\' => [
 
 
    ],
];

$GLOBALS[\'TCA\'][\'tx_contentelementregistry_domain_model_relation\'] = array_replace_recursive($GLOBALS[\'TCA\'][\'tx_contentelementregistry_domain_model_relation\'], $tempTca);

/**
 * tx_contentelementregistry_domain_model_relation new fields
 */
$'.lcfirst($inlineRelationItemTitleWithoutSpacesAndFirstWordUppercase).'Columns = [
    ' . GeneralCreateCommandUtility::addColumnsToTCA('tx_contentelementregistry_domain_model_relation', $contentElementName, $inlineRelationItemTitleWithoutSpacesAndFirstWordUppercase, $inlineRelationFields, '\Digitalwerk\DwBoilerplate\Domain\Model\ContentElement\\' . $contentElementName . '\\' . $inlineRelationItemTitleWithoutSpacesAndFirstWordUppercase, 'contentElementsAndInlineRelationFields','    ','dw_boilerplate', '') . '
];
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(\'tx_contentelementregistry_domain_model_relation\', $'.lcfirst($inlineRelationItemTitleWithoutSpacesAndFirstWordUppercase).'Columns);  
';

        //        IRRE default icon
        $IRREIcon = "public/typo3conf/ext/dw_boilerplate/Resources/Public/Icons/ContentElement/dwboilerplate_".strtolower($contentElementName)."_".strtolower($inlineRelationItemTitleWithoutSpaces).".svg";

//        Flexform creating
        if (!file_exists('public/typo3conf/ext/dw_boilerplate/Configuration/FlexForms/ContentElement')) {
            mkdir('public/typo3conf/ext/dw_boilerplate/Configuration/FlexForms/ContentElement/', 0777, true);
        }
        if (!(CreateContentElementUtility::areAFlexFormOnlyInCEFields($inlineRelationFields, 'inlineRelationField')) && CreateContentElementUtility::areAFlexFormOnlyInCEFields($contentElementFields, 'contentElementField')) {
            file_put_contents($CEFlexForm, $CEFlexFormContent);
        }

//        Add translations (title, description) to public/typo3conf/ext/dw_boilerplate/Resources/Private/Language/locallang_db.xlf
        GeneralCreateCommandUtility::addTitleToTranslation(
            'public/typo3conf/ext/dw_boilerplate/Resources/Private/Language/locallang_db.xlf',
            'tt_content.dwboilerplate_'. strtolower($contentElementName) . '.title',
            $contentElementTitle
        );
        GeneralCreateCommandUtility::addTitleToTranslation(
            'public/typo3conf/ext/dw_boilerplate/Resources/Private/Language/locallang_db.xlf',
            'tt_content.dwboilerplate_'. strtolower($contentElementName) . '.description',
            $contentElementDescription
        );

//        Add translations (IRRE title, and Item title) to public/typo3conf/ext/dw_boilerplate/Resources/Private/Language/locallang_db.xlf
        GeneralCreateCommandUtility::addTitleToTranslation(
            'public/typo3conf/ext/dw_boilerplate/Resources/Private/Language/locallang_db.xlf',
            'tt_content.dwboilerplate_'. strtolower($contentElementName) . '.column.tx_contentelementregistry_relations',
            $inlineRelationTitle
        );
        GeneralCreateCommandUtility::addTitleToTranslation(
            'public/typo3conf/ext/dw_boilerplate/Resources/Private/Language/locallang_db.xlf',
            'tt_content.dwboilerplate_'. strtolower($contentElementName) . '_' . strtolower($inlineRelationItemTitleWithoutSpacesAndFirstWordUppercase),
            $inlineRelationItemTitle
        );

//        Add translations (fields titles) to public/typo3conf/ext/dw_boilerplate/Resources/Private/Language/locallang_db.xlf
        GeneralCreateCommandUtility::addFieldsTitleToTranslation(
            'public/typo3conf/ext/dw_boilerplate/Resources/Private/Language/locallang_db.xlf',
            'tt_content',
            $contentElementName,
            $contentElementName,
            $contentElementFields,
            'contentElementsAndInlineRelationFields',
            'DwBoilerplate'
            );

//        Created new files
        file_put_contents($CeClass, $CeClassContent);
        file_put_contents($CeTemplate, $CeTemplateContent);
        file_put_contents($CeModel, $CeModelContent);
        if ((!empty($contentElementFields) || $contentElementFields !== '-') && !(CreateContentElementUtility::areAllFieldsDefault($contentElementFields))) {
            file_put_contents($ttContent, $ttContentAddContent);
        }


        copy("public/typo3conf/ext/content_element_registry/Resources/Public/Icons/CEDefaultIcon.svg",$CeIcon);

//        Add translations (title, description, fields name...) to public/typo3conf/ext/dw_boilerplate/Resources/Private/Language/locallang_db.xlf
        GeneralCreateCommandUtility::addFieldsTitleToTranslation(
            'public/typo3conf/ext/dw_boilerplate/Resources/Private/Language/locallang_db.xlf',
            'tx_contentelementregistry_domain_model_relation',
            $contentElementName,
            $inlineRelationItemTitleWithoutSpacesAndFirstWordUppercase,
            $inlineRelationFields,
            'contentElementsAndInlineRelationFields',
            'DwBoilerplate'
        );

//        Created new files IRRE
        copy("public/typo3conf/ext/content_element_registry/Resources/Public/Icons/CEDefaultIcon.svg",$IRREIcon);
        copy("public/typo3conf/ext/dw_boilerplate/Resources/Public/Images/dummy.jpg", $CePreviewImage);
        if (!file_exists('public/typo3conf/ext/dw_boilerplate/Classes/Domain/Model/ContentElement/'.$contentElementName.'')) {
            mkdir('public/typo3conf/ext/dw_boilerplate/Classes/Domain/Model/ContentElement/'.$contentElementName.'', 0777, true);
        }
        file_put_contents($IRREModel, $IRREModelContent);
        file_put_contents($IRREtxContentelementregistryDomainModelRelation, $IRREtxContentelementregistryDomainModelRelationContent);

//        Message with register icon
        GeneralCreateCommandUtility::importStringInToFileAfterString(
            'public/typo3conf/ext/dw_boilerplate/ext_localconf.php',
            [
                "                'ContentElement/dwboilerplate_" . strtolower($contentElementName) . "_" . strtolower($inlineRelationItemTitleWithoutSpaces) . "', \n"
            ],
            [
                "'GridElement/2ColumnGrid',",
                "'GridElement/AccordionContainer',",
                "'GridElement/AccordionContainerItem',",
            ]
        );


//        Element created message
        GeneralCreateCommandUtility::importStringInToFileAfterString(
            'public/typo3conf/ext/dw_boilerplate/ext_typoscript_setup.typoscript',
            [
                ' ' . GeneralCreateCommandUtility::getTyposcriptMapping($inlineRelationName, $inlineRelationFields, 'contentElementsAndInlineRelationFields', 'tx_contentelementregistry_domain_model_relation', 'dwboilerplate_'.strtolower($contentElementName).'_'.strtolower($inlineRelationName), 'Digitalwerk\DwBoilerplate\Domain\Model\ContentElement\\'.$contentElementName.'\\'.$inlineRelationName) . "\n"
            ],
            [
                'config.tx_extbase {',
                'persistence {',
                'classes {',
            ]
        );

//        Element created message
        $output->writeln('<bg=green;options=bold>Content element IRRE '.$contentElementName.' was created.</>');
//        Message with sql fields
        if ((!empty($contentElementFields) || $contentElementFields !== '-') && !(CreateContentElementUtility::areAllFieldsDefault($contentElementFields))) {
            GeneralCreateCommandUtility::importStringInToFileAfterString(
                'public/typo3conf/ext/dw_boilerplate/ext_tables.sql',
                [
                    '    ' . GeneralCreateCommandUtility::addFieldsToTable($contentElementFields, $contentElementName, 'contentElementsAndInlineRelationFields'). ", \n"
                ],
                [
                    "# Table structure for table 'tt_content'",
                    "#",
                    "CREATE TABLE tt_content (",
                ]
            );
            $output->writeln('<bg=red;options=bold>• Update/Compare Typo3 database. (Content elements fields added)</>');
        }

        //        Message with IRRE sql fields
        if ((!empty($inlineRelationFields) || $inlineRelationFields !== '-') && !(CreateContentElementUtility::areAllFieldsDefault($inlineRelationFields))) {
            GeneralCreateCommandUtility::importStringInToFileAfterString(
                'public/typo3conf/ext/dw_boilerplate/ext_tables.sql',
                [
                    '    ' . GeneralCreateCommandUtility::addFieldsToTable($inlineRelationFields, $inlineRelationItemTitleWithoutSpacesAndFirstWordUppercase, 'contentElementsAndInlineRelationFields'). ", \n"
                ],
                [
                    "# Table structure for table 'tx_contentelementregistry_domain_model_relation'",
                    "#",
                    "CREATE TABLE tx_contentelementregistry_domain_model_relation (",
                ]
            );
            $output->writeln('<bg=red;options=bold>• Update/Compare Typo3 database. (Inline relations fields added)</>');
        }
        $output->writeln('<bg=red;options=bold>• Fill template: public/typo3conf/ext/dw_boilerplate/Resources/Private/Templates/ContentElements</>');
        $output->writeln('<bg=red;options=bold>• Change Content element and inline relation Icons.</>');
        $output->writeln('<bg=red;options=bold>• Change Content element Preview image.</>');
    }
}
