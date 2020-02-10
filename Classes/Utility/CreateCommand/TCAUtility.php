<?php
namespace Digitalwerk\ContentElementRegistry\Utility\CreateCommand;

use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Config\TCAFieldTypes;
use Digitalwerk\ContentElementRegistry\Utility\GeneralCreateCommandUtility;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class TCAUtility
 * @package Digitalwerk\ContentElementRegistry\Utility\CreateCommand
 */
class TCAUtility
{
    /**
     * @param $table
     * @param $contentElementName
     * @param $secondDesignation
     * @param $fields
     * @param $relativePath
     * @param $commandFieldType
     * @param $extraSpaces
     * @param $extensionName
     * @param $extraSpaces2
     * @param string $relativePathToClass
     * @return string
     * Return fields formatted like in TCA (format string)
     */
    public static function addColumnsToTCA($table ,$contentElementName, $secondDesignation, $fields, $relativePath, $extraSpaces, $extensionName, $extraSpaces2, $relativePathToClass = '')
    {
        if (!empty($fields)) {
            $generalCreateCommandUtility = GeneralUtility::makeInstance(GeneralCreateCommandUtility::class);
            $fieldsToArray = $generalCreateCommandUtility->fieldsToArray($fields);
            $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypes::class);
            $result = [];

            foreach ($fieldsToArray as $field) {
                $fieldName = $generalCreateCommandUtility->getFieldName($field);
                $fieldType = $generalCreateCommandUtility->getFieldType($field);
                $fieldItems = $generalCreateCommandUtility->getFieldItems($field);
                $fieldItem = $generalCreateCommandUtility->getFirstFieldItem($field);

                if (!empty($TCAFieldTypes->getTCAFieldTypes($table)[$table][$fieldType])) {
                    $fieldConfig = $TCAFieldTypes->getTCAFieldTypes($table, $contentElementName, $secondDesignation, $fieldName, $field, $relativePath, $fieldType, $fieldItem, $relativePathToClass)[$table][$fieldType]['config'];
                    if (null !== $fieldConfig) {
                        $result[] = (new TCAUtility)->generateFieldInTCA($fieldName, $secondDesignation, $table, $contentElementName, $fieldConfig, $extraSpaces, $extensionName);
                    }

                    if ($TCAFieldTypes->getTCAFieldTypes($table)[$table][$fieldType]['FlexFormItemsAllowed']) {
                        //Create FlexForm
                        FlexFormUtility::createFlexForm(
                            "public/typo3conf/ext/dw_boilerplate/Configuration/FlexForms/ContentElement/dwboilerplate_" . strtolower($contentElementName) . '.xml',
                            $fieldItems,
                            $contentElementName,
                            $table,
                            true,
                            $fieldType
                        );
                    }
                } else {
//                Field does not exist
                    throw new InvalidArgumentException('Field "' . $fieldType . '" does not exist.4');
                }
            }

            return implode("\n" . $extraSpaces2, $result);
        }
    }

    /**
     * @param $fields
     * @param $name
     * @param $table
     * @return string
     * Return IRRE field's name (format string)
     */
    public static function addFieldsToIRRETypeTCA($fields, $name, $table)
    {
        if (!empty($fields) || $fields !== '-') {
            $generalCreateCommandUtility = GeneralUtility::makeInstance(GeneralCreateCommandUtility::class);
            $fieldsToArray = $generalCreateCommandUtility->fieldsToArray($fields);
            $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypes::class)->getTCAFieldTypes($table);
            $createdFields = [];

            foreach ($fieldsToArray as $field) {
                $fieldName = $generalCreateCommandUtility->getFieldName($field);
                $fieldType = $generalCreateCommandUtility->getFieldType($field);

                if ($generalCreateCommandUtility->isFieldTypeDefault($TCAFieldTypes, $table, $fieldType)) {
                    $createdFields[] = $fieldType;
                } elseif (!$generalCreateCommandUtility->isFieldTypeDefault($TCAFieldTypes, $table, $fieldType)) {
                    $createdFields[] = strtolower($name).'_'.$fieldName;
                } else {
//                    Fieldtype does not exist
                    throw new InvalidArgumentException('Field "' . $fieldType . '" does not exist.5');
                }
            }

            return implode(', ', $createdFields) . ',';
        } else {
            return '';
        }
    }


    public static function getDefaultFieldsWithAnotherTitle($table, $contentElementName, $secondDesignation, $fields, $extraSpaces = '')
    {
        $generalCreateCommandUtility = GeneralUtility::makeInstance(GeneralCreateCommandUtility::class);
        $fieldsToArray = $generalCreateCommandUtility->fieldsToArray($fields);
        $defaultFieldsWithAnotherTitle = [];
        $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypes::class)->getTCAFieldTypes($table);

        foreach ($fieldsToArray as $field) {
            $fieldName = $generalCreateCommandUtility->getFieldName($field);
            $fieldType = $generalCreateCommandUtility->getFieldType($field);
            $fieldTitle = $generalCreateCommandUtility->getFieldTitle($field);
            if ($fieldTitle !== $TCAFieldTypes[$table][$fieldType]['defaultFieldTitle'] && $generalCreateCommandUtility->isFieldTypeDefault($TCAFieldTypes, $table, $fieldType))
            {
                if ($TCAFieldTypes[$table][$fieldType]['inlineFieldsAllowed']) {
                    $fieldItem = $generalCreateCommandUtility->getFirstFieldItem($field);
                    $fieldItemName = $generalCreateCommandUtility->getItemName($fieldItem);

                    $defaultFieldsWithAnotherTitle[] =
                        $extraSpaces . '            \''.$fieldType.'\' => [
                '.$extraSpaces.'\'label\' => \'LLL:EXT:dw_boilerplate/Resources/Private/Language/locallang_db.xlf:' . $table . '.dwboilerplate_'.strtolower($contentElementName).'.'. strtolower($secondDesignation).'_'. strtolower($fieldName).'\',
                '.$extraSpaces.'\'config\' => [
                    '.$extraSpaces.'\'overrideChildTca\' => [
                        '.$extraSpaces.'\'columns\' => [
                            '.$extraSpaces.'\'type\' => [
                                '.$extraSpaces.'\'config\' => [
                                    '.$extraSpaces.'\'items\' => [
                                        [\'LLL:EXT:dw_boilerplate/Resources/Private/Language/locallang_db.xlf:tt_content.dwboilerplate_'.strtolower($contentElementName).'_'.strtolower($fieldItemName).'\', self::CONTENT_RELATION_'.strtoupper($fieldItemName).'],
                                    '.$extraSpaces.'],
                                    '.$extraSpaces.'\'default\' => self::CONTENT_RELATION_'.strtoupper($fieldItemName).'
                                '.$extraSpaces.'],
                            '.$extraSpaces.'],
                        '.$extraSpaces.'],
                    '.$extraSpaces.'],
                '.$extraSpaces.'],
            '.$extraSpaces.'],';
                    TranslationUtility::addStringToTranslation('public/typo3conf/ext/dw_boilerplate/Resources/Private/Language/locallang_db.xlf','tt_content.dwboilerplate_'.strtolower($contentElementName).'_'.strtolower($fieldItemName),str_replace('-', ' ', $generalCreateCommandUtility->getItemTitle($fieldItem)));
                } else {
                    $defaultFieldsWithAnotherTitle[] =
                        $extraSpaces . '            \''.$fieldType.'\' => [
                '.$extraSpaces.'\'label\' => \'LLL:EXT:dw_boilerplate/Resources/Private/Language/locallang_db.xlf:' . $table . '.dwboilerplate_'.strtolower($contentElementName).'.'. strtolower($secondDesignation).'_'. strtolower($fieldName).'\',
            '.$extraSpaces.'],';
                }

            }
        }

        return implode('
', $defaultFieldsWithAnotherTitle);
    }

    /**
     * @param $field
     * @param $table
     * @param $extensionName
     * @param $name
     * @param $secondDesignation
     * @param $relativePath
     * @param $extraSpaces
     * @return bool
     */
    public static function addFieldsItemsToTCA($field, $table, $extensionName,$name, $secondDesignation, $relativePath, $extraSpaces)
    {
        if (!empty($field) && !empty($table)) {
            $generalCreateCommandUtility = GeneralUtility::makeInstance(GeneralCreateCommandUtility::class);
            $result = [];
            $fieldName = $generalCreateCommandUtility->getFieldName($field);
            $fieldType = $generalCreateCommandUtility->getFieldType($field);
            $fieldItems = $generalCreateCommandUtility->getFieldItems($field);

            if (!empty($fieldItems[0]) && (new TCAFieldTypes)->getTCAFieldTypes($table)[$table][$fieldType]['FlexFormItemsAllowed'] !== true) {
                if ((new TCAFieldTypes)->getTCAFieldTypes($table)[$table][$fieldType]['TCAItemsAllowed'] === true) {
                    foreach ($fieldItems as $fieldItem) {
                        $itemName = $generalCreateCommandUtility->getItemName($fieldItem);

                        $result[] = '[\'LLL:EXT:' . $extensionName . '/Resources/Private/Language/locallang_db.xlf:' . $table . '.' . str_replace('_', '', $extensionName) . '_'.strtolower($name).'.'. strtolower($secondDesignation).'_'.$fieldName.'.' . strtolower($itemName) . '\', ' . $relativePath . '::' . strtoupper($fieldName) . '_' .strtoupper($itemName) . '],';
                    }
                } else {
                    throw new InvalidArgumentException('You can not add items to ' . $fieldType . ', because items is not allowed.');
                }
            }

            return implode("\n                " . $extraSpaces , $result);
        }
    }

    /**
     * @param $fieldName
     * @param $secondDesignation
     * @param $table
     * @param $name
     * @param $fieldConfig
     * @param $extraSpaces
     * @param $extensionName
     * @return string
     */
    public function generateFieldInTCA($fieldName, $secondDesignation, $table, $name, $fieldConfig, $extraSpaces, $extensionName): string
    {
        return
'\''.strtolower($secondDesignation).'_'.$fieldName.'\' => [
    ' . $extraSpaces . '\'label\' => \'LLL:EXT:' . $extensionName . '/Resources/Private/Language/locallang_db.xlf:' . $table . '.' . str_replace('_','',$extensionName) . '_'.strtolower($name).'.'. strtolower($secondDesignation).'_'.$fieldName.'\',
    ' . $extraSpaces . '\'config\' => ' . $fieldConfig . '
' . $extraSpaces . '],';
    }
}
