<?php
namespace Digitalwerk\ContentElementRegistry\Utility\CreateCommand;

use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Config\TCAFieldTypes;
use Digitalwerk\ContentElementRegistry\Utility\GeneralCreateCommandUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class InlineUtility
 * @package Digitalwerk\ContentElementRegistry\Utility\CreateCommand
 */
class InlineUtility
{
    /**
     * @var string
     */
    protected $relativePathToInlineModel = 'Domain/Model/ContentElement/';

    /**
     * @param string $relativePathToInlineModel
     */
    public function setRelativePathToInlineModel(string $relativePathToInlineModel): void
    {
        $this->relativePathToInlineModel = $relativePathToInlineModel;
    }

    /**
     * @return string
     */
    public function getRelativePathToInlineModel(): string
    {
        return $this->relativePathToInlineModel;
    }

    /**
     * @param $staticName
     * @param $name
     * @param $fields
     * @param array $inlineFields
     * @return string
     * Only for CE command
     */
    public function checkAndAddInlineFields($staticName, $name, $fields, array $inlineFields)
    {
        if (!empty($fields)) {
            if ($this->getRelativePathToInlineModel() === 'Domain/Model/ContentElement/') {
                $table = 'tt_content';
            } else {
                $table = 'tx_contentelementregistry_domain_model_relation';
            }

            $needToCompareDatabase = false;
            $generalCreateCommandUtility = GeneralUtility::makeInstance(GeneralCreateCommandUtility::class);
            $fieldsToArray = $generalCreateCommandUtility->fieldsToArray($fields);
            $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypes::class)->getTCAFieldTypes($table);
            foreach ($fieldsToArray as $field) {
                $fieldType = explode(',', $field)[1];

                if ($TCAFieldTypes[$table][$fieldType]['inlineFieldsAllowed']) {
                    $firstFieldItem = $generalCreateCommandUtility->getFirstFieldItem($field);
                    $firstFieldItemName = $generalCreateCommandUtility->getItemName($firstFieldItem);
                    $firstFieldItemType = $generalCreateCommandUtility->getItemType($firstFieldItem);

//                    add constant
                    GeneralCreateCommandUtility::importStringInToFileAfterString(
                        'public/typo3conf/ext/dw_boilerplate/Classes/ContentElement/' . $staticName . '.php',
                        ['   const CONTENT_RELATION_' . strtoupper($firstFieldItemName) . ' = \'dwboilerplate_' . strtolower($staticName) . '_' . strtolower($firstFieldItemName) . '\';' . "\n"],
                        [
                            'class ' . $staticName . ' extends AbstractContentElementRegistryItem',
                            '{'
                        ]
                    );

                    if ($name === $staticName && $this->getRelativePathToInlineModel() !== 'Domain/Model/ContentElement/') {
                        $this->setRelativePathToInlineModel('Domain/Model/ContentElement/' . $name . '/');
                    } else {
                        $this->setRelativePathToInlineModel($this->getRelativePathToInlineModel() . $name . '/');
                    }

//                    add irre model
                    $inlineNameSpace = "Digitalwerk\DwBoilerplate\\" . str_replace('/' , '\\', substr($this->getRelativePathToInlineModel(), 0, -1)) ;
                    $inlineModel = "public/typo3conf/ext/dw_boilerplate/Classes/" . $this->getRelativePathToInlineModel() . $firstFieldItemName .".php";
                    $inlineModelContent = '<?php
declare(strict_types=1);
namespace '.$inlineNameSpace.';

' . ModelUtility::importClassToModel($inlineFields[$firstFieldItemType], $table) . '
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class ' . $firstFieldItemName . '
 * @package '.$inlineNameSpace.'
 */
class ' . $firstFieldItemName . ' extends AbstractEntity
{
    ' . ModelUtility::addConstantsToModel($inlineFields[$firstFieldItemType], $table) . '
    
    ' . ModelUtility::addFieldsToModel($inlineFields[$firstFieldItemType], $table, '', str_replace('/', '\\', $this->getRelativePathToInlineModel() . $firstFieldItemName)) . '
}';
                    if (!file_exists('public/typo3conf/ext/dw_boilerplate/Classes/' . substr($this->getRelativePathToInlineModel(), 0, -1))) {
                        mkdir('public/typo3conf/ext/dw_boilerplate/Classes/'. substr($this->getRelativePathToInlineModel(), 0, -1) , 0777, true);
                    }
                    file_put_contents($inlineModel, $inlineModelContent);

//                    Check if exist TCA for inline and add fields etc..

                    $inlineTCA = 'public/typo3conf/ext/dw_boilerplate/Configuration/TCA/Overrides/tx_contentelementregistry_domain_model_relation_' . $staticName . '_' . $firstFieldItemName . '.php';
                    $inlineTCAContent = '<?php
defined(\'TYPO3_MODE\') or die();

$tempTca = [
    \'ctrl\' => [
        \'typeicon_classes\' => [
            Digitalwerk\DwBoilerplate\ContentElement\\' . $staticName . '::CONTENT_RELATION_'.strtoupper($firstFieldItemName).' => Digitalwerk\DwBoilerplate\ContentElement\\' . $staticName . '::CONTENT_RELATION_'.strtoupper($firstFieldItemName).',
        ],
    ],
    \'types\' => [
        Digitalwerk\DwBoilerplate\ContentElement\\' . $staticName . '::CONTENT_RELATION_'.strtoupper($firstFieldItemName).' => [
            \'showitem\' => \'type, ' . TCAUtility::addFieldsToIRRETypeTCA($inlineFields[$firstFieldItemType], $firstFieldItemName, $table) . '
                           --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access, hidden, starttime, endtime, sys_language_uid, l10n_parent, l10n_diffsource\',
            \'columnsOverrides\' => [
' . TCAUtility::getDefaultFieldsWithAnotherTitle($table, $staticName, $firstFieldItemName, $inlineFields[$firstFieldItemType], '    ') . '
            ],
        ],
    ],
];

$GLOBALS[\'TCA\'][\'tx_contentelementregistry_domain_model_relation\'] = array_replace_recursive($GLOBALS[\'TCA\'][\'tx_contentelementregistry_domain_model_relation\'], $tempTca);

/**
 * tx_contentelementregistry_domain_model_relation new fields
 */
$'.lcfirst($firstFieldItemName).'Columns = [
    ' . TCAUtility::addColumnsToTCA($table, $staticName, $firstFieldItemName, $inlineFields[$firstFieldItemType], '\Digitalwerk\DwBoilerplate\\' . str_replace('/', '\\', $this->getRelativePathToInlineModel()) . $firstFieldItemName,'    ','dw_boilerplate', '    ', '\Digitalwerk\DwBoilerplate\ContentElement\\' . $staticName) . '
];
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(\'tx_contentelementregistry_domain_model_relation\', $'.lcfirst($firstFieldItemName).'Columns);  
';
                    file_put_contents($inlineTCA, $inlineTCAContent);


//        Add translations (title, description, fields name...) to public/typo3conf/ext/dw_boilerplate/Resources/Private/Language/locallang_db.xlf
                    TranslationUtility::addFieldsTitleToTranslation(
                        'public/typo3conf/ext/dw_boilerplate/Resources/Private/Language/locallang_db.xlf',
                        $table,
                        $staticName,
                        $firstFieldItemName,
                        $inlineFields[$firstFieldItemType],
                        'DwBoilerplate'
                    );

//        IRRE default icon
                    $inlineIcon = "public/typo3conf/ext/dw_boilerplate/Resources/Public/Icons/ContentElement/dwboilerplate_".strtolower($staticName)."_".strtolower($firstFieldItemName).".svg";
                    copy("public/typo3conf/ext/content_element_registry/Resources/Public/Icons/CEDefaultIcon.svg",$inlineIcon);
                    GeneralCreateCommandUtility::importStringInToFileAfterString(
                        'public/typo3conf/ext/dw_boilerplate/ext_localconf.php',
                        [
                            "                'ContentElement/dwboilerplate_" . strtolower($staticName) . "_" . strtolower($firstFieldItemName) . "', \n"
                        ],
                        [
                            "'GridElement/2ColumnGrid',",
                            "'GridElement/AccordionContainer',",
                            "'GridElement/AccordionContainerItem',",
                        ]
                    );

                    GeneralCreateCommandUtility::importStringInToFileAfterString(
                        'public/typo3conf/ext/dw_boilerplate/ext_typoscript_setup.typoscript',
                        [
                            ' ' . TypoScriptUtility::getTyposcriptMapping($firstFieldItemName, $inlineFields[$firstFieldItemType], $table, 'dwboilerplate_'.strtolower($staticName).'_'.strtolower($firstFieldItemName), 'Digitalwerk\DwBoilerplate\\'. str_replace('/', '\\',$this->getRelativePathToInlineModel() . $firstFieldItemName)) . "\n"
                        ],
                        [
                            'config.tx_extbase {',
                            'persistence {',
                            'classes {',
                        ]
                    );

//        Message with inline sql fields
                    if ((!empty($inlineFields[$firstFieldItemType]) || $inlineFields[$firstFieldItemType] !== '-') && !(GeneralCreateCommandUtility::areAllFieldsDefault($inlineFields[$firstFieldItemType], $table))) {
                        GeneralCreateCommandUtility::importStringInToFileAfterString(
                            'public/typo3conf/ext/dw_boilerplate/ext_tables.sql',
                            [
                                '    ' . (new SQLUtility)->addFieldsToSQLTable($inlineFields[$firstFieldItemType], $firstFieldItemName, $table). ", \n"
                            ],
                            [
                                "# Table structure for table 'tx_contentelementregistry_domain_model_relation'",
                                "#",
                                "CREATE TABLE tx_contentelementregistry_domain_model_relation (",
                            ]
                        );
                        $needToCompareDatabase = true;
                    }

                    self::checkAndAddInlineFields($staticName, $firstFieldItemName,$inlineFields[$firstFieldItemType], $inlineFields);
                }
            }
            return $needToCompareDatabase;
        }
    }
}
