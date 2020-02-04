<?php
namespace Digitalwerk\ContentElementRegistry\Utility\CreateCommand;

use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Config\ImportedClasses;
use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Config\TCAFieldTypes;
use Digitalwerk\ContentElementRegistry\Utility\GeneralCreateCommandUtility;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ModelUtility
 * @package Digitalwerk\ContentElementRegistry\Utility\CreateCommand
 */
class ModelUtility
{
    /**
     * @param $fields
     * @param $table
     * @param string $betweenProtectedsAndGetters
     * @param string $inlineRelativePath
     * @return string
     * Return content element model's protected and getters (string format)
     */
    public static function addFieldsToModel($fields, $table, $betweenProtectedsAndGetters = '', $inlineRelativePath = '')
    {
        if (!empty($fields)) {
            $generalCreateCommandUtility = GeneralUtility::makeInstance(GeneralCreateCommandUtility::class);
            $fieldsToArray = $generalCreateCommandUtility->fieldsToArray($fields);
            $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypes::class)->getTCAFieldTypes($table);
            $resultOfTraits = [];
            $resultOfProtected = [];
            $resultOfGetters = [];

            foreach ($fieldsToArray as $field) {
                $fieldName = $generalCreateCommandUtility->getFieldName($field);
                $fieldType = $generalCreateCommandUtility->getFieldType($field);

                if ($TCAFieldTypes[$table][$fieldType]['defaultFieldName'] === $fieldName && !empty($TCAFieldTypes[$table][$fieldType]['trait']))
                {
                    if (in_array('use ' . ucfirst($TCAFieldTypes[$table][$fieldType]['trait']) . ';', $resultOfTraits) === false) {
                        $resultOfTraits[] = 'use ' . ucfirst($TCAFieldTypes[$table][$fieldType]['trait']) . ';';
                    }
                } elseif ($TCAFieldTypes[$table][$fieldType]['specialFieldName'] === $fieldName && !empty($TCAFieldTypes[$table][$fieldType]['trait'])) {
                    if (in_array('use ' . ucfirst($TCAFieldTypes[$table][$fieldType]['trait']) . ';', $resultOfTraits) === false) {
                        $resultOfTraits[] = 'use ' . ucfirst($TCAFieldTypes[$table][$fieldType]['trait']) . ';';
                    }
                } else {
                    if ($TCAFieldTypes[$table][$fieldType]['inlineFieldsAllowed']) {
                        $fieldItem = $generalCreateCommandUtility->getFirstFieldItem($field);
                        $typeValue =
                            [
                                $TCAFieldTypes[$table][$fieldType]['modelDataTypes']['propertyDataType'],
                                GeneralUtility::makeInstance(TCAFieldTypes::class)->getTCAFieldTypes($table,'', '', '','','','',$fieldItem,'',$inlineRelativePath)[$table][$fieldType]['modelDataTypes']['propertyDataTypeDescribe'],
                                $TCAFieldTypes[$table][$fieldType]['modelDataTypes']['getterDataTypeDescribe'],
                                $TCAFieldTypes[$table][$fieldType]['modelDataTypes']['getterDataType'],
                            ];
                    } else {
                        $typeValue =
                            [
                                $TCAFieldTypes[$table][$fieldType]['modelDataTypes']['propertyDataType'],
                                $TCAFieldTypes[$table][$fieldType]['modelDataTypes']['propertyDataTypeDescribe'],
                                $TCAFieldTypes[$table][$fieldType]['modelDataTypes']['getterDataTypeDescribe'],
                                $TCAFieldTypes[$table][$fieldType]['modelDataTypes']['getterDataType'],
                            ];
                    }


                    $resultOfProtected[] =
                        '/**
     * @var '.$typeValue[1].'
     */
    protected $'.str_replace(' ','',lcfirst(ucwords(str_replace('_',' ',$fieldName)))).' = '.$typeValue[0].';';

                    $resultOfGetters[] =
                        '/**
     * @return '.$typeValue[2].'
     */
    public function get'.str_replace(' ','',ucwords(str_replace('_',' ',$fieldName))).'(): '.$typeValue[3].'
    {
        return $this->'.str_replace(' ','',lcfirst(ucwords(str_replace('_',' ',$fieldName)))).';
    }';
                }
            }


            $resultOfTraits = implode('
    ', $resultOfTraits);

            $resultOfProtected = implode('
    
    ', $resultOfProtected);

            $resultOfGetters = implode('
    
    ', $resultOfGetters);

            $resultOfTraits = $resultOfTraits ?  $resultOfTraits . '
    
    ' : '';

            $resultOfProtected = $resultOfProtected ?  $resultOfProtected . '
    
    ' : '';

            $betweenProtectedsAndGetters = $betweenProtectedsAndGetters ?  $betweenProtectedsAndGetters . '
    
    ' : '';

            $resultOfGetters = $resultOfGetters ?  $resultOfGetters . '
    
    ' : '';

            return rtrim($resultOfTraits . $resultOfProtected . $betweenProtectedsAndGetters . $resultOfGetters);
        } else {
            return null;
        }
    }

    /**
     * @param $fields
     * @param $table
     * @return string
     */
    public static function addConstantsToModel($fields, $table)
    {
        if (!empty($fields)) {
            $generalCreateCommandUtility = GeneralUtility::makeInstance(GeneralCreateCommandUtility::class);
            $fieldsToArray = $generalCreateCommandUtility->fieldsToArray($fields);
            $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypes::class)->getTCAFieldTypes($table);
            $result = [];

            foreach ($fieldsToArray as $field) {
                $fieldName = $generalCreateCommandUtility->getFieldName($field);
                $fieldType = $generalCreateCommandUtility->getFieldType($field);
                $fieldItems = $generalCreateCommandUtility->getFieldItems($field);

                if ($TCAFieldTypes[$table][$fieldType]['TCAItemsAllowed'] === true) {
                    foreach ($fieldItems as $fieldItem) {
                        $itemName = $generalCreateCommandUtility->getItemName($fieldItem);
                        $itemValue = $generalCreateCommandUtility->getItemValue($fieldItem);
                        $result[] =  'const ' . strtoupper($fieldName) . '_' .strtoupper($itemName) . ' = ' . '"' . $itemValue . '";';
                    }
                } elseif (!empty($fieldItems) && $TCAFieldTypes[$table][$fieldType]['FlexFormItemsAllowed'] !== true && $TCAFieldTypes[$table][$fieldType]['inlineFieldsAllowed'] !== true) {
                    throw new InvalidArgumentException('You can not add items to ' . $fieldType . ', because items is not allowed.');
                }
            }
            return implode("\n    ", $result);
        }
    }

    /**
     * @param $fields
     * @param $table
     * @param null $optionalClass
     * @return string
     * Return string of class which needs to be imported to model
     */
    public static function importClassToModel($fields, $table, $optionalClass = null)
    {
        if (!empty($fields) || $fields !== '-') {
            $generalCreateCommandUtility = GeneralUtility::makeInstance(GeneralCreateCommandUtility::class);
            $fieldsToArray = $generalCreateCommandUtility->fieldsToArray($fields);
            $TCAFieldTypesAndImportedClasses = GeneralUtility::makeInstance(TCAFieldTypes::class)->getTCAFieldTypes($table);
            $result = [];
            $importClass = GeneralUtility::makeInstance(ImportedClasses::class)->getClasses();

            foreach ($fieldsToArray as $field) {
                $fieldName = $generalCreateCommandUtility->getFieldName($field);
                $fieldType = $generalCreateCommandUtility->getFieldType($field);

                if ($optionalClass !== null && in_array($importClass[$optionalClass], $result) === false) {
                    $result[] = $importClass[$optionalClass];
                }
                if ($TCAFieldTypesAndImportedClasses[$table][$fieldType]['needImportClass']) {
                    if ($TCAFieldTypesAndImportedClasses[$table][$fieldType]['importClassConditional']['needDefaulFieldName']) {
                        if ($TCAFieldTypesAndImportedClasses[$table][$fieldType]['defaultFieldName'] === $fieldName) {
                            foreach ($TCAFieldTypesAndImportedClasses[$table][$fieldType]['importClass'] as $importClassFromField) {
                                if (in_array($importClass[$importClassFromField], $result) === false){
                                    $result[] = $importClass[$importClassFromField];
                                }
                            }
                        }
                    } elseif ($TCAFieldTypesAndImportedClasses[$table][$fieldType]['importClassConditional']['needSpecialFieldName']) {
                        if ($TCAFieldTypesAndImportedClasses[$table][$fieldType]['specialFieldName'] === $fieldName) {
                            foreach ($TCAFieldTypesAndImportedClasses[$table][$fieldType]['importClass'] as $importClassFromField) {
                                if (in_array($importClass[$importClassFromField], $result) === false){
                                    $result[] = $importClass[$importClassFromField];
                                }
                            }
                        }
                    } else {
                        foreach ($TCAFieldTypesAndImportedClasses[$table][$fieldType]['importClass'] as $importClassFromField) {
                            if (in_array($importClass[$importClassFromField], $result) === false){
                                $result[] = $importClass[$importClassFromField];
                            }
                        }
                    }
                }
            }

            return implode("\n", $result);
        } else {
            return null;
        }
    }
}
