<?php
namespace Digitalwerk\ContentElementRegistry\Command\CreateCommand\Config;

use Digitalwerk\ContentElementRegistry\Utility\CreateCommand\TCAUtility;
use Digitalwerk\ContentElementRegistry\Utility\CreateCommand\TranslationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class TCAFieldTypes
 * @package Digitalwerk\ContentElementRegistry\Command\CreateCommand\Config
 */
class TCAFieldTypes
{
    /**
     * @var array
     */
    protected $defaultFieldTypes = [];

    /**
     * @var array
     */
    protected $newFieldTypes = [];

    /**
     * @return array|null
     */
    public function getDefaultFieldTypes(): ? array
    {
        return $this->defaultFieldTypes;
    }

    /**
     * @param array $defaultFieldTypes
     */
    public function setDefaultFieldTypes(array $defaultFieldTypes): void
    {
        $this->defaultFieldTypes = $defaultFieldTypes;
    }

    /**
     * @return array|null
     */
    public function getNewFieldTypes(): ? array
    {
        return $this->newFieldTypes;
    }

    /**
     * @param array $newFieldTypes
     */
    public function setNewFieldTypes(array $newFieldTypes): void
    {
        $this->newFieldTypes = $newFieldTypes;
    }

    /**
     * @param string $table
     * @param string $contentElementOrPageTypeName
     * @param string $secondDesignation
     * @param string $fieldName
     * @param array $fieldsToArray
     * @param string $relativePath
     * @param string $fieldTypeConfig
     * @param string $fieldItem
     * @param string $relativePathToClass
     * @param string $inlineRelativePath
     * @return array
     */
    public function getTCAFieldTypes($table, $contentElementOrPageTypeName = '', $secondDesignation = '', $fieldName = '', $fieldsToArray = [], $relativePath = '', $fieldTypeConfig = '', $fieldItem = '', $relativePathToClass = '', $inlineRelativePath = ''): array
    {
        if (empty($this->getDefaultFieldTypes()) || $fieldItem !== '') {
            self::getDefaultTCAFieldTypes($table, $inlineRelativePath, $fieldItem);
        }

        if (empty($this->getNewFieldTypes()) || $fieldItem !== '') {
            self::getTypo3NewCustomFieldTypes($table, $contentElementOrPageTypeName, $secondDesignation, $fieldName , $fieldsToArray, $relativePath, $fieldTypeConfig, $fieldItem, $relativePathToClass, $inlineRelativePath);
        }

        return [
            $table => array_merge($this->getNewFieldTypes(),$this->getDefaultFieldTypes()),
        ];
    }


    /**
     * @param $table
     * @param string $inlineRelativePath
     * @param string $fieldItem
     * @param bool $allowReturn
     * @return array
     */
    public function getDefaultTCAFieldTypes($table, $inlineRelativePath = '', $fieldItem = '', $allowReturn = false)
    {
        $defaultFieldTypes = $GLOBALS['TCA'][$table]['columns'];
        $result = [];
        $importedClasses = GeneralUtility::makeInstance(ImportedClasses::class);
        foreach (array_keys($defaultFieldTypes) as $defaultFieldType) {
            if (!in_array($defaultFieldType, $result)) {
                $defaultFiedTypeTitle = $defaultFieldTypes[$defaultFieldType]['label'];
                if ($defaultFiedTypeTitle) {
                    $file = 'public/typo3conf/ext/' . explode(':', $defaultFiedTypeTitle)[2];
                    $file = file_exists($file) ? $file : 'public/typo3/sysext/' . explode(':', $defaultFiedTypeTitle)[2];
                    if (file_exists($file)) {
                        $defaultFiedTypeTitle = TranslationUtility::getSourceByFileNameAndId($file, explode(':', $defaultFiedTypeTitle)[3]);

                        $result[$defaultFieldType] = [
                            'isFieldDefault' => true,
                            'defaultFieldName' => $defaultFieldType,
                            'defaultFieldTitle' => str_replace(' ', '-', $defaultFiedTypeTitle),
                            'tableFieldDataType' => null,
                            'config' => null,
                        ];

                        $result[$defaultFieldType]['TCAItemsAllowed'] = $defaultFieldTypes[$defaultFieldType]['config']['items'] ? true : false;

                        if ($importedClasses->getClasses()[$defaultFieldType . 'Trait']) {
                            $result[$defaultFieldType]['needImportClass'] = true;
                            $result[$defaultFieldType]['importClassConditional']['needDefaulFieldName'] = true;
                            $result[$defaultFieldType]['importClass'][] = $defaultFieldType . 'Trait';
                            $result[$defaultFieldType]['trait'] = $defaultFieldType . 'Trait';
                        }

                        if ($defaultFieldTypes[$defaultFieldType]['config']['type'] === 'inline') {
//                            Default model property for inline
                            $result[$defaultFieldType]['needImportClass'] = true;
                            $result[$defaultFieldType]['importClassConditional']['needDefaulFieldName'] = false;
                            $result[$defaultFieldType]['importClass'][] = 'objectStorage';
                            if ($defaultFieldTypes[$defaultFieldType]['config']['foreign_table_field'] !== 'tablenames') {
                                $result[$defaultFieldType]['modelDataTypes']['propertyDataType'] = 'null';
                                $result[$defaultFieldType]['modelDataTypes']['propertyDataTypeDescribe'] = '\TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Digitalwerk\DwBoilerplate\\' . $inlineRelativePath . '\\' . $fieldItem[0] . '>';
                                $result[$defaultFieldType]['modelDataTypes']['getterDataTypeDescribe'] = 'ObjectStorage';
                                $result[$defaultFieldType]['modelDataTypes']['getterDataType'] = '? ObjectStorage';
                                $result[$defaultFieldType]['inlineFieldsAllowed'] = true;
                            } else {
                                if ($defaultFieldTypes[$defaultFieldType]['config']['maxitems'] === 1) {
                                    $result[$defaultFieldType]['needImportClass'] = $importedClasses->getClasses()[$defaultFieldType . 'Trait'] ? true : false;
                                    $result[$defaultFieldType]['modelDataTypes']['propertyDataType'] = 'null';
                                    $result[$defaultFieldType]['modelDataTypes']['propertyDataTypeDescribe'] = '\TYPO3\CMS\Extbase\Domain\Model\FileReference';
                                    $result[$defaultFieldType]['modelDataTypes']['getterDataTypeDescribe'] = 'FileReference';
                                    $result[$defaultFieldType]['modelDataTypes']['getterDataType'] = '? FileReference';
                                } else {
                                    $result[$defaultFieldType]['modelDataTypes']['propertyDataType'] = 'null';
                                    $result[$defaultFieldType]['modelDataTypes']['propertyDataTypeDescribe'] = '\TYPO3\CMS\Extbase\Persistence\ObjectStorage';
                                    $result[$defaultFieldType]['modelDataTypes']['getterDataTypeDescribe'] = 'ObjectStorage';
                                    $result[$defaultFieldType]['modelDataTypes']['getterDataType'] = '? ObjectStorage';
                                }
                            }
                        } elseif ($defaultFieldTypes[$defaultFieldType]['config']['type'] === 'group') {
//                            Default model property for group
                            $result[$defaultFieldType]['needImportClass'] = true;
                            $result[$defaultFieldType]['importClassConditional']['needDefaulFieldName'] = false;
                            $result[$defaultFieldType]['importClass'][] = 'objectStorage';

                            $result[$defaultFieldType]['modelDataTypes']['propertyDataType'] = 'null';
                            $result[$defaultFieldType]['modelDataTypes']['propertyDataTypeDescribe'] = '\TYPO3\CMS\Extbase\Persistence\ObjectStorage';
                            $result[$defaultFieldType]['modelDataTypes']['getterDataTypeDescribe'] = 'ObjectStorage';
                            $result[$defaultFieldType]['modelDataTypes']['getterDataType'] = '? ObjectStorage';
                        } elseif ($defaultFieldTypes[$defaultFieldType]['config']['type'] === 'flex') {
//                            Default model property for flex
                            $result[$defaultFieldType]['FlexFormItemsAllowed'] = true;
                            $result[$defaultFieldType]['modelDataTypes']['propertyDataType'] = '""';
                            $result[$defaultFieldType]['modelDataTypes']['propertyDataTypeDescribe'] = 'string';
                            $result[$defaultFieldType]['modelDataTypes']['getterDataTypeDescribe'] = 'array';
                            $result[$defaultFieldType]['modelDataTypes']['getterDataType'] = '? array';
                        } elseif ($defaultFieldTypes[$defaultFieldType]['config']['type'] === 'text' || $defaultFieldTypes[$defaultFieldType]['config']['type'] === 'input') {
//                            Default model property for input and text
                            $result[$defaultFieldType]['modelDataTypes']['propertyDataType'] = '""';
                            $result[$defaultFieldType]['modelDataTypes']['propertyDataTypeDescribe'] = 'string';
                            $result[$defaultFieldType]['modelDataTypes']['getterDataTypeDescribe'] = 'string';
                            $result[$defaultFieldType]['modelDataTypes']['getterDataType'] = 'string';
                        }
                    }
                }
            }
        }

        if ($allowReturn) {
            return $result;
        }
        $this->defaultFieldTypes = $result;
    }

    /**
     * @param string $table
     * @param string $contentElementOrPageTypeName
     * @param string $secondDesignation
     * @param string $fieldName
     * @param array $fieldsToArray
     * @param string $relativePath
     * @param string $fieldTypeConfig
     * @param string $fieldItem
     * @param string $relativePathToClass
     * @param string $inlineRelativePath
     */
    public function getTypo3NewCustomFieldTypes($table, $contentElementOrPageTypeName, $secondDesignation, $fieldName, $fieldsToArray, $relativePath, $fieldTypeConfig, $fieldItem, $relativePathToClass, $inlineRelativePath)
    {
        $selectConfig = null;
        $checkConfig = null;
        $radioConfig = null;
        $inlineConfig = null;
        if ($fieldTypeConfig === 'select' && $table === 'tt_content') {
            $selectConfig = $this->getSelectConfig($table, $contentElementOrPageTypeName, $secondDesignation, 'dw_boilerplate', $fieldsToArray, $relativePath);
        }
        if ($fieldTypeConfig === 'inline' && $table === 'tt_content') {
            $inlineConfig = $this->getInlineConfig($relativePathToClass, $contentElementOrPageTypeName, $fieldItem, $contentElementOrPageTypeName);
        }
        if ($fieldTypeConfig === 'check' && $table === 'tt_content') {
            $checkConfig = $this->getCheckConfig($table, $contentElementOrPageTypeName, $secondDesignation, 'dw_boilerplate', $fieldsToArray, $relativePath);
        }
        if ($fieldTypeConfig === 'radio' && $table === 'tt_content') {
            $radioConfig = $this->getRadioConfig($table, $contentElementOrPageTypeName, $secondDesignation, 'dw_boilerplate', $fieldsToArray, $relativePath);
        }

        if ($fieldTypeConfig === 'select' && $table === 'tx_contentelementregistry_domain_model_relation') {
            $selectConfig = $this->getSelectConfig($table, $contentElementOrPageTypeName, $secondDesignation, 'dw_boilerplate', $fieldsToArray, $relativePath);
        }
        if ($fieldTypeConfig === 'inline' && $table === 'tx_contentelementregistry_domain_model_relation') {
            $inlineConfig = $this->getInlineConfig($relativePathToClass, $contentElementOrPageTypeName, $fieldItem, $contentElementOrPageTypeName);
        }
        if ($fieldTypeConfig === 'check' && $table === 'tx_contentelementregistry_domain_model_relation') {
            $checkConfig = $this->getCheckConfig($table, $contentElementOrPageTypeName, $secondDesignation, 'dw_boilerplate', $fieldsToArray, $relativePath);
        }
        if ($fieldTypeConfig === 'radio' && $table === 'tx_contentelementregistry_domain_model_relation') {
            $radioConfig = $this->getRadioConfig($table, $contentElementOrPageTypeName, $secondDesignation, 'dw_boilerplate', $fieldsToArray, $relativePath);
        }

        if ($fieldTypeConfig === 'select' && $table === 'pages') {
            $selectConfig = $this->getSelectConfig($table, $contentElementOrPageTypeName, $secondDesignation, 'dw_page_types', $fieldsToArray, $relativePath, '    ');
        }
        if ($fieldTypeConfig === 'check' && $table === 'pages') {
            $checkConfig = $this->getCheckConfig($table, $contentElementOrPageTypeName, $secondDesignation, 'dw_page_types', $fieldsToArray, $relativePath, '    ');
        }
        if ($fieldTypeConfig === 'radio' && $table === 'pages') {
            $radioConfig = $this->getRadioConfig($table, $contentElementOrPageTypeName, $secondDesignation, 'dw_page_types', $fieldsToArray, $relativePath, '    ');
        }

        $this->newFieldTypes = [
            'input' => [
                'isFieldDefault' => false,
                'defaultFieldName' => null,
                'tableFieldDataType' => 'varchar(255) DEFAULT \'\' NOT NULL',
                'config' => $this->getInputConfig(),
                'TCAItemsAllowed' => false,
                'needImportClass' => false,
                'modelDataTypes' => [
                    'propertyDataType' => "''",
                    'propertyDataTypeDescribe' => 'string',
                    'getterDataTypeDescribe' => 'string',
                    'getterDataType' => 'string',
                ],
                'trait' => null
            ],
            'select' => [
                'isFieldDefault' => false,
                'defaultFieldName' => null,
                'tableFieldDataType' => 'int(11) DEFAULT 0 NOT NULL',
                'config' => $selectConfig,
                'TCAItemsAllowed' => true,
                'needImportClass' => false,
                'modelDataTypes' => [
                    'propertyDataType' => "0",
                    'propertyDataTypeDescribe' => 'int',
                    'getterDataTypeDescribe' => 'int',
                    'getterDataType' => '? int',
                ],
                'trait' => null
            ],
            'fal' => [
                'isFieldDefault' => false,
                'defaultFieldName' => true,
                'tableFieldDataType' => 'int(11) DEFAULT 0 NOT NULL',
                'config' => $fieldName ? $this->getFalConfig($secondDesignation, $fieldName) : null,
                'TCAItemsAllowed' => false,
                'needImportClass' => true,
                'importClassConditional' => [
                    'needDefaulFieldName' => false
                ],
                'importClass' => [
                    'objectStorage',
                ],
                'modelDataTypes' => [
                    'propertyDataType' => "null",
                    'propertyDataTypeDescribe' => '\TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>',
                    'getterDataTypeDescribe' => 'ObjectStorage',
                    'getterDataType' => '? ObjectStorage',
                ],
                'trait' => null
            ],
            'radio' => [
                'isFieldDefault' => false,
                'defaultFieldName' => null,
                'tableFieldDataType' => 'int(11) DEFAULT 0 NOT NULL',
                'config' => $radioConfig,
                'TCAItemsAllowed' => true,
                'needImportClass' => false,
                'modelDataTypes' => [
                    'propertyDataType' => "0",
                    'propertyDataTypeDescribe' => 'int',
                    'getterDataTypeDescribe' => 'int',
                    'getterDataType' => '? int',
                ],
                'trait' => null
            ],
            'textarea' => [
                'isFieldDefault' => false,
                'defaultFieldName' => null,
                'tableFieldDataType' => 'text',
                'config' => $this->getTextAreaConfig(),
                'TCAItemsAllowed' => false,
                'needImportClass' => false,
                'modelDataTypes' => [
                    'propertyDataType' => "''",
                    'propertyDataTypeDescribe' => 'string',
                    'getterDataTypeDescribe' => 'string',
                    'getterDataType' => 'string',
                ],
                'trait' => null
            ],
            'check' => [
                'isFieldDefault' => false,
                'defaultFieldName' => null,
                'tableFieldDataType' => 'int(11) DEFAULT 0 NOT NULL',
                'config' => $checkConfig,
                'TCAItemsAllowed' => true,
                'needImportClass' => false,
                'modelDataTypes' => [
                    'propertyDataType' => "0",
                    'propertyDataTypeDescribe' => 'int',
                    'getterDataTypeDescribe' => 'int',
                    'getterDataType' => '? int',
                ],
                'trait' => null
            ],
            'group' => [
                'isFieldDefault' => false,
                'defaultFieldName' => null,
                'tableFieldDataType' => 'varchar(255) DEFAULT \'\' NOT NULL',
                'config' => $this->getGroupConfig(),
                'TCAItemsAllowed' => false,
                'needImportClass' => true,
                'importClassConditional' => [
                    'needDefaulFieldName' => false
                ],
                'importClass' => [
                    'objectStorage',
                ],
                'modelDataTypes' => [
                    'propertyDataType' => "null",
                    'propertyDataTypeDescribe' => '\TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Digitalwerk\DwPageTypes\Domain\Model\News>',
                    'getterDataTypeDescribe' => 'ObjectStorage',
                    'getterDataType' => '? ObjectStorage',
                ],
                'trait' => null
            ],
            'inline' => [
                'isFieldDefault' => false,
                'defaultFieldName' => null,
                'defaultFieldTitle' => null,
                'tableFieldDataType' => 'int(11) unsigned DEFAULT \'0\' NOT NULL',
                'config' => $inlineConfig,
                'TCAItemsAllowed' => false,
                'FlexFormItemsAllowed' => false,
                'needImportClass' => true,
                'importClassConditional' => [
                    'needDefaulFieldName' => false
                ],
                'importClass' => [
                    'objectStorage',
                ],
                'modelDataTypes' => [
                    'propertyDataType' => "null",
                    'propertyDataTypeDescribe' => '\TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Digitalwerk\DwBoilerplate\\' . $inlineRelativePath . '\\' . $fieldItem[0] . '>',
                    'getterDataTypeDescribe' => 'ObjectStorage',
                    'getterDataType' => '? ObjectStorage',
                ],
                'trait' => null,
                'inlineFieldsAllowed' => true
            ],
        ];
    }

    /**
     * @param string $extraSpaces
     * @return string
     */
    public function getInputConfig($extraSpaces = ''): string
    {
        return '[
            ' . $extraSpaces. '\'type\' => \'input\',
            ' . $extraSpaces. '\'eval\' => \'trim\',
            ' . $extraSpaces. '\'max\' => 255,
        ' . $extraSpaces. '],';
    }

    /**
     * @param $table
     * @param $contentElementName
     * @param $secondDesignation
     * @param $extensionName
     * @param $fieldsToArray
     * @param $relativePath
     * @param string $extraSpaces
     * @return string
     */
    public function getCheckConfig($table, $contentElementName, $secondDesignation, $extensionName, $fieldsToArray, $relativePath, $extraSpaces = ''): string
    {
        return '[
             ' . $extraSpaces. '\'type\' => \'check\',
             ' . $extraSpaces. '\'items\' => [
                  ' . $extraSpaces . TCAUtility::addFieldsItemsToTCA($fieldsToArray, $table, $extensionName,$contentElementName, $secondDesignation, $relativePath, $extraSpaces) . '
              ' . $extraSpaces. '],
              ' . $extraSpaces. '\'cols\' => \'3\',
        ' . $extraSpaces. '],';
    }

    /**
     * @param string $extraSpaces
     * @return string
     */
    public function getTextAreaConfig($extraSpaces = ''): string
    {
        return '[
            ' . $extraSpaces. '\'type\' => \'text\',
            ' . $extraSpaces. '\'enableRichtext\' => true,
        ' . $extraSpaces. '],';
    }

    /**
     * @param $pathToClass
     * @param $fieldName
     * @param $fieldItem
     * @param $contentElementName
     * @param string $extraSpaces
     * @return string
     */
    public function getInlineConfig($pathToClass,$fieldName, $fieldItem, $contentElementName, $extraSpaces = ''): string
    {
        TranslationUtility::addStringToTranslation(
            'public/typo3conf/ext/dw_boilerplate/Resources/Private/Language/locallang_db.xlf',
            'tt_content.dwboilerplate_' . strtolower($fieldName) . '_' . strtolower($fieldItem[0]),
            str_replace('-', ' ', $fieldItem[2])
            );
        return '[
            ' . $extraSpaces. '\'type\' => \'inline\',
            ' . $extraSpaces. '\'foreign_table\' => \'tx_contentelementregistry_domain_model_relation\',
            ' . $extraSpaces. '\'foreign_field\' => \'content_element\',
            ' . $extraSpaces. '\'foreign_sortby\' => \'sorting\',
            ' . $extraSpaces. '\'foreign_match_fields\' => [
                ' . $extraSpaces. '\'type\' => \Digitalwerk\DwBoilerplate\ContentElement\\' . $contentElementName . '::CONTENT_RELATION_' . strtoupper($fieldItem[0]) . ',
            ' . $extraSpaces. '],
            ' . $extraSpaces. '\'maxitems\' => 9999,
            ' . $extraSpaces. '\'appearance\' => [
                ' . $extraSpaces. '\'useSortable\' => true,
                ' . $extraSpaces. '\'collapseAll\' => 1,
                ' . $extraSpaces. '\'levelLinksPosition\' => \'top\',
                ' . $extraSpaces. '\'showSynchronizationLink\' => 1,
                ' . $extraSpaces. '\'showPossibleLocalizationRecords\' => 1,
                ' . $extraSpaces. '\'showAllLocalizationLink\' => 1
            ' . $extraSpaces. '],
            ' . $extraSpaces. '\'overrideChildTca\' => [
                ' . $extraSpaces. '\'columns\' => [
                    ' . $extraSpaces. '\'type\' => [
                        ' . $extraSpaces. '\'config\' => [
                            ' . $extraSpaces. '\'items\' => [
                                ' . $extraSpaces. '[\'LLL:EXT:dw_boilerplate/Resources/Private/Language/locallang_db.xlf:tt_content.dwboilerplate_' . strtolower($fieldName) . '_' . strtolower($fieldItem[0]) . '\', ' . $pathToClass . '::CONTENT_RELATION_' . strtoupper($fieldItem[0]) . '],
                            ' . $extraSpaces. '],
                            ' . $extraSpaces. '\'default\' => ' . $pathToClass . '::CONTENT_RELATION_' . strtoupper($fieldItem[0]) . '
                        ' . $extraSpaces. '],
                    ' . $extraSpaces. '],
                ' . $extraSpaces. '],
            ' . $extraSpaces. '],
        ' . $extraSpaces. ']';
    }

    /**
     * @param string $extraSpaces
     * @return string
     */
    public function getGroupConfig($extraSpaces = ''): string
    {
        return '[
            ' . $extraSpaces. '\'type\' => \'group\',
            ' . $extraSpaces. '\'internal_type\' => \'db\',
            ' . $extraSpaces. '\'allowed\' => \'pages\',
            ' . $extraSpaces. '\'size\' => 1,
            ' . $extraSpaces. '\'suggestOptions\' => [
                ' . $extraSpaces. '\'pages\' => [
                    ' . $extraSpaces. '\'searchCondition\' => \'doktype=99\',
                ' . $extraSpaces. '],
            ' . $extraSpaces. '],
        ' . $extraSpaces. '],';
    }

    /**
     * @param $table
     * @param $contentElementName
     * @param $secondDesignation
     * @param $extensionName
     * @param $fieldsToArray
     * @param $relativePath
     * @param string $extraSpaces
     * @return string
     */
    public function getSelectConfig($table, $contentElementName, $secondDesignation, $extensionName, $fieldsToArray, $relativePath, $extraSpaces = ''): string
    {
        return '[
            ' . $extraSpaces. '\'type\' => \'select\',
            ' . $extraSpaces. '\'renderType\' => \'selectSingle\',
            ' . $extraSpaces. '\'items\' => [
                ' . $extraSpaces. '[\'\', 0],
                ' . $extraSpaces . TCAUtility::addFieldsItemsToTCA($fieldsToArray, $table, $extensionName,$contentElementName, $secondDesignation, $relativePath, $extraSpaces) . '
            ' . $extraSpaces. '],
        ' . $extraSpaces. '],';
    }

    /**
     * @param $table
     * @param $contentElementName
     * @param $secondDesignation
     * @param $extensionName
     * @param $fieldsToArray
     * @param $relativePath
     * @param string $extraSpaces
     * @return string
     */
    public function getRadioConfig($table, $contentElementName, $secondDesignation, $extensionName, $fieldsToArray, $relativePath, $extraSpaces = ''): string
    {
        return '[
            ' . $extraSpaces. '\'type\' => \'radio\',
            ' . $extraSpaces. '\'items\' => [
                ' . $extraSpaces . TCAUtility::addFieldsItemsToTCA($fieldsToArray, $table, $extensionName,$contentElementName, $secondDesignation, $relativePath, $extraSpaces) . '
            ' . $extraSpaces. '],
        ' . $extraSpaces. '],';
    }

    /**
     * @param $secondDesignation
     * @param $fieldName
     * @param string $extraSpaces
     * @return string
     */
    public function getFalConfig($secondDesignation, $fieldName, $extraSpaces = ''): string
    {
        return '\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
            ' . $extraSpaces. '\''.strtolower($secondDesignation).'_'.$fieldName.'\',
            ' . $extraSpaces. '[
               ' . $extraSpaces. ' \'appearance\' => [
                    ' . $extraSpaces. '\'createNewRelationLinkTitle\' => \'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:images.addFileReference\',
                ' . $extraSpaces. '],
                ' . $extraSpaces. '\'overrideChildTca\' => [
                    ' . $extraSpaces. '\'types\' => [
                        ' . $extraSpaces. '\TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
                           ' . $extraSpaces. '\'showitem\' => \'
                            ' . $extraSpaces. '--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            ' . $extraSpaces. '--palette--;;filePalette\'
                        ' . $extraSpaces. '],
                    ' . $extraSpaces. '],
                ' . $extraSpaces. '],
            ' . $extraSpaces. '],
           ' . $extraSpaces. '$GLOBALS[\'TYPO3_CONF_VARS\'][\'GFX\'][\'imagefile_ext\']
        ' . $extraSpaces. '),';
    }
}
