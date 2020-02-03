<?php
namespace Digitalwerk\ContentElementRegistry\Utility\CreateCommand;

use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Config\TCAFieldTypes;
use Digitalwerk\ContentElementRegistry\Utility\GeneralCreateCommandUtility;
use InvalidArgumentException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class TyposcriptUtility
 * @package Digitalwerk\ContentElementRegistry\Utility\CreateCommand
 */
class TypoScriptUtility
{
    /**
     * @param $fields
     * @param $name
     * @param $table
     * @return string
     * Return full field's name => protected name (format string)
     */
    public static function addFieldsToTypoScriptMapping($fields, $name, $table)
    {
        if (!empty($fields)) {
            $fieldsToArray = (new GeneralCreateCommandUtility)->fieldsToArray($fields);
            $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypes::class)->getTCAFieldTypes($table);
            $createdFields = [];

            foreach ($fieldsToArray as $field) {
                $fieldName = explode(',',$field)[0];
                $fieldType = explode(',', $field)[1];

                if ($fieldName === $fieldType && $TCAFieldTypes[$table][$fieldType]['isFieldDefault']) {
//                   Nothing to add (default fields)
                } elseif ($fieldName !== $fieldType && $TCAFieldTypes[$table][$fieldType]['isFieldDefault']) {
                    $createdFields[] = $fieldType.'.mapOnProperty = '.str_replace(' ','',lcfirst(ucwords(str_replace('_',' ',$fieldName))));
                } elseif ($TCAFieldTypes[$table][$fieldType]) {
                    $createdFields[] = strtolower($name).'_'.$fieldName.'.mapOnProperty = '.str_replace(' ','',lcfirst(ucwords(str_replace('_',' ',$fieldName))));
                } else {
//                    ErrorMessage (FieldType does not exist)
                    throw new InvalidArgumentException('Field "' . $fieldType . '" does not exist.2');
                }
            }

            return  implode('
            ', $createdFields);
        } else {
            return null;
        }
    }

    /**
     * @param $name
     * @param $fields
     * @param $table
     * @param $recordType
     * @param $pathToModel
     * @return string
     * Return TypoScript Mapping (format string)
     */
    public static function getTyposcriptMapping($name, $fields, $table, $recordType, $pathToModel)
    {
        $mappingFields = self::addFieldsToTyposcriptMapping($fields, $name, $table);
        $columnsMapOnProperty = '';
        if (!empty($mappingFields)) {
            $columnsMapOnProperty = '
          columns {
            ' . $mappingFields . '
          }';
        }

        $template =
            '      ' . $pathToModel . ' {
        mapping {
          tableName = ' . $table . '
          recordType = ' . $recordType . '' . $columnsMapOnProperty . '
        }
      }';


        return $template;
    }
}
