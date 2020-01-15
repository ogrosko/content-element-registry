<?php
namespace Digitalwerk\ContentElementRegistry\Command;

use Digitalwerk\ContentElementRegistry\Utility\CreatePageTypeUtility;
use Digitalwerk\ContentElementRegistry\Utility\GeneralCreateCommandUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreatePageType
 * @package Digitalwerk\ContentElementRegistry\Command
 */
class CreatePageType extends Command
{

    protected function configure()
    {
        $this->setDescription('Create page type with some fields.');
        $this->addArgument('name', InputArgument::REQUIRED,'Enter name of PageType.');
        $this->addArgument('title', InputArgument::REQUIRED,'Enter title of PageType.');
        $this->addArgument('doktype', InputArgument::REQUIRED,'Enter doktype of PageType.');
        $this->addArgument('auto-header', InputArgument::REQUIRED,'Set true, if auto generating header is needed.');
        $this->addArgument('fields', InputArgument::REQUIRED,'Add new fields. format: "fieldName,fieldType,fieldTitle/"');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doktype = $input->getArgument('doktype');
        $pageTypeName = $input->getArgument('name');
        $pageTypeTitle = $input->getArgument('title');
        $autoHeader = $input->getArgument('auto-header');
        $fields = $input->getArgument('fields');


        $pageTypeIcon = "public/typo3conf/ext/dw_page_types/Resources/Public/Icons/dw-page-type-" . $doktype . ".svg";
        $pageTypeIconNotInMenu = "public/typo3conf/ext/dw_page_types/Resources/Public/Icons/dw-page-type-" . $doktype . "-not-in-menu.svg";

        $CeModel = "public/typo3conf/ext/dw_page_types/Classes/Domain/Model/" . $pageTypeName . ".php";
        $CeModelContent = '<?php
declare(strict_types=1);
namespace Digitalwerk\DwPageTypes\Domain\Model;

' . GeneralCreateCommandUtility::importClassToModel($fields, 'pageTypesFields') . '

/**
 * Class ' . $pageTypeName . '
 * @package Digitalwerk\DwPageTypes\Domain\Model
 */
class ' . $pageTypeName . ' extends DefaultPage
{
    ' . GeneralCreateCommandUtility::addConstantsToModel($fields, 'pageTypesFields') . '

    /**
     * @var int
     */
    protected static $doktype = ' . $doktype . ';
    
    ' . GeneralCreateCommandUtility::addFieldsToModel($fields, 'pageTypesFields', $pageTypeName) . '
}';

        $pageTCAContent = 'public/typo3conf/ext/dw_page_types/Configuration/TCA/Overrides/pages_' . $pageTypeName . '.php';
        $pageTCAAddContent = '<?php
declare(strict_types=1);    
use Digitalwerk\DwPageTypes\Domain\Model;

defined(\'TYPO3_MODE\') or die();

//Add page doktypes
Digitalwerk\DwPageTypes\Utility\PageTypeUtility::addTcaDoktype(Model\\' . $pageTypeName . '::getDoktype());

$tca = [
    \'palettes\' => [
        \'' . lcfirst($pageTypeName) . '\' => [
            \'label\' => \'LLL:EXT:dw_page_types/Resources/Private/Language/locallang_db.xlf:page.type.' . $doktype . '.label\',
            \'showitem\' => \'' . GeneralCreateCommandUtility::addFieldsToPalette($fields, $pageTypeName, 'pageTypesFields', '          '). '\'
        ],
    ],    
    \'columns\' => [
        ' . GeneralCreateCommandUtility::addColumnsToTCA('pages', $pageTypeName, $pageTypeName, $fields, 'Digitalwerk\DwPageTypes\Domain\Model\\' . $pageTypeName, 'pageTypesFields', '        ', 'dw_page_types', '    '). '
    ],
];

$GLOBALS[\'TCA\'][\'pages\'] = array_replace_recursive($GLOBALS[\'TCA\'][\'pages\'], $tca);


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    \'pages\',
    \'--div--;LLL:EXT:dw_page_types/Resources/Private/Language/locallang_db.xlf:page.type.' . $doktype . '.label,
                        --palette--;;' . lcfirst($pageTypeName) . '\',
    Model\\' . $pageTypeName . '::getDoktype(),
    \'after:subtitle\'
);';
//        Check and add auto header
        CreatePageTypeUtility::checkDefaultTemplateOptionalAndAddAutoHeader($autoHeader, $pageTypeName);

//        Add content to ext_tables.php
        GeneralCreateCommandUtility::importStringInToFileAfterString(
            'public/typo3conf/ext/dw_page_types/ext_tables.php',
            [
                "        Digitalwerk\DwPageTypes\Utility\PageTypeUtility::addPageDoktype(" . $pageTypeName . "::getDoktype()); \n"
            ],
            [
                'call_user_func(',
                'function () {'
            ]
        );

        GeneralCreateCommandUtility::importStringInToFileAfterString(
            'public/typo3conf/ext/dw_page_types/ext_tables.php',
            [
                "use Digitalwerk\DwPageTypes\Domain\Model\\" . $pageTypeName . ";\n"
            ],
            [
                '<?php',
                ''
            ]
        );

//        Add content to typoscript
        GeneralCreateCommandUtility::importStringInToFileAfterString(
            'public/typo3conf/ext/dw_page_types/ext_typoscript_setup.typoscript',
            [
                GeneralCreateCommandUtility::getTyposcriptMapping($pageTypeName, $fields, 'pageTypesFields', 'pages', '{$PAGE_DOKTYPE_' . strtoupper($pageTypeName) . '}', 'Digitalwerk\DwPageTypes\Domain\Model\\' . $pageTypeName). " \n"
            ],
            [
                'config.tx_extbase {',
                'persistence {',
                'classes {'
            ]
        );

        GeneralCreateCommandUtility::importStringInToFileAfterString(
            'public/typo3conf/ext/dw_page_types/ext_typoscript_setup.typoscript',
            [
                "          Digitalwerk\DwPageTypes\Domain\Model\\" . $pageTypeName . " = Digitalwerk\DwPageTypes\Domain\Model\\" . $pageTypeName. " \n"
            ],
            [
                'Digitalwerk\DwPageTypes\Domain\Model\DefaultPage {',
                'mapping {',
                'tableName = pages',
                'recordType = {$PAGE_DOKTYPE_DEFAULT_PAGE}',
                '}',
                'subclasses {',
            ]
        );

        GeneralCreateCommandUtility::importStringInToFileAfterString(
            'public/typo3conf/ext/dw_boilerplate/Configuration/TypoScript/Extensions/DwBoilerplate.typoscript',
            [
                "                " . strtolower($pageTypeName) . " = {\$PAGE_DOKTYPE_" . strtoupper($pageTypeName) . "} \n"
            ],
            [
                '}',
                'doktype {',
                'defaultPage = {$PAGE_DOKTYPE_DEFAULT_PAGE}',
            ]
        );

        GeneralCreateCommandUtility::importStringInToFileAfterString(
            'public/typo3conf/ext/dw_boilerplate/Configuration/TypoScript/constants.typoscript',
            ["PAGE_DOKTYPE_" . strtoupper($pageTypeName) . " = " . $doktype . " \n"],
            [
                '#Page types',
                'PAGE_DOKTYPE_DEFAULT_PAGE = 1'
            ]
        );

        file_put_contents($CeModel, $CeModelContent);
        file_put_contents($pageTCAContent, $pageTCAAddContent);
        copy("public/typo3conf/ext/dw_page_types/Resources/Public/Icons/dw-page-type-99.svg",$pageTypeIcon);
        copy("public/typo3conf/ext/dw_page_types/Resources/Public/Icons/dw-page-type-99.svg",$pageTypeIconNotInMenu);

        GeneralCreateCommandUtility::addTitleToTranslation(
            'public/typo3conf/ext/dw_page_types/Resources/Private/Language/locallang_db.xlf',
            'page.type.'. $doktype . '.label',
            $pageTypeTitle
        );

        GeneralCreateCommandUtility::addFieldsTitleToTranslation(
            'public/typo3conf/ext/dw_page_types/Resources/Private/Language/locallang_db.xlf',
            'pages',
            $pageTypeName,
            $pageTypeName,
            $fields,
            'pageTypesFields',
            'DwPageTypes'
        );

//        Message with sql fields
        if ($fields !== '-') {
            GeneralCreateCommandUtility::importStringInToFileAfterString(
                'public/typo3conf/ext/dw_page_types/ext_tables.sql',
                ['    ' . GeneralCreateCommandUtility::addFieldsToTable($fields, $pageTypeName, 'pageTypesFields'). ", \n"],
                [
                    '#',
                    "# Table structure for table 'pages'",
                    "#",
                    "CREATE TABLE pages (",
                ]
            );
        }

//        Element created message
        $output->writeln('<bg=green;options=bold>Page type ' . $pageTypeName . ' was created.</>');

        $output->writeln('<bg=red;options=bold>• Update/Compare Typo3 database.</>');
        $output->writeln('<bg=red;options=bold>• Change PageType Icon.</>');

        if ($autoHeader === 'true') {
            $output->writeln('<bg=red;options=bold>• Fill auto header template: public/typo3conf/ext/dw_boilerplate/Resources/Private/Partials/PageType</>');
        }
    }
}
