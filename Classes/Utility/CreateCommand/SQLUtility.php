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
     * @var array
     */
    protected $dataTypes = [
        'int' => 'int(11) DEFAULT 0 NOT NULL',
        'varchar255' => 'varchar(255) DEFAULT \'\' NOT NULL',
        'text' => 'text',
    ];

    /**
     * @return array
     */
    public function getDataTypes(): array
    {
        return $this->dataTypes;
    }

    /**
     * @param array $dataTypes
     */
    public function setDataTypes(array $dataTypes): void
    {
        $this->dataTypes = $dataTypes;
    }

    /**
     * @param $fields
     * @param $name
     * @param $table
     * @return string
     * Return CE sql table fields (format string)
     */
    public function addFieldsToSQLTable($fields, $name, $table)
    {
        $generalCreateCommandUtility = GeneralUtility::makeInstance(GeneralCreateCommandUtility::class);
        $fieldsToArray = $generalCreateCommandUtility->fieldsToArray($fields);
        $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypes::class)->getTCAFieldTypes($table);
        $result = [];


        foreach ($fieldsToArray as $field) {
            $fieldName = $generalCreateCommandUtility->getFieldName($field);
            $fieldType = $generalCreateCommandUtility->getFieldType($field);
            $fieldItems = $generalCreateCommandUtility->getFieldItems($field);

            if ($TCAFieldTypes[$table][$fieldType]) {
                if ($this->hasFieldSQLDataType($TCAFieldTypes, $table, $fieldType)) {
                    //Explain Default sql data types for select, radio and check is int. If value of items is not numeric, so change field sql data type to varchar255
                    if (!self::isAllItemsNumeric($fieldItems)) {
                        $result[] = strtolower($name) . '_' . $fieldName.' ' . $this->getVarchar255DataType();
                    } else {
                        $result[] = strtolower($name) . '_' . $fieldName.' ' . $this->getFieldSQLDataType($TCAFieldTypes, $table, $fieldType);
                    }
                }
            } else {
//                Field does not exist
                throw new InvalidArgumentException('Field "' . $fieldType . '" does not exist.3');
            }
        }

        return implode(",\n    ", $result);
    }

    /**
     * @param $TCAFieldTypes
     * @param $table
     * @param $fieldType
     * @return bool
     */
    protected function hasFieldSQLDataType($TCAFieldTypes, $table, $fieldType)
    {
        return !empty($TCAFieldTypes[$table][$fieldType]['tableFieldDataType']);
    }

    /**
     * @param $TCAFieldTypes
     * @param $table
     * @param $fieldType
     * @return bool
     */
    protected function getFieldSQLDataType($TCAFieldTypes, $table, $fieldType)
    {
        return $TCAFieldTypes[$table][$fieldType]['tableFieldDataType'];
    }

    /**
     * @return mixed
     */
    public function getIntDataType()
    {
        return $this->getDataTypes()['int'];
    }

    /**
     * @return mixed
     */
    public function getVarchar255DataType()
    {
        return $this->getDataTypes()['varchar255'];
    }

    /**
     * @return mixed
     */
    public function getTextDataType()
    {
        return $this->getDataTypes()['text'];
    }

    /**
     * @param $fieldItems
     * @return mixed
     */
    public function isAllItemsNumeric($fieldItems)
    {
        $generalCreateCommandUtility = GeneralUtility::makeInstance(GeneralCreateCommandUtility::class);

        foreach ($fieldItems as $fieldItem) {
            if (!is_numeric($generalCreateCommandUtility->getItemValue($fieldItem))) {
                return false;
                break;
            }
        }

        return true;
    }
}
