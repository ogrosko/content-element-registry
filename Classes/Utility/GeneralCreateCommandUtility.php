<?php
namespace Digitalwerk\ContentElementRegistry\Utility;

use Symfony\Component\Console\Exception\InvalidArgumentException;

/**
 * Class GeneralCreateCommandUtility
 * @package Digitalwerk\ContentElementRegistry\Utility
 */
class GeneralCreateCommandUtility
{
    /**
     * @param $autoHeader
     * @param $pageTypeName
     */
    public static function checkDefaultTemplateOptionalAndAddAutoHeader($autoHeader, $pageTypeName)
    {
        if ($autoHeader !== 'true' && $autoHeader !== 'false') {
            throw new InvalidArgumentException('Syntax error in field "auto-header" : ' . $autoHeader . ' (must be false or true)');
        }
        $pageTypeTemplate = 'public/typo3conf/ext/dw_boilerplate/Resources/Private/Partials/PageType/' . $pageTypeName . '/Header.html';
        $pageTypeTemplateContent = '<html xmlns="http://www.w3.org/1999/xhtml" lang="en"
      xmlns:f="http://typo3.org/ns/TYPO3/Fluid/ViewHelpers"
      xmlns:v="http://typo3.org/ns/FluidTYPO3/Vhs/ViewHelpers"
      data-namespace-typo3-fluid="true">

<f:alias map="{' . strtolower($pageTypeName) . ':dwPageType}">

</f:alias>

</html>';

        if ($autoHeader === 'true') {
            //        Check default template if it is set to optional
            $defaultTemplate = 'public/typo3conf/ext/dw_boilerplate/Resources/Private/Templates/Page/Default.html';
            $defaultTemplateLines = file($defaultTemplate);
            if (!(in_array('<f:render partial="PageType/{dwPageType.modelName}/Header" optional="1" arguments="{dwPageType:dwPageType}" />', array_map('trim', $defaultTemplateLines))))
            {
                GeneralCreateCommandUtility::importStringInToFileAfterString(
                    $defaultTemplate,
                    ["    <f:render partial=\"PageType/{dwPageType.modelName}/Header\" optional=\"1\" arguments=\"{dwPageType:dwPageType}\" /> \n"],
                    ['<!--TYPO3SEARCH_begin-->']
                );
            }

            if (!file_exists('public/typo3conf/ext/dw_boilerplate/Resources/Private/Partials/PageType')) {
                mkdir('public/typo3conf/ext/dw_boilerplate/Resources/Private/Partials/PageType', 0777, true);
            }
            if (!file_exists('public/typo3conf/ext/dw_boilerplate/Resources/Private/Partials/PageType/' . $pageTypeName)) {
                mkdir('public/typo3conf/ext/dw_boilerplate/Resources/Private/Partials/PageType/' . $pageTypeName, 0777, true);
            }
            file_put_contents($pageTypeTemplate, $pageTypeTemplateContent);
        }
    }

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
                if (count(explode(',', $field)) === 4 && count(explode(';', self::getFirstFieldItem($field))) !== 3) {
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
     * @return array
     */
    public function getFieldItems($field)
    {
        $fieldItems = explode('*', explode(',', $field)[3]);
        array_pop($fieldItems);
        return $fieldItems;
    }

    /**
     * @param $field
     * @return string
     */
    public function getFirstFieldItem($field)
    {
        return explode('*', explode(',', $field)[3])[0];
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

    /**
     * @param $TCAFieldTypes
     * @param $table
     * @param $fieldType
     * @return bool
     */
    public function isFieldTypeDefault($TCAFieldTypes, $table, $fieldType)
    {
        return $TCAFieldTypes[$table][$fieldType]['isFieldDefault'];
    }
}
