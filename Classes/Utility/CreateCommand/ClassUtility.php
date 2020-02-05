<?php
namespace Digitalwerk\ContentElementRegistry\Utility\CreateCommand;

use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Config\TCAFieldTypes;
use Digitalwerk\ContentElementRegistry\Utility\GeneralCreateCommandUtility;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ClassUtility
 * @package Digitalwerk\ContentElementRegistry\Utility
 */
class ClassUtility
{
    /**
     * @param $fields
     * @param $name
     * @param $table
     * @param $extraSpaces
     * @return string
     * Return full field's name => protected name (format string)
     */
    public static function addFieldsToClassMapping($fields, $name, $table, $extraSpaces)
    {
        if (!empty($fields)) {
            $generalCreateCommandUtility = GeneralUtility::makeInstance(GeneralCreateCommandUtility::class);
            $fieldsToArray = $generalCreateCommandUtility->fieldsToArray($fields);
            $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypes::class)->getTCAFieldTypes($table);
            $createdFields = [];

            foreach ($fieldsToArray as $field) {
                $fieldName = $generalCreateCommandUtility->getFieldName($field);
                $fieldType = $generalCreateCommandUtility->getFieldType($field);

                if ($fieldName === $fieldType && $generalCreateCommandUtility->isFieldTypeDefault($TCAFieldTypes, $table, $fieldType)) {
                    //Default fields (no action)
                } elseif ($fieldName !== $fieldType && $generalCreateCommandUtility->isFieldTypeDefault($TCAFieldTypes, $table, $fieldType)) {
                    $createdFields[] = '"' . $fieldType . '" => "' . str_replace(' ','',lcfirst(ucwords(str_replace('_',' ',$fieldName)))) . '"';
                } elseif ($TCAFieldTypes[$table][$fieldType]) {
                    $createdFields[] = '"'. strtolower($name).'_'.$fieldName.'" => "'.str_replace(' ','',lcfirst(ucwords(str_replace('_',' ',$fieldName)))) . '"';
                } else {
                    throw new InvalidArgumentException('Field "' . $fieldType . '" does not exist.6');
                }
            }

            return implode(",\n" . $extraSpaces, $createdFields);
        } else {
            return null;
        }
    }
}
