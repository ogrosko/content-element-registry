<?php
namespace Digitalwerk\ContentElementRegistry\Utility\CreateCommand;

use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Config\FlexFormFieldTypes;
use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Config\TCAFieldTypes;
use Digitalwerk\ContentElementRegistry\Utility\GeneralCreateCommandUtility;
use InvalidArgumentException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class FlexFormUtility
 * @package Digitalwerk\ContentElementRegistry\Utility\CreateCommand
 */
class FlexFormUtility
{
    /**
     * @param $fields
     * @param $name
     * @param $plugins
     * @return string
     */
    public static function addFieldsToFlexForm($fields, $name)
    {
        $generalCreateCommandUtility = GeneralUtility::makeInstance(GeneralCreateCommandUtility::class);
        $fieldsToArray = $generalCreateCommandUtility->fieldsToArray($fields);
        $flexFormFieldTypes = GeneralUtility::makeInstance(FlexFormFieldTypes::class);
        $result = [];
        foreach ($fieldsToArray as $field) {
            $fieldName = $generalCreateCommandUtility->getFieldName($field);
            $fieldType = $generalCreateCommandUtility->getFieldType($field);
            $fieldTitle = $generalCreateCommandUtility->getFieldTitle($field);

            if ($flexFormFieldTypes->getFlexFormFieldTypes()['plugins'][$fieldType]) {
                $result[] = "<" . $fieldName . ">
                        <TCEforms>
                            <label>LLL:EXT:dw_page_types/Resources/Private/Language/locallang_db.xlf:plugin." . strtolower($name) . ".FlexForm.General.". $fieldName . "</label>
                            <config>
                                " . $flexFormFieldTypes->getFlexFormFieldTypes()['plugins'][$fieldType]['config'] . "
                            </config>
                        </TCEforms>
                    </" . $fieldName . ">";

                TranslationUtility::addStringToTranslation(
                    'public/typo3conf/ext/dw_page_types/Resources/Private/Language/locallang_db.xlf',
                    "plugin." . strtolower($name) . ".FlexForm.General.". $fieldName,
                    $fieldTitle
                );
            } else {
                throw new InvalidArgumentException('Field type ' . $fieldType . ' does not exist in FlexForm field types.');
            }
        }
        return implode("\n                    ", $result);
    }

    /**
     * @param $fieldType
     * @param $items
     * @param $name
     * @param $table
     * @return string
     * Return string of fields which needs to be imported to flexForm
     */
    public static function addFieldsToFlexFormFromItems($fieldType, $items, $name, $table)
    {

        $generalCreateCommandUtility = GeneralUtility::makeInstance(GeneralCreateCommandUtility::class);
        $flexFormFieldTypes = GeneralUtility::makeInstance(FlexFormFieldTypes::class);
        $TCAFieldTypesAndImportedClasses = GeneralUtility::makeInstance(TCAFieldTypes::class)->getTCAFieldTypes($table);
        $result = [];

            if ($TCAFieldTypesAndImportedClasses[$table][$fieldType]['FlexFormItemsAllowed'] === true) {
                foreach ($items as $fieldItem) {
                    $itemName = $generalCreateCommandUtility->getItemName($fieldItem);
                    $itemType = $generalCreateCommandUtility->getItemType($fieldItem);
                    $itemTitle = $generalCreateCommandUtility->getItemTitle($fieldItem);
                    if ($flexFormFieldTypes->getFlexFormFieldTypes()['plugins'][$itemType]) {
                        $result[] =  "<" . $itemName . ">
                        <TCEforms>
                            <label>LLL:EXT:dw_boilerplate/Resources/Private/Language/locallang_db.xlf:tt_content.dwboilerplate_" . strtolower($name) . "." . $fieldType . "." . strtolower($itemName) . "</label>
                            <config>
                                " . $flexFormFieldTypes->getFlexFormFieldTypes()['plugins'][$itemType]['config'] . "
                            </config>
                        </TCEforms>
                    </" . $itemName . ">";
                        TranslationUtility::addStringToTranslation(
                            'public/typo3conf/ext/dw_boilerplate/Resources/Private/Language/locallang_db.xlf',
                            "tt_content.dwboilerplate_" . strtolower($name) . "." . $fieldType . "." . strtolower($itemName),
                            $itemTitle
                        );
                    }
                    else {
                        throw new InvalidArgumentException('Field type ' . $itemType . ' does not exist in FlexForm field types.');
                    }
                }
            } else {
                throw new InvalidArgumentException('You can not add items to ' . $fieldType . ', because fields items is not allowed.');
            }

        return implode("\n                    ", $result);
    }

    /**
     * @param $file
     * @param $fieldsOrItems
     * @param $name
     * @param $table
     * @param $areFlexFormFieldsAsItems
     * @param string $fieldType
     */
    public static function createFlexForm($file, $fieldsOrItems, $name, $table, $areFlexFormFieldsAsItems, $fieldType = '')
    {
        if ($areFlexFormFieldsAsItems) {
            if (!file_exists('public/typo3conf/ext/dw_boilerplate/Configuration/FlexForms/ContentElement')) {
                mkdir('public/typo3conf/ext/dw_boilerplate/Configuration/FlexForms/ContentElement/', 0777, true);
            }

            $flexFormItems = self::addFieldsToFlexFormFromItems($fieldType, $fieldsOrItems, $name, $table);
        } else {
            $flexFormItems = self::addFieldsToFlexForm($fieldsOrItems,$name);
        }

        //        Content element flexform
        $CEFlexFormContent = '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
<T3DataStructure>
    <meta>
        <langDisable>1</langDisable>
    </meta>
    <sheets>
        <sDEF>
            <ROOT>
                <type>array</type>
                <el>
                    ' . $flexFormItems . '
                </el>
            </ROOT>
        </sDEF>
    </sheets>
</T3DataStructure>
';

        file_put_contents($file, $CEFlexFormContent);
    }
}
