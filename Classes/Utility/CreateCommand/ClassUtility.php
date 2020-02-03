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
     * @return string
     * Return full field's name => protected name (format string)
     */
    public static function addFieldsToClassMapping($fields, $name, $table)
    {
        if (!empty($fields)) {
            $fieldsToArray = GeneralUtility::makeInstance(GeneralCreateCommandUtility::class)->fieldsToArray($fields);
            $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypes::class)->getTCAFieldTypes($table);
            $createdFields = [];

            foreach ($fieldsToArray as $field) {
                $fieldName = explode(',',$field)[0];
                $fieldType = explode(',', $field)[1];

                if ($fieldName === $fieldType && $TCAFieldTypes[$table][$fieldType]['isFieldDefault']) {
//                    Default fields
                } elseif ($fieldName !== $fieldType && $TCAFieldTypes[$table][$fieldType]['isFieldDefault']) {
                    $createdFields[] = '"' . $fieldType . '" => "' . str_replace(' ','',lcfirst(ucwords(str_replace('_',' ',$fieldName)))) . '"';
                } elseif ($TCAFieldTypes[$table][$fieldType]) {
                    $createdFields[] = '"'. strtolower($name).'_'.$fieldName.'" => "'.str_replace(' ','',lcfirst(ucwords(str_replace('_',' ',$fieldName)))) . '"';
                } else {
//                    ErrorMessage (FieldType does not exist)
                    throw new InvalidArgumentException('Field "' . $fieldType . '" does not exist.6');
                }
            }

            return implode(',
        ', $createdFields);
        } else {
            return null;
        }
    }
}
