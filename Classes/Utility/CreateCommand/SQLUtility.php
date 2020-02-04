<?php
namespace Digitalwerk\ContentElementRegistry\Utility\CreateCommand;

use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Config\TCAFieldTypes;
use Digitalwerk\ContentElementRegistry\Utility\GeneralCreateCommandUtility;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class SQLUtility
 * @package Digitalwerk\ContentElementRegistry\Utility\CreateCommand
 */
class SQLUtility
{
    /**
     * @param $fields
     * @param $name
     * @param $table
     * @return string
     * Return CE sql table fields (format string)
     */
    public static function addFieldsToSQLTable($fields, $name, $table)
    {
        $generalCreateCommandUtility = GeneralUtility::makeInstance(GeneralCreateCommandUtility::class);
        $fieldsToArray = $generalCreateCommandUtility->fieldsToArray($fields);
        $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypes::class)->getTCAFieldTypes($table);
        $result = [];


        foreach ($fieldsToArray as $field) {
            $fieldName = $generalCreateCommandUtility->getFieldName($field);
            $fieldType = $generalCreateCommandUtility->getFieldType($field);

            if ($TCAFieldTypes[$table][$fieldType]) {
                if (null !== $TCAFieldTypes[$table][$fieldType]['tableFieldDataType']) {
                    $result[] = strtolower($name) . '_' . $fieldName.' ' . $TCAFieldTypes[$table][$fieldType]['tableFieldDataType'];
                }
            } else {
//                Field does not exist
                throw new InvalidArgumentException('Field "' . $fieldType . '" does not exist.3');
            }
        }

        return implode(",\n    ", $result);
    }
}
