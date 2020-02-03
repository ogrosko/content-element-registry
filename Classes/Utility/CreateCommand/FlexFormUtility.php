<?php
namespace Digitalwerk\ContentElementRegistry\Utility\CreateCommand;

use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Config\FlexFormFieldTypes;
use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Config\TCAFieldTypes;
use Digitalwerk\ContentElementRegistry\Command\TCAFieldTypesAndImportedClasses;
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
     * @param $tableOrPlugins
     * @param bool $isFieldInTCA
     * @return string
     * Return string of fields which needs to be imported to flexForm
     */
    public static function addFieldsToFlexForm($fields, $name, $tableOrPlugins, bool $isFieldInTCA)
    {
        if (!empty($fields)) {
            $fieldsToArray = GeneralUtility::makeInstance(GeneralCreateCommandUtility::class)->fieldsToArray($fields);
            $flexFormFieldTypes = GeneralUtility::makeInstance(FlexFormFieldTypes::class);
            $TCAFieldTypesAndImportedClasses = GeneralUtility::makeInstance(TCAFieldTypes::class)->getTCAFieldTypes($tableOrPlugins);
            $result = [];
            foreach ($fieldsToArray as $field) {
                $fieldName = explode(',', $field)[0];
                $fieldType = explode(',', $field)[1];
                $fieldTitle = explode(',', $field)[2];
                $fieldItems = explode('*', explode(',', $field)[3]);
                array_pop($fieldItems);

                if ($isFieldInTCA === false) {
                    if ($flexFormFieldTypes->getFlexFormFieldTypes()[$tableOrPlugins][$fieldType]) {
                        $result[] = "<" . $fieldName . ">
                        <TCEforms>
                            <label>LLL:EXT:dw_page_types/Resources/Private/Language/locallang_db.xlf:plugin." . strtolower($name) . ".FlexForm.General.". $fieldName . "</label>
                            <config>
                                " . $flexFormFieldTypes->getFlexFormFieldTypes()[$tableOrPlugins][$fieldType]['config'] . "
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
                } else {
                    if ($TCAFieldTypesAndImportedClasses[$tableOrPlugins][$fieldType]['FlexFormItemsAllowed'] === true) {
                        foreach ($fieldItems as $fieldItem) {
                            $itemName = explode(';' ,$fieldItem)[0];
                            $itemType = explode(';' ,$fieldItem)[1];
                            $itemTitle = explode(';' ,$fieldItem)[2];
                            if ($flexFormFieldTypes->getFlexFormFieldTypes()[$tableOrPlugins][$itemType]) {
                                $result[] =  "<" . $itemName . ">
                    <TCEforms>
                        <label>LLL:EXT:dw_boilerplate/Resources/Private/Language/locallang_db.xlf:tt_content.dwboilerplate_" . strtolower($name) . ".pi_flexform." . strtolower($itemName) . "</label>
                        <config>
                            " . $flexFormFieldTypes->getFlexFormFieldTypes()[$tableOrPlugins][$itemType]['config'] . "
                        </config>
                    </TCEforms>
                </" . $itemName . ">";
                                TranslationUtility::addStringToTranslation(
                                    'public/typo3conf/ext/dw_boilerplate/Resources/Private/Language/locallang_db.xlf',
                                    "tt_content.dwboilerplate_" . strtolower($name) . ".pi_flexform." . strtolower($itemName),
                                    $itemTitle
                                );
                            }
                            else {
                                throw new InvalidArgumentException('Field type ' . $fieldType . ' does not exist in FlexForm field types.');
                            }
                        }
                    } elseif (!empty($fieldItems) && $TCAFieldTypesAndImportedClasses[$tableOrPlugins][$fieldType]['TCAItemsAllowed'] !== true && $TCAFieldTypesAndImportedClasses[$tableOrPlugins][$fieldType]['inlineFieldsAllowed'] !== true) {
                        throw new InvalidArgumentException('You can not add items to ' . $fieldType . ', because fields items is not allowed.');
                    }
                }
            }
            return implode("\n                    ", $result);
        }
    }
}
