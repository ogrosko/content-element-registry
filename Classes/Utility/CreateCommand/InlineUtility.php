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
                    $fieldItem = $generalCreateCommandUtility->getFirstFieldItem($field);

//                    add constant
                    GeneralCreateCommandUtility::importStringInToFileAfterString(
                        'public/typo3conf/ext/dw_boilerplate/Classes/ContentElement/' . $staticName . '.php',
                        ['   const CONTENT_RELATION_' . strtoupper($fieldItem[0]) . ' = \'dwboilerplate_' . strtolower($staticName) . '_' . strtolower(($fieldItem[0])) . '\';' . "\n"],
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
                    $inlineModel = "public/typo3conf/ext/dw_boilerplate/Classes/" . $this->getRelativePathToInlineModel() . $fieldItem[0] .".php";
                    $inlineModelContent = '<?php
declare(strict_types=1);
namespace '.$inlineNameSpace.';

' . ModelUtility::importClassToModel($inlineFields[$fieldItem[1]], $table) . '
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class ' . $fieldItem[0] . '
 * @package '.$inlineNameSpace.'
 */
class ' . $fieldItem[0] . ' extends AbstractEntity
{
    ' . ModelUtility::addConstantsToModel($inlineFields[$fieldItem[1]], $table) . '
    
    ' . ModelUtility::addFieldsToModel($inlineFields[$fieldItem[1]], $table, '', str_replace('/', '\\', $this->getRelativePathToInlineModel() . $fieldItem[0])) . '
}';
                    if (!file_exists('public/typo3conf/ext/dw_boilerplate/Classes/' . substr($this->getRelativePathToInlineModel(), 0, -1))) {
                        mkdir('public/typo3conf/ext/dw_boilerplate/Classes/'. substr($this->getRelativePathToInlineModel(), 0, -1) , 0777, true);
                    }
                    file_put_contents($inlineModel, $inlineModelContent);

//                    Check if exist TCA for inline and add fields etc..

                    $inlineTCA = 'public/typo3conf/ext/dw_boilerplate/Configuration/TCA/Overrides/tx_contentelementregistry_domain_model_relation_' . $staticName . '_' . $fieldItem[0] . '.php';
                    $inlineTCAContent = '<?php
defined(\'TYPO3_MODE\') or die();

$tempTca = [
    \'ctrl\' => [
        \'typeicon_classes\' => [
            Digitalwerk\DwBoilerplate\ContentElement\\' . $staticName . '::CONTENT_RELATION_'.strtoupper($fieldItem[0]).' => Digitalwerk\DwBoilerplate\ContentElement\\' . $staticName . '::CONTENT_RELATION_'.strtoupper($fieldItem[0]).',
        ],
    ],
    \'types\' => [
        Digitalwerk\DwBoilerplate\ContentElement\\' . $staticName . '::CONTENT_RELATION_'.strtoupper($fieldItem[0]).' => [
            \'showitem\' => \'type, ' . TCAUtility::addFieldsToIRRETypeTCA($inlineFields[$fieldItem[1]], $fieldItem[0], $table) . '
                           --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access, hidden, starttime, endtime, sys_language_uid, l10n_parent, l10n_diffsource\',
            \'columnsOverrides\' => [
' . TCAUtility::getDefaultFieldsWithAnotherTitle($table, $staticName, $fieldItem[0], $inlineFields[$fieldItem[1]], '    ') . '
            ],
        ],
    ],
];

$GLOBALS[\'TCA\'][\'tx_contentelementregistry_domain_model_relation\'] = array_replace_recursive($GLOBALS[\'TCA\'][\'tx_contentelementregistry_domain_model_relation\'], $tempTca);

/**
 * tx_contentelementregistry_domain_model_relation new fields
 */
$'.lcfirst($fieldItem[0]).'Columns = [
    ' . TCAUtility::addColumnsToTCA($table, $staticName, $fieldItem[0], $inlineFields[$fieldItem[1]], '\Digitalwerk\DwBoilerplate\\' . str_replace('/', '\\', $this->getRelativePathToInlineModel()) . $fieldItem[0],'    ','dw_boilerplate', '    ', '\Digitalwerk\DwBoilerplate\ContentElement\\' . $staticName) . '
];
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(\'tx_contentelementregistry_domain_model_relation\', $'.lcfirst($fieldItem[0]).'Columns);  
';
                    file_put_contents($inlineTCA, $inlineTCAContent);


//        Add translations (title, description, fields name...) to public/typo3conf/ext/dw_boilerplate/Resources/Private/Language/locallang_db.xlf
                    TranslationUtility::addFieldsTitleToTranslation(
                        'public/typo3conf/ext/dw_boilerplate/Resources/Private/Language/locallang_db.xlf',
                        $table,
                        $staticName,
                        $fieldItem[0],
                        $inlineFields[$fieldItem[1]],
                        'DwBoilerplate'
                    );

//        IRRE default icon
                    $inlineIcon = "public/typo3conf/ext/dw_boilerplate/Resources/Public/Icons/ContentElement/dwboilerplate_".strtolower($staticName)."_".strtolower($fieldItem[0]).".svg";
                    copy("public/typo3conf/ext/content_element_registry/Resources/Public/Icons/CEDefaultIcon.svg",$inlineIcon);
                    GeneralCreateCommandUtility::importStringInToFileAfterString(
                        'public/typo3conf/ext/dw_boilerplate/ext_localconf.php',
                        [
                            "                'ContentElement/dwboilerplate_" . strtolower($staticName) . "_" . strtolower($fieldItem[0]) . "', \n"
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
                            ' ' . TypoScriptUtility::getTyposcriptMapping($fieldItem[0], $inlineFields[$fieldItem[1]], $table, 'dwboilerplate_'.strtolower($staticName).'_'.strtolower($fieldItem[0]), 'Digitalwerk\DwBoilerplate\\'. str_replace('/', '\\',$this->getRelativePathToInlineModel() . $fieldItem[0])) . "\n"
                        ],
                        [
                            'config.tx_extbase {',
                            'persistence {',
                            'classes {',
                        ]
                    );

//        Message with inline sql fields
                    if ((!empty($inlineFields[$fieldItem[1]]) || $inlineFields[$fieldItem[1]] !== '-') && !(GeneralCreateCommandUtility::areAllFieldsDefault($inlineFields[$fieldItem[1]], $table))) {
                        GeneralCreateCommandUtility::importStringInToFileAfterString(
                            'public/typo3conf/ext/dw_boilerplate/ext_tables.sql',
                            [
                                '    ' . SQLUtility::addFieldsToSQLTable($inlineFields[$fieldItem[1]], $fieldItem[0], $table). ", \n"
                            ],
                            [
                                "# Table structure for table 'tx_contentelementregistry_domain_model_relation'",
                                "#",
                                "CREATE TABLE tx_contentelementregistry_domain_model_relation (",
                            ]
                        );
                        $needToCompareDatabase = true;
                    }

                    self::checkAndAddInlineFields($staticName, $fieldItem[0],$inlineFields[$fieldItem[1]], $inlineFields);
                }
            }
            return $needToCompareDatabase;
        }
    }
}
