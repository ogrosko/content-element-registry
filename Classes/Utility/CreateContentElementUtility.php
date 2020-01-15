<?php
namespace Digitalwerk\ContentElementRegistry\Utility;


use Digitalwerk\ContentElementRegistry\Command\TCAFieldTypesAndImportedClasses;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class CreateContentElementUtility
 * @package Digitalwerk\DwBoilerplate\Utility
 */
class CreateContentElementUtility
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
     * @param $fields
     * @param $type
     * @return bool
     */
    public static function areAFlexFormOnlyInCEFields($fields, $type)
    {
        if (!empty($fields)) {
            $fieldsToArray = GeneralUtility::makeInstance(GeneralCreateCommandUtility::class)->fieldsToArray($fields);
            $result = false;
            foreach ($fieldsToArray as $field) {
                $fieldType = explode(',', $field)[1];

                if ('pi_flexform' === $fieldType && $type === 'contentElementField') {
                    $result = true;
                }
                if ('pi_flexform' === $fieldType && $type === 'inlineRelationField') {
//                    You can not use flexForm and tx_contentelementregistry_relations in inline relation
                    throw new InvalidArgumentException('Field ' . $fieldType . ' you can use only as content element field.');
                }
            }
            return $result;
        }
    }

    /**
     * @param $fields
     * @param $type
     * @return bool
     */
    public static function areTxContentelementregistryRelationsOnlyInCEFields($fields, $type)
    {
        if (!empty($fields)) {
            $fieldsToArray = GeneralUtility::makeInstance(GeneralCreateCommandUtility::class)->fieldsToArray($fields);
            foreach ($fieldsToArray as $field) {
                $fieldType = explode(',', $field)[1];

                if ('tx_contentelementregistry_relations' === $fieldType && $type === 'inlineRelationField') {
//                    You can not use flexForm and tx_contentelementregistry_relations in inline relation
                    throw new InvalidArgumentException('Field ' . $fieldType . ' you can use only as content element field.');
                }
            }
        }
    }

    /**
     * @param $fields
     * @return bool
     */
    public static function areAllFieldsDefault($fields)
    {
        if (!empty($fields)) {
            $fieldsToArray = GeneralUtility::makeInstance(GeneralCreateCommandUtility::class)->fieldsToArray($fields);
            $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypesAndImportedClasses::class);

            foreach ($fieldsToArray as $field) {
                $fieldType = explode(',', $field)[1];

                if ($TCAFieldTypes->getTCAFieldTypes()['contentElementsAndInlineRelationFields'][$fieldType]['isFieldDefault'] === true) {
                } elseif ($TCAFieldTypes->getTCAFieldTypes()['contentElementsAndInlineRelationFields'][$fieldType]['isFieldDefault'] === false) {

                    return false;
                    break;
                }
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $fields
     * @param $name
     * @return string
     * Return full field's name => protected name (format string)
     */
    public static function addFieldsToClassMapping($fields, $name)
    {
        if (!empty($fields)) {
            $fieldsToArray = GeneralUtility::makeInstance(GeneralCreateCommandUtility::class)->fieldsToArray($fields);
            $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypesAndImportedClasses::class);
            $createdFields = [];

            foreach ($fieldsToArray as $field) {
                $fieldName = explode(',',$field)[0];
                $fieldType = explode(',', $field)[1];

                if ($fieldName === $fieldType && $TCAFieldTypes->getTCAFieldTypes()['contentElementsAndInlineRelationFields'][$fieldType]['isFieldDefault']) {
//                    Default fields
                } elseif ($fieldName !== $fieldType && $TCAFieldTypes->getTCAFieldTypes()['contentElementsAndInlineRelationFields'][$fieldType]['isFieldDefault']) {
                    $createdFields[] = '"' . $fieldType . '" => "' . str_replace(' ','',lcfirst(ucwords(str_replace('_',' ',$fieldName)))) . '"';
                } elseif ($TCAFieldTypes->getTCAFieldTypes()['contentElementsAndInlineRelationFields'][$fieldType]) {
                    $createdFields[] = '"'. strtolower($name).'_'.$fieldName.'" => "'.str_replace(' ','',lcfirst(ucwords(str_replace('_',' ',$fieldName)))) . '"';
                } else {
//                    ErrorMessage (FieldType does not exist)
                    throw new InvalidArgumentException('Field "' . $fieldType . '" does not exist.');
                }
            }

            return implode(',
        ', $createdFields);
        } else {
            return null;
        }
    }

    /**
     * @param $fields
     * @param $name
     * @return string
     * Return IRRE field's name (format string)
     */
    public static function addFieldsToIRRETypeTCA($fields, $name)
    {
        if (!empty($fields) || $fields !== '-') {
            $fieldsToArray = GeneralUtility::makeInstance(GeneralCreateCommandUtility::class)->fieldsToArray($fields);
            $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypesAndImportedClasses::class);
            $createdFields = [];

            foreach ($fieldsToArray as $field) {
                $fieldName = explode(',',$field)[0];
                $fieldType = explode(',', $field)[1];

                if ($TCAFieldTypes->getTCAFieldTypes()['contentElementsAndInlineRelationFields'][$fieldType]['isFieldDefault']) {
                    $createdFields[] = $fieldType;
                } elseif ($TCAFieldTypes->getTCAFieldTypes()['contentElementsAndInlineRelationFields'][$fieldType]['isFieldDefault'] === false) {
                    $createdFields[] = strtolower($name).'_'.$fieldName;
                } else {
//                    Fieldtype does not exist
                    throw new InvalidArgumentException('Field "' . $fieldType . '" does not exist.');
                }
            }

            return implode(', ', $createdFields) . ',';
        } else {
            return '';
        }
    }

    public static function getDefaultFieldsWithAnotherTitle($table, $contentElementName, $secondDesignation, $fields, $extraSpaces = '')
    {
        $fieldsToArray = GeneralUtility::makeInstance(GeneralCreateCommandUtility::class)->fieldsToArray($fields);
        $defaultFieldsWithAnotherTitle = [];
        $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypesAndImportedClasses::class);

        foreach ($fieldsToArray as $field) {
            $fieldName = explode(',',$field)[0];
            $fieldType = explode(',',$field)[1];
            $fieldTitle = explode(',',$field)[2];
            if ($fieldTitle !== $TCAFieldTypes->getTCAFieldTypes()['contentElementsAndInlineRelationFields'][$fieldType]['defaultFieldTitle'] && $TCAFieldTypes->getTCAFieldTypes()['contentElementsAndInlineRelationFields'][$fieldType]['isFieldDefault'])
            {
                if ($TCAFieldTypes->getTCAFieldTypes()['contentElementsAndInlineRelationFields'][$fieldType]['inlineFieldsAllowed']) {
                    $fieldItem = explode(';', explode('*', explode(',', $field)[3])[0]);

                    $defaultFieldsWithAnotherTitle[] =
$extraSpaces . '            \''.$fieldType.'\' => [
                '.$extraSpaces.'\'label\' => \'LLL:EXT:dw_boilerplate/Resources/Private/Language/locallang_db.xlf:' . $table . '.dwboilerplate_'.strtolower($contentElementName).'.'. strtolower($secondDesignation).'_'. strtolower($fieldName).'\',
                '.$extraSpaces.'\'config\' => [
                    '.$extraSpaces.'\'overrideChildTca\' => [
                        '.$extraSpaces.'\'columns\' => [
                            '.$extraSpaces.'\'type\' => [
                                '.$extraSpaces.'\'config\' => [
                                    '.$extraSpaces.'\'items\' => [
                                        [\'LLL:EXT:dw_boilerplate/Resources/Private/Language/locallang_db.xlf:tt_content.dwboilerplate_'.strtolower($contentElementName).'_'.strtolower($fieldItem[0]).'\', self::CONTENT_RELATION_'.strtoupper($fieldItem[0]).'],
                                    '.$extraSpaces.'],
                                    '.$extraSpaces.'\'default\' => self::CONTENT_RELATION_'.strtoupper($fieldItem[0]).'
                                '.$extraSpaces.'],
                            '.$extraSpaces.'],
                        '.$extraSpaces.'],
                    '.$extraSpaces.'],
                '.$extraSpaces.'],
            '.$extraSpaces.'],';
                    GeneralCreateCommandUtility::addTitleToTranslation('public/typo3conf/ext/dw_boilerplate/Resources/Private/Language/locallang_db.xlf','tt_content.dwboilerplate_'.strtolower($contentElementName).'_'.strtolower($fieldItem[0]),str_replace('-', ' ', $fieldItem[2]));
                } else {
                    $defaultFieldsWithAnotherTitle[] =
$extraSpaces . '            \''.$fieldType.'\' => [
                '.$extraSpaces.'\'label\' => \'LLL:EXT:dw_boilerplate/Resources/Private/Language/locallang_db.xlf:' . $table . '.dwboilerplate_'.strtolower($contentElementName).'.'. strtolower($secondDesignation).'_'. strtolower($fieldName).'\',
            '.$extraSpaces.'],';
                }

            }


        }

        return implode('
', $defaultFieldsWithAnotherTitle);
    }

    /**
     * @param $staticName
     * @param $name
     * @param $fields
     * @param $commandFieldType
     * @param array $inlineFields
     * @return string
     * Only for CE command
     */
    public function checkAndAddInlineFields($staticName, $name, $fields, $commandFieldType, array $inlineFields)
    {
        if (!empty($fields)) {
            $needToCompareDatabase = false;
            $fieldsToArray = (new GeneralCreateCommandUtility)->fieldsToArray($fields);
            $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypesAndImportedClasses::class);
            foreach ($fieldsToArray as $field) {
                $fieldType = explode(',', $field)[1];

                if ($TCAFieldTypes->getTCAFieldTypes()[$commandFieldType][$fieldType]['inlineFieldsAllowed']) {
                    $fieldItem = explode(';', explode('*', explode(',', $field)[3])[0]);

                    CreateContentElementUtility::areAFlexFormOnlyInCEFields($inlineFields[$fieldItem[1]], 'inlineRelationField');
                    CreateContentElementUtility::areTxContentelementregistryRelationsOnlyInCEFields($inlineFields[$fieldItem[1]], 'inlineRelationField');


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

' . GeneralCreateCommandUtility::importClassToModel($inlineFields[$fieldItem[1]], 'contentElementsAndInlineRelationFields') . '
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class ' . $fieldItem[0] . '
 * @package '.$inlineNameSpace.'
 */
class ' . $fieldItem[0] . ' extends AbstractEntity
{
    ' . GeneralCreateCommandUtility::addConstantsToModel($inlineFields[$fieldItem[1]], 'contentElementsAndInlineRelationFields') . '
    
    ' . GeneralCreateCommandUtility::addFieldsToModel($inlineFields[$fieldItem[1]], 'contentElementsAndInlineRelationFields', $staticName, '', str_replace('/', '\\', $this->getRelativePathToInlineModel() . $fieldItem[0])) . '
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
            \'showitem\' => \'type, '.CreateContentElementUtility::addFieldsToIRRETypeTCA($inlineFields[$fieldItem[1]], $fieldItem[0]).'
                           --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access, hidden, starttime, endtime, sys_language_uid, l10n_parent, l10n_diffsource\',
            \'columnsOverrides\' => [
' . CreateContentElementUtility::getDefaultFieldsWithAnotherTitle('tx_contentelementregistry_domain_model_relation', $staticName, $fieldItem[0], $inlineFields[$fieldItem[1]], '    ') . '
            ],
        ],
    ],
];

$GLOBALS[\'TCA\'][\'tx_contentelementregistry_domain_model_relation\'] = array_replace_recursive($GLOBALS[\'TCA\'][\'tx_contentelementregistry_domain_model_relation\'], $tempTca);

/**
 * tx_contentelementregistry_domain_model_relation new fields
 */
$'.lcfirst($fieldItem[0]).'Columns = [
    ' . GeneralCreateCommandUtility::addColumnsToTCA('tx_contentelementregistry_domain_model_relation', $staticName, $fieldItem[0], $inlineFields[$fieldItem[1]], '\Digitalwerk\DwBoilerplate\\' . str_replace('/', '\\', $this->getRelativePathToInlineModel()) . $fieldItem[0], 'contentElementsAndInlineRelationFields','    ','dw_boilerplate', '', '\Digitalwerk\DwBoilerplate\ContentElement\\' . $staticName) . '
];
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(\'tx_contentelementregistry_domain_model_relation\', $'.lcfirst($fieldItem[0]).'Columns);  
';
                    file_put_contents($inlineTCA, $inlineTCAContent);


//        Add translations (title, description, fields name...) to public/typo3conf/ext/dw_boilerplate/Resources/Private/Language/locallang_db.xlf
                    GeneralCreateCommandUtility::addFieldsTitleToTranslation(
                        'public/typo3conf/ext/dw_boilerplate/Resources/Private/Language/locallang_db.xlf',
                        'tx_contentelementregistry_domain_model_relation',
                        $staticName,
                        $fieldItem[0],
                        $inlineFields[$fieldItem[1]],
                        'contentElementsAndInlineRelationFields',
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
                            ' ' . GeneralCreateCommandUtility::getTyposcriptMapping($fieldItem[0], $inlineFields[$fieldItem[1]], 'contentElementsAndInlineRelationFields', 'tx_contentelementregistry_domain_model_relation', 'dwboilerplate_'.strtolower($staticName).'_'.strtolower($fieldItem[0]), 'Digitalwerk\DwBoilerplate\\'. str_replace('/', '\\',$this->getRelativePathToInlineModel() . $fieldItem[0])) . "\n"
                        ],
                        [
                            'config.tx_extbase {',
                            'persistence {',
                            'classes {',
                        ]
                    );

//        Message with inline sql fields
                    if ((!empty($inlineFields[$fieldItem[1]]) || $inlineFields[$fieldItem[1]] !== '-') && !(CreateContentElementUtility::areAllFieldsDefault($inlineFields[$fieldItem[1]]))) {
                        GeneralCreateCommandUtility::importStringInToFileAfterString(
                            'public/typo3conf/ext/dw_boilerplate/ext_tables.sql',
                            [
                                '    ' . GeneralCreateCommandUtility::addFieldsToTable($inlineFields[$fieldItem[1]], $fieldItem[0], 'contentElementsAndInlineRelationFields'). ", \n"
                            ],
                            [
                                "# Table structure for table 'tx_contentelementregistry_domain_model_relation'",
                                "#",
                                "CREATE TABLE tx_contentelementregistry_domain_model_relation (",
                            ]
                        );
                        $needToCompareDatabase = true;
                    }

                    self::checkAndAddInlineFields($staticName, $fieldItem[0],$inlineFields[$fieldItem[1]],'contentElementsAndInlineRelationFields', $inlineFields);
                }
            }
            return $needToCompareDatabase;
        }
    }
}
