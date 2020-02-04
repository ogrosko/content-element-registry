<?php
namespace Digitalwerk\ContentElementRegistry\Utility;

use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Config\TCAFieldTypes;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class GeneralCreateCommandUtility
 * @package Digitalwerk\ContentElementRegistry\Utility
 */
class GeneralCreateCommandUtility
{
    /**
     * @param $fields
     * @return array
     * Return converted fields from string to array
     */
    public function fieldsToArray($fields)
    {
        $fieldsToArray = explode('/',$fields);
        array_pop($fieldsToArray);

        if (count($fieldsToArray) === 0 && $fields !== '-') {
            throw new InvalidArgumentException('Field syntax error.');
        }

        foreach ($fieldsToArray as $field) {
            if (count(explode(',', $field)) !== 3) {
                if (count(explode(',', $field)) === 4 && count(self::getFirstFieldItem($field))  !== 3) {
                    throw new InvalidArgumentException('Field syntax error.');
                }
                if (count(explode(',', $field)) > 4) {
                    throw new InvalidArgumentException('Field syntax error.');
                }
            }
        }

        return $fieldsToArray;
    }

    /**
     * @param array $array
     * @param string $key
     * @param array $new
     *
     * @return array
     */
    public static function arrayInsertAfter( array $array, $key, array $new ) {
        $keys = array_keys( $array );
        $index = array_search( $key, $keys );
        $pos = false === $index ? count( $array ) : $index + 1;
        return array_merge( array_slice( $array, 0, $pos ), $new, array_slice( $array, $pos ) );
    }

    /**
     * @param string $filename
     * @param array $newLine
     * @param array $afterLines
     */
    public static function importStringInToFileAfterString(string $filename, array $newLine, array $afterLines)
    {
        $lines = file($filename);
        $index = 0;
        $editedAfterLines = [];

        if (count($afterLines) === count(array_intersect($afterLines, array_map('trim', $lines)))) {
            foreach ($lines as $line) {
                if (trim($line) === $afterLines[0]) {
                    break;
                }
                $index++;
            }

            for ($oldKey = 0; $oldKey <= count($afterLines)-1; $oldKey++) {
                $editedAfterLines[$index] = $afterLines[$oldKey];
                $index++;
            }

            if (count($editedAfterLines) === count(array_intersect_assoc($editedAfterLines, array_map('trim', $lines)))) {
                $lines = self::arrayInsertAfter($lines, array_search(end($editedAfterLines), array_map('trim', $lines)), $newLine);
                file_put_contents($filename, $lines);
            }
        }
    }

    /**
     * @param $fields
     * @param $name
     * @param $table
     * @param $extraSpace
     * @return string
     * Return field's name with --linebreak-- (format string)
     */
    public static function addFieldsToPalette($fields, $name, $table, $extraSpace)
    {
        if (!empty($fields)) {
            $generalCreateCommandUtility = GeneralUtility::makeInstance(GeneralCreateCommandUtility::class);
            $fieldsToArray = $generalCreateCommandUtility->fieldsToArray($fields);
            $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypes::class);
            $createdFields = [];


            foreach ($fieldsToArray as $field) {
                $fieldName = $generalCreateCommandUtility->getFieldName($field);
                $fieldType = $generalCreateCommandUtility->getFieldType($field);

                if ($TCAFieldTypes->getTCAFieldTypes($table)[$table][$fieldType]['isFieldDefault']) {
                    $createdFields[] = '--linebreak--, ' . $fieldType;
                } elseif ($TCAFieldTypes->getTCAFieldTypes($table)[$table][$fieldType]['isFieldDefault'] === false) {
                    $createdFields[] = '--linebreak--, ' . strtolower($name).'_'.$fieldName;
                } else {
//                    Fieldtype does not exist
                    throw new InvalidArgumentException('Field "' . $fieldType . '" does not exist.1');
                }
            }
            return preg_replace('/--linebreak--, /', '', implode(",\n" . $extraSpace, $createdFields),1);
        } else {
            return '';
        }
    }

    /**
     * @param $fields
     * @param $table
     * @return bool
     */
    public static function areAllFieldsDefault($fields, $table)
    {
        if (!empty($fields)) {
            $fieldsToArray = GeneralUtility::makeInstance(GeneralCreateCommandUtility::class)->fieldsToArray($fields);
            $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypes::class);

            foreach ($fieldsToArray as $field) {
                $fieldType = explode(',', $field)[1];

                if ($TCAFieldTypes->getTCAFieldTypes($table)[$table][$fieldType]['isFieldDefault'] === true) {
                } elseif ($TCAFieldTypes->getTCAFieldTypes($table)[$table][$fieldType]['isFieldDefault'] === false) {

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
     * @param $field
     * @return string
     */
    public function getFieldName($field)
    {
        return explode(',', $field)[0];
    }

    /**
     * @param $field
     * @return string
     */
    public function getFieldType($field)
    {
        return explode(',', $field)[1];
    }

    /**
     * @param $field
     * @return string
     */
    public function getFieldTitle($field)
    {
        return explode(',', $field)[2];
    }

    /**
     * @param $field
     * @return string
     */
    public function getFieldItems($field)
    {
        $fieldItems = explode('*', explode(',', $field)[3]);
        array_pop($fieldItems);
        return $fieldItems;
    }

    /**
     * @param $field
     * @return array
     */
    public function getFirstFieldItem($field)
    {
        return explode(';', explode('*', explode(',', $field)[3])[0]);
    }

    /**
     * @param $field
     * @return bool
     */
    public function hasItems($field)
    {
        return !empty(self::getFieldItems($field));
    }

    /**
     * @param $item
     * @return string
     */
    public function getItemName($item)
    {
        return explode(';', $item)[0];
    }

    /**
     * @param $item
     * @return string
     */
    public function getItemType($item)
    {
        return explode(';', $item)[1];
    }

    /**
     * @param $item
     * @return string
     */
    public function getItemValue($item)
    {
        return explode(';', $item)[1];
    }

    /**
     * @param $item
     * @return string
     */
    public function getItemTitle($item)
    {
        return explode(';', $item)[2];
    }
}
