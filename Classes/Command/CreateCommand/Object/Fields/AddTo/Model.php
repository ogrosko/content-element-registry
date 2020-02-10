<?php
namespace Digitalwerk\ContentElementRegistry\Command\CreateCommand\Object\Fields\AddTo;

use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Config\ImportedClasses;
use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Config\TCAFieldTypes;
use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Object\Fields;
use Digitalwerk\ContentElementRegistry\Utility\FieldsUtility;
use InvalidArgumentException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Model
 * @package Digitalwerk\ContentElementRegistry\Command\CreateCommand\Object\Fields\AddTo
 */
class Model
{
    /**
     * @param Fields $fields
     * @param null $optionalClass
     * @return string
     */
    public function toUseClass(Fields $fields, $optionalClass = null)
    {
        $result = [];
        $importClass = GeneralUtility::makeInstance(ImportedClasses::class)->getClasses();

        foreach ($fields->getFields() as $field) {
            $fieldName = $field->getName();

            if ($optionalClass !== null && in_array($importClass[$optionalClass], $result) === false) {
                $result[] = $importClass[$optionalClass];
            }
            if ($field->needImportClass()) {
                if ($field->needImportedClassDefaultName()) {
                    if ($field->getDefaultName() === $fieldName) {
                        foreach ($field->getImportClasses() as $importClassFromField) {
                            if (in_array($importClass[$importClassFromField], $result) === false){
                                $result[] = $importClass[$importClassFromField];
                            }
                        }
                    }
                } else {
                    foreach ($field->getImportClasses() as $importClassFromField) {
                        if (in_array($importClass[$importClassFromField], $result) === false){
                            $result[] = $importClass[$importClassFromField];
                        }
                    }
                }
            }
        }

        return implode("\n", $result);
    }

    /**
     * @param $fields
     * @param $table
     * @return string
     */
    public function constants(Fields $fields, $table)
    {
        $result = [];

        foreach ($fields->getFields() as $field) {
            $fieldName = $field->getName();
            $fieldType = $field->getType();
            $fieldItems = $field->getItems();
            if ($field->isTCAItemsAllowed()) {
                foreach ($fieldItems as $item) {
                    $itemName = $item->getName();
                    $itemValue = $item->getValue();
                    $result[] =  'const ' . strtoupper($fieldName) . '_' .strtoupper($itemName) . ' = ' . '"' . $itemValue . '";';
                }
            } elseif (!empty($fieldItems) && !$field->isFlexFormItemsAllowed() && !$field->isInlineItemsAllowed()) {
                throw new InvalidArgumentException('You can not add items to ' . $fieldType . ', because items is not allowed.');
            }
        }
        return implode("\n    ", $result);
    }

    /**
     * @param $fields
     * @param $table
     * @param string $betweenProtectedsAndGetters
     * @param string $inlineRelativePath
     * @return string
     * Return content element model's protected and getters (string format)
     */
    public function fields(Fields $fields, $table, $betweenProtectedsAndGetters = '', $inlineRelativePath = '')
    {
        if (!empty($fields)) {
            $resultOfTraits = [];
            $resultOfProtected = [];
            $resultOfGetters = [];

            foreach ($fields->getFields() as $field) {
                $fieldName = $field->getName();
                $fieldType = $field->getType();

                if ($field->getDefaultName() === $fieldName && !empty($field->getTrait()))
                {
                    if (in_array('use ' . ucfirst($field->getTrait()) . ';', $resultOfTraits) === false) {
                        $resultOfTraits[] = 'use ' . ucfirst($field->getTrait()) . ';';
                    }
                } else {
                    if ($field->isInlineItemsAllowed()) {
                        $simulateRawFieldItems = ',,,' . $field->getFirstItem()->getName() . ';' . $field->getFirstItem()->getValue() . ';' . $field->getFirstItem()->getTitle() .'*/';

                        $modelDataTypes = $field->getModelDataTypes();
                        $modelDataTypes->setPropertyDataTypeDescribe(
                            GeneralUtility::makeInstance(FieldsUtility::class)->getFieldModelDataTypePropertyDescribe($table, $fieldType, $simulateRawFieldItems, $inlineRelativePath)
                        );
                        $field->setModelDataTypes($modelDataTypes);
                    }

                    $resultOfProtected[] =
                        '/**
     * @var ' . $field->getModelDataTypes()->getPropertyDataTypeDescribe() . '
     */
    protected $'.str_replace(' ','',lcfirst(ucwords(str_replace('_',' ',$fieldName)))).' = ' . $field->getModelDataTypes()->getPropertyDataType() . ';';

                    $resultOfGetters[] =
                        '/**
     * @return ' . $field->getModelDataTypes()->getGetterDataTypeDescribe() . '
     */
    public function get'.str_replace(' ','',ucwords(str_replace('_',' ',$fieldName))).'(): ' . $field->getModelDataTypes()->getGetterDataType() . '
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
}
