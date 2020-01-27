<?php
namespace Digitalwerk\ContentElementRegistry\Command;

use Digitalwerk\ContentElementRegistry\Utility\GeneralCreateCommandUtility;
use Symfony\Component\Console\Exception\InvalidArgumentException;

/**
 * Class TCAFieldTypesAndImportedClasses
 * @package Digitalwerk\DwBoilerplate\Command
 */
class TCAFieldTypesAndImportedClasses
{
    /**
     * @param string $table
     * @param string $contentElementOrPageTypeName
     * @param string $secondDesignation
     * @param string $fieldName
     * @param array $fieldsToArray
     * @param string $relativePath
     * @param string $fieldTypeConfig
     * @param string $commandFieldType
     * @param string $fieldItem
     * @param string $relativePathToClass
     * @return array
     */
    public function getTCAFieldTypes($table = '', $contentElementOrPageTypeName = '', $secondDesignation = '', $fieldName = '', $fieldsToArray = [], $relativePath = '', $fieldTypeConfig = '', $commandFieldType = '', $fieldItem = '', $relativePathToClass = '', $inlineRelativePath = ''): array
    {
        $selectConfig = null;
        $checkConfig = null;
        $radioConfig = null;
        $inlineConfig = null;
        if ($fieldTypeConfig === 'select' && $commandFieldType === 'contentElementsAndInlineRelationFields') {
            $selectConfig = $this->getSelectConfig($table, $contentElementOrPageTypeName, $secondDesignation, 'dw_boilerplate', $fieldsToArray, $relativePath, 'contentElementsAndInlineRelationFields');
        }
        if ($fieldTypeConfig === 'inline' && $commandFieldType === 'contentElementsAndInlineRelationFields') {
            $inlineConfig = $this->getInlineConfig($relativePathToClass, $contentElementOrPageTypeName, $fieldItem, $contentElementOrPageTypeName);
        }
        if ($fieldTypeConfig === 'check' && $commandFieldType === 'contentElementsAndInlineRelationFields') {
            $checkConfig = $this->getCheckConfig($table, $contentElementOrPageTypeName, $secondDesignation, 'dw_boilerplate', $fieldsToArray, $relativePath, 'contentElementsAndInlineRelationFields');
        }
        if ($fieldTypeConfig === 'radio' && $commandFieldType === 'contentElementsAndInlineRelationFields') {
            $radioConfig = $this->getRadioConfig($table, $contentElementOrPageTypeName, $secondDesignation, 'dw_boilerplate', $fieldsToArray, $relativePath, 'contentElementsAndInlineRelationFields');
        }
        if ($fieldTypeConfig === 'select' && $commandFieldType === 'pageTypesFields') {
            $selectConfig = $this->getSelectConfig($table, $contentElementOrPageTypeName, $secondDesignation, 'dw_page_types', $fieldsToArray, $relativePath, 'pageTypesFields', '    ');
        }
        if ($fieldTypeConfig === 'check' && $commandFieldType === 'pageTypesFields') {
            $checkConfig = $this->getCheckConfig($table, $contentElementOrPageTypeName, $secondDesignation, 'dw_page_types', $fieldsToArray, $relativePath, 'pageTypesFields', '    ');
        }
        if ($fieldTypeConfig === 'radio' && $commandFieldType === 'pageTypesFields') {
            $radioConfig = $this->getRadioConfig($table, $contentElementOrPageTypeName, $secondDesignation, 'dw_page_types', $fieldsToArray, $relativePath, 'pageTypesFields', '    ');
        }

        return [
            'contentElementsAndInlineRelationFields' => [
                'title' => [
                    'isFieldDefault' => true,
                    'defaultFieldName' => 'title',
                    'defaultFieldTitle' => 'Title',
                    'tableFieldDataType' => null,
                    'config' => null,
                    'TCAItemsAllowed' => false,
                    'needImportClass' => true,
                    'importClassConditional' => [
                        'needDefaulFieldName' => true
                    ],
                    'importClass' => [
                        'titleTrait',
                    ],
                    'modelDataTypes' => [
                        'propertyDataType' => "''",
                        'propertyDataTypeDescribe' => 'string',
                        'getterDataTypeDescribe' => 'string',
                        'getterDataType' => 'string',
                    ],
                    'trait' => 'titleTrait'
                ],
                'bodytext' => [
                    'isFieldDefault' => true,
                    'defaultFieldName' => 'bodytext',
                    'specialFieldName' => 'text',
                    'defaultFieldTitle' => 'Bodytext',
                    'tableFieldDataType' => null,
                    'config' => null,
                    'TCAItemsAllowed' => false,
                    'needImportClass' => true,
                    'importClassConditional' => [
                        'needDefaulFieldName' => false,
                        'needSpecialFieldName' => false,
                    ],
                    'importClass' => [
                        'textTrait',
                    ],
                    'modelDataTypes' => [
                        'propertyDataType' => "''",
                        'propertyDataTypeDescribe' => 'string',
                        'getterDataTypeDescribe' => 'string',
                        'getterDataType' => 'string',
                    ],
                    'trait' => 'textTrait'
                ],
                'image' => [
                    'isFieldDefault' => true,
                    'defaultFieldName' => 'image',
                    'defaultFieldTitle' => 'Image',
                    'tableFieldDataType' => null,
                    'config' => null,
                    'TCAItemsAllowed' => false,
                    'needImportClass' => true,
                    'importClassConditional' => [
                        'needDefaulFieldName' => true
                    ],
                    'importClass' => [
                        'imageTrait',
                    ],
                    'modelDataTypes' => [
                        'propertyDataType' => "null",
                        'propertyDataTypeDescribe' => '\TYPO3\CMS\Extbase\Domain\Model\FileReference',
                        'getterDataTypeDescribe' => 'FileReference',
                        'getterDataType' => '? FileReference',
                    ],
                    'trait' => 'imageTrait'
                ],
                'media' => [
                    'isFieldDefault' => true,
                    'defaultFieldName' => 'media',
                    'defaultFieldTitle' => 'Media',
                    'tableFieldDataType' => null,
                    'config' => null,
                    'TCAItemsAllowed' => false,
                    'needImportClass' => true,
                    'importClassConditional' => [
                        'needDefaulFieldName' => true
                    ],
                    'importClass' => [
                        'mediaTrait',
                    ],
                    'modelDataTypes' => [
                        'propertyDataType' => "null",
                        'propertyDataTypeDescribe' => '\TYPO3\CMS\Extbase\Domain\Model\FileReference',
                        'getterDataTypeDescribe' => 'FileReference',
                        'getterDataType' => '? FileReference',
                    ],
                    'trait' => 'mediaTrait'
                ],
                'link' => [
                    'isFieldDefault' => true,
                    'defaultFieldName' => 'link',
                    'defaultFieldTitle' => 'Link',
                    'tableFieldDataType' => null,
                    'config' => null,
                    'TCAItemsAllowed' => false,
                    'needImportClass' => true,
                    'importClassConditional' => [
                        'needDefaulFieldName' => true
                    ],
                    'importClass' => [
                        'linkTrait',
                    ],
                    'modelDataTypes' => [
                        'propertyDataType' => "''",
                        'propertyDataTypeDescribe' => 'string',
                        'getterDataTypeDescribe' => 'string',
                        'getterDataType' => 'string',
                    ],
                    'trait' => 'linkTrait'
                ],
                'link_text' => [
                    'isFieldDefault' => true,
                    'defaultFieldName' => 'link_text',
                    'defaultFieldTitle' => 'Link-text',
                    'tableFieldDataType' => null,
                    'config' => null,
                    'TCAItemsAllowed' => false,
                    'needImportClass' => true,
                    'importClassConditional' => [
                        'needDefaulFieldName' => true
                    ],
                    'importClass' => [
                        'linkTrait',
                    ],
                    'modelDataTypes' => [
                        'propertyDataType' => "''",
                        'propertyDataTypeDescribe' => 'string',
                        'getterDataTypeDescribe' => 'string',
                        'getterDataType' => 'string',
                    ],
                    'trait' => 'linkTrait'
                ],
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
                'pi_flexform' => [
                    'isFieldDefault' => true,
                    'defaultFieldName' => 'pi_flexform',
                    'defaultFieldTitle' => '?',
                    'tableFieldDataType' => null,
                    'config' => null,
                    'TCAItemsAllowed' => false,
                    'FlexFormItemsAllowed' => true,
                    'needImportClass' => true,
                    'importClassConditional' => [
                        'needDefaulFieldName' => true
                    ],
                    'importClass' => [
                        'flexFormTrait',
                    ],
                    'modelDataTypes' => [
                        'propertyDataType' => "''",
                        'propertyDataTypeDescribe' => 'string',
                        'getterDataTypeDescribe' => 'array',
                        'getterDataType' => '? array',
                    ],
                    'trait' => 'flexFormTrait'
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
                'tx_contentelementregistry_relations' => [
                    'isFieldDefault' => true,
                    'defaultFieldName' => 'tx_contentelementregistry_relations',
                    'defaultFieldTitle' => '?',
                    'tableFieldDataType' => null,
                    'config' => null,
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
            ],
            'pageTypesFields' => [
                'input' => [
                    'isFieldDefault' => false,
                    'defaultFieldName' => null,
                    'tableFieldDataType' => 'varchar(255) DEFAULT \'\' NOT NULL',
                    'config' => $this->getInputConfig('    '),
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
                    'config' => $fieldName ? $this->getFalConfig($secondDesignation, $fieldName,  '    ') : null,
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
                    'config' => $this->getTextAreaConfig('    '),
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
                    'config' => $this->getGroupConfig('    '),
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
            ],
        ];
    }

    /**
     * @return array
     */
    public function getClasses(): array
    {
        return [
            'objectStorage' => 'use TYPO3\CMS\Extbase\Persistence\ObjectStorage;',
            'titleTrait' => 'use Digitalwerk\DwBoilerplate\Traits\ContentElement\TitleTrait;',
            'textTrait' => 'use Digitalwerk\DwBoilerplate\Traits\ContentElement\TextTrait;',
            'linkTrait' => 'use Digitalwerk\DwBoilerplate\Traits\ContentElement\LinkTrait;',
            'imageTrait' => 'use Digitalwerk\DwBoilerplate\Traits\ContentElement\ImageTrait;',
            'mediaTrait' => 'use Digitalwerk\DwBoilerplate\Traits\ContentElement\MediaTrait;',
            'flexFormTrait' => 'use Digitalwerk\DwBoilerplate\Traits\ContentElement\FlexFormTrait;',
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
     * @param $commandFieldType
     * @param string $extraSpaces
     * @return string
     */
    public function getCheckConfig($table, $contentElementName, $secondDesignation, $extensionName, $fieldsToArray, $relativePath, $commandFieldType, $extraSpaces = ''): string
    {
        return '[
             ' . $extraSpaces. '\'type\' => \'check\',
             ' . $extraSpaces. '\'items\' => [
                  ' . $extraSpaces . self::addFieldsItemsToTCA($fieldsToArray, $table, $extensionName,$contentElementName, $secondDesignation, $relativePath, $commandFieldType, $extraSpaces) . '
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
        GeneralCreateCommandUtility::addTitleToTranslation(
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
     * @param $commandFieldType
     * @param string $extraSpaces
     * @return string
     */
    public function getSelectConfig($table, $contentElementName, $secondDesignation, $extensionName, $fieldsToArray, $relativePath, $commandFieldType, $extraSpaces = ''): string
    {
        return '[
            ' . $extraSpaces. '\'type\' => \'select\',
            ' . $extraSpaces. '\'renderType\' => \'selectSingle\',
            ' . $extraSpaces. '\'items\' => [
                ' . $extraSpaces. '[\'\', 0],
                ' . $extraSpaces . self::addFieldsItemsToTCA($fieldsToArray, $table, $extensionName,$contentElementName, $secondDesignation, $relativePath, $commandFieldType, $extraSpaces) . '
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
     * @param $commandFieldType
     * @param string $extraSpaces
     * @return string
     */
    public function getRadioConfig($table, $contentElementName, $secondDesignation, $extensionName, $fieldsToArray, $relativePath, $commandFieldType, $extraSpaces = ''): string
    {
        return '[
            ' . $extraSpaces. '\'type\' => \'radio\',
            ' . $extraSpaces. '\'items\' => [
                ' . $extraSpaces . self::addFieldsItemsToTCA($fieldsToArray, $table, $extensionName,$contentElementName, $secondDesignation, $relativePath, $commandFieldType, $extraSpaces) . '
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

    /**
     * @param $fieldName
     * @param $secondDesignation
     * @param $table
     * @param $name
     * @param $fieldConfig
     * @param $extraSpaces
     * @param $extensionName
     * @return string
     */
    public function generateFieldInTCA($fieldName, $secondDesignation, $table, $name, $fieldConfig, $extraSpaces, $extensionName): string
    {
        return '\''.strtolower($secondDesignation).'_'.$fieldName.'\' => [
    ' . $extraSpaces . '\'label\' => \'LLL:EXT:' . $extensionName . '/Resources/Private/Language/locallang_db.xlf:' . $table . '.' . str_replace('_','',$extensionName) . '_'.strtolower($name).'.'. strtolower($secondDesignation).'_'.$fieldName.'\',
    ' . $extraSpaces . '\'config\' => '.$fieldConfig.'
' . $extraSpaces . '],';
    }

    /**
     * @param $field
     * @param $table
     * @param $extensionName
     * @param $name
     * @param $secondDesignation
     * @param $relativePath
     * @param $commandFieldType
     * @param $extraSpaces
     * @return bool
     */
    public static function addFieldsItemsToTCA($field, $table, $extensionName,$name, $secondDesignation, $relativePath, $commandFieldType, $extraSpaces)
    {
        if (!empty($field) && !empty($table)) {
            $result = [];
            $fieldName = explode(',', $field)[0];
            $fieldType = explode(',', $field)[1];
            $fieldItems = explode('*', explode(',', $field)[3]);
            array_pop($fieldItems);

            if (!empty($fieldItems[0]) && (new TCAFieldTypesAndImportedClasses)->getTCAFieldTypes()[$commandFieldType][$fieldType]['FlexFormItemsAllowed'] !== true) {
                if ((new TCAFieldTypesAndImportedClasses)->getTCAFieldTypes()[$commandFieldType][$fieldType]['TCAItemsAllowed'] === true) {
                    foreach ($fieldItems as $fieldItem) {
                        $itemName = explode(';' ,$fieldItem)[0];

                        $result[] = '[\'LLL:EXT:' . $extensionName . '/Resources/Private/Language/locallang_db.xlf:' . $table . '.' . str_replace('_', '', $extensionName) . '_'.strtolower($name).'.'. strtolower($secondDesignation).'_'.$fieldName.'.' . strtolower($itemName) . '\', ' . $relativePath . '::' . strtoupper($fieldName) . '_' .strtoupper($itemName) . '],';
                    }
                } else {
                    throw new InvalidArgumentException('You can not add items to ' . $fieldType . ', because items is not allowed.');
                }
            }

            return implode("\n                " . $extraSpaces , $result);
        }
    }
}
