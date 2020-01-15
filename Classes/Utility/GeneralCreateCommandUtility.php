<?php
namespace Digitalwerk\ContentElementRegistry\Utility;

use Digitalwerk\ContentElementRegistry\Command\FlexFormFieldTypes;
use Digitalwerk\ContentElementRegistry\Command\TCAFieldTypesAndImportedClasses;
use DOMDocument;
use SimpleXMLElement;
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
                if (count(explode(',', $field)) === 4 && count(explode(';', explode('*', explode(',', $field)[3])[0]))  !== 3) {
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
     * @param $file
     * @param $table
     * @param $contentElementName
     * @param $secondDesignation
     * @param $fields
     * Create and add fields translation (XML format)
     * @param $commandFieldType
     * @param $extensionName
     */
    public static function addFieldsTitleToTranslation($file, $table, $contentElementName, $secondDesignation, $fields, $commandFieldType, $extensionName)
    {
        $fieldsToArray = GeneralUtility::makeInstance(GeneralCreateCommandUtility::class)->fieldsToArray($fields);
        $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypesAndImportedClasses::class);
        $xml = simplexml_load_file($file);
        $body = $xml->file->body;

        foreach ($fieldsToArray as $field) {
            $fieldName = explode(',',$field)[0];
            $fieldType = explode(',',$field)[1];
            $fieldTitle = explode(',',$field)[2];
            $fieldItems = explode('*', explode(',', $field)[3]);
            array_pop($fieldItems);

            if ($fieldTitle !== $TCAFieldTypes->getTCAFieldTypes()[$commandFieldType][$fieldType]['defaultFieldTitle'])
            {
                $transUnitField = $body->addChild('trans-unit');
                $transUnitField->addAttribute('id',$table.'.' . strtolower($extensionName) . '_'. strtolower($contentElementName).'.'. strtolower($secondDesignation).'_'. strtolower($fieldName).'');
                $transUnitField->addChild('source', ''.str_replace('-',' ',$fieldTitle).'');
            }

            if ($TCAFieldTypes->getTCAFieldTypes()[$commandFieldType][$fieldType]['TCAItemsAllowed'] === true) {
                foreach ($fieldItems as $fieldItem) {
                    $itemName = explode(';' ,$fieldItem)[0];
                    $itemTitle = explode(';' ,$fieldItem)[1];

                    $transUnitFieldValue1 = $body->addChild('trans-unit');
                    $transUnitFieldValue1->addAttribute('id',$table . '.' . strtolower($extensionName) . '_'. strtolower($contentElementName).'.'. strtolower($secondDesignation).'_'. strtolower($fieldName) . '.' . strtolower($itemName));
                    $transUnitFieldValue1->addChild('source', str_replace('-',' ',$itemTitle));
                }
            } elseif (!empty($fieldItems) && $TCAFieldTypes->getTCAFieldTypes()[$commandFieldType][$fieldType]['FlexFormItemsAllowed'] !== true && $TCAFieldTypes->getTCAFieldTypes()[$commandFieldType][$fieldType]['inlineFieldsAllowed'] !== true) {
                throw new InvalidArgumentException('You can not add items to ' . $fieldType . ', because items is not allowed.');
            }
        }

        $dom = new DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());
        $formatXml = new SimpleXMLElement($dom->saveXML());
        $formatXml->saveXML($file);
    }

    /**
     * @param $file
     * @param $translationId
     * @param $translationValue
     */
    public static function addTitleToTranslation($file, $translationId, $translationValue)
    {
        $xml = simplexml_load_file($file);
        $body = $xml->file->body;

        $transUnit = $body->addChild('trans-unit');
        $transUnit->addAttribute('id',$translationId);
        $transUnit->addChild('source', ''.str_replace('-',' ',$translationValue).'');

        $dom = new DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());
        $formatXml = new SimpleXMLElement($dom->saveXML());
        $formatXml->saveXML($file);
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
     * @param $commandFieldType
     * @return string
     * Return CE sql table fields (format string)
     */
    public static function addFieldsToTable($fields, $name, $commandFieldType)
    {
        $fieldsToArray = (new GeneralCreateCommandUtility)->fieldsToArray($fields);
        $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypesAndImportedClasses::class);
        $result = [];


        foreach ($fieldsToArray as $field) {
            $fieldName = explode(',',$field)[0];
            $fieldType = explode(',',$field)[1];

            if ($TCAFieldTypes->getTCAFieldTypes()[$commandFieldType][$fieldType]) {
                if (null !== $TCAFieldTypes->getTCAFieldTypes()[$commandFieldType][$fieldType]['tableFieldDataType']) {
                    $result[] = strtolower($name) . '_' . $fieldName.' ' . $TCAFieldTypes->getTCAFieldTypes()[$commandFieldType][$fieldType]['tableFieldDataType'];
                }
            } else {
//                Field does not exist
                throw new InvalidArgumentException('Field "' . $fieldType . '" does not exist.');
            }
        }

        return implode(",\n    ", $result);
    }

    /**
     * @param $fields
     * @param $commandFieldType
     * @param $name
     * @param string $betweenProtectedsAndGetters
     * @param string $inlineRelativePath
     * @return string
     * Return content element model's protected and getters (string format)
     */
    public static function addFieldsToModel($fields, $commandFieldType, $name, $betweenProtectedsAndGetters = '', $inlineRelativePath = '')
    {
        if (!empty($fields)) {
            $fieldsToArray = (new GeneralCreateCommandUtility)->fieldsToArray($fields);
            $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypesAndImportedClasses::class);
            $resultOfTraits = [];
            $resultOfProtected = [];
            $resultOfGetters = [];

            foreach ($fieldsToArray as $field) {
                $fieldName = explode(',',$field)[0];
                $fieldType = explode(',',$field)[1];

                if ($TCAFieldTypes->getTCAFieldTypes()[$commandFieldType][$fieldType]['defaultFieldName'] === $fieldName && !empty($TCAFieldTypes->getTCAFieldTypes()[$commandFieldType][$fieldType]['trait']))
                {
                    if (in_array('use ' . ucfirst($TCAFieldTypes->getTCAFieldTypes()[$commandFieldType][$fieldType]['trait']) . ';', $resultOfTraits) === false) {
                        $resultOfTraits[] = 'use ' . ucfirst($TCAFieldTypes->getTCAFieldTypes()[$commandFieldType][$fieldType]['trait']) . ';';
                    }
                } elseif ($TCAFieldTypes->getTCAFieldTypes()[$commandFieldType][$fieldType]['specialFieldName'] === $fieldName && !empty($TCAFieldTypes->getTCAFieldTypes()[$commandFieldType][$fieldType]['trait'])) {
                    if (in_array('use ' . ucfirst($TCAFieldTypes->getTCAFieldTypes()[$commandFieldType][$fieldType]['trait']) . ';', $resultOfTraits) === false) {
                        $resultOfTraits[] = 'use ' . ucfirst($TCAFieldTypes->getTCAFieldTypes()[$commandFieldType][$fieldType]['trait']) . ';';
                    }
                } else {
                    if ($TCAFieldTypes->getTCAFieldTypes()[$commandFieldType][$fieldType]['inlineFieldsAllowed']) {
                        $fieldItem = explode(';', explode('*', explode(',', $field)[3])[0]);
                        $typeValue =
                            [
                                $TCAFieldTypes->getTCAFieldTypes()[$commandFieldType][$fieldType]['modelDataTypes']['propertyDataType'],
                                $TCAFieldTypes->getTCAFieldTypes('','', '', '','','','','',$fieldItem,'',$inlineRelativePath)[$commandFieldType][$fieldType]['modelDataTypes']['propertyDataTypeDescribe'],
                                $TCAFieldTypes->getTCAFieldTypes()[$commandFieldType][$fieldType]['modelDataTypes']['getterDataTypeDescribe'],
                                $TCAFieldTypes->getTCAFieldTypes()[$commandFieldType][$fieldType]['modelDataTypes']['getterDataType'],
                            ];
                    } else {
                        $typeValue =
                            [
                                $TCAFieldTypes->getTCAFieldTypes()[$commandFieldType][$fieldType]['modelDataTypes']['propertyDataType'],
                                $TCAFieldTypes->getTCAFieldTypes()[$commandFieldType][$fieldType]['modelDataTypes']['propertyDataTypeDescribe'],
                                $TCAFieldTypes->getTCAFieldTypes()[$commandFieldType][$fieldType]['modelDataTypes']['getterDataTypeDescribe'],
                                $TCAFieldTypes->getTCAFieldTypes()[$commandFieldType][$fieldType]['modelDataTypes']['getterDataType'],
                            ];
                    }


                    $resultOfProtected[] =
                        '/**
     * @var '.$typeValue[1].'
     */
    protected $'.str_replace(' ','',lcfirst(ucwords(str_replace('_',' ',$fieldName)))).' = '.$typeValue[0].';';

                    $resultOfGetters[] =
                        '/**
     * @return '.$typeValue[2].'
     */
    public function get'.str_replace(' ','',ucwords(str_replace('_',' ',$fieldName))).'(): '.$typeValue[3].'
    {
        return $this->'.str_replace(' ','',lcfirst(ucwords(str_replace('_',' ',$fieldName)))).';
    }';
                }
            }


            $resultOfTraits = implode('
    ', $resultOfTraits);

            $resultOfProtected = implode('
    
    ', $resultOfProtected);

            $resultOfGetters = implode('
    
    ', $resultOfGetters);

            $resultOfTraits = $resultOfTraits ?  $resultOfTraits . '
    
    ' : '';

            $resultOfProtected = $resultOfProtected ?  $resultOfProtected . '
    
    ' : '';

            $betweenProtectedsAndGetters = $betweenProtectedsAndGetters ?  $betweenProtectedsAndGetters . '
    
    ' : '';

            $resultOfGetters = $resultOfGetters ?  $resultOfGetters . '
    
    ' : '';

            return rtrim($resultOfTraits . $resultOfProtected . $betweenProtectedsAndGetters . $resultOfGetters);
        } else {
            return null;
        }
    }

    /**
     * @param $fields
     * @param $commandFieldType
     * @return string
     */
    public static function addConstantsToModel($fields, $commandFieldType)
    {
        if (!empty($fields)) {
            $fieldsToArray = (new GeneralCreateCommandUtility)->fieldsToArray($fields);
            $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypesAndImportedClasses::class);
            $result = [];

            foreach ($fieldsToArray as $field) {
                $fieldName = explode(',', $field)[0];
                $fieldType = explode(',', $field)[1];
                $fieldItems = explode('*', explode(',', $field)[3]);
                array_pop($fieldItems);

                if ($TCAFieldTypes->getTCAFieldTypes()[$commandFieldType][$fieldType]['TCAItemsAllowed'] === true) {
                    foreach ($fieldItems as $fieldItem) {
                        $itemName = explode(';' ,$fieldItem)[0];
                        $itemValue = explode(';' ,$fieldItem)[2];
                        $result[] =  'const ' . strtoupper($fieldName) . '_' .strtoupper($itemName) . ' = ' . '"' . $itemValue . '";';
                    }
                } elseif (!empty($fieldItems) && $TCAFieldTypes->getTCAFieldTypes()[$commandFieldType][$fieldType]['FlexFormItemsAllowed'] !== true && $TCAFieldTypes->getTCAFieldTypes()[$commandFieldType][$fieldType]['inlineFieldsAllowed'] !== true) {
                    throw new InvalidArgumentException('You can not add items to ' . $fieldType . ', because items is not allowed.');
                }
            }
            return implode("\n    ", $result);
        }
    }

    /**
     * @param $fields
     * @param $commandFieldType
     * @param null $optionalClass
     * @return string
     * Return string of class which needs to be imported to model
     */
    public static function importClassToModel($fields, $commandFieldType, $optionalClass = null)
    {
        if (!empty($fields) || $fields !== '-') {
            $fieldsToArray = GeneralUtility::makeInstance(GeneralCreateCommandUtility::class)->fieldsToArray($fields);
            $TCAFieldTypesAndImportedClasses = GeneralUtility::makeInstance(TCAFieldTypesAndImportedClasses::class);
            $result = [];
            $importClass = $TCAFieldTypesAndImportedClasses->getClasses();

            foreach ($fieldsToArray as $field) {
                $fieldName = explode(',', $field)[0];
                $fieldType = explode(',', $field)[1];

                if ($optionalClass !== null && in_array($importClass[$optionalClass], $result) === false) {
                    $result[] = $importClass[$optionalClass];
                }
                if ($TCAFieldTypesAndImportedClasses->getTCAFieldTypes()[$commandFieldType][$fieldType]['needImportClass']) {
                    if ($TCAFieldTypesAndImportedClasses->getTCAFieldTypes()[$commandFieldType][$fieldType]['importClassConditional']['needDefaulFieldName']) {
                        if ($TCAFieldTypesAndImportedClasses->getTCAFieldTypes()[$commandFieldType][$fieldType]['defaultFieldName'] === $fieldName) {
                            foreach ($TCAFieldTypesAndImportedClasses->getTCAFieldTypes()[$commandFieldType][$fieldType]['importClass'] as $importClassFromField) {
                                if (in_array($importClass[$importClassFromField], $result) === false){
                                    $result[] = $importClass[$importClassFromField];
                                }
                            }
                        }
                    } elseif ($TCAFieldTypesAndImportedClasses->getTCAFieldTypes()[$commandFieldType][$fieldType]['importClassConditional']['needSpecialFieldName']) {
                        if ($TCAFieldTypesAndImportedClasses->getTCAFieldTypes()[$commandFieldType][$fieldType]['specialFieldName'] === $fieldName) {
                            foreach ($TCAFieldTypesAndImportedClasses->getTCAFieldTypes()[$commandFieldType][$fieldType]['importClass'] as $importClassFromField) {
                                if (in_array($importClass[$importClassFromField], $result) === false){
                                    $result[] = $importClass[$importClassFromField];
                                }
                            }
                        }
                    } else {
                        foreach ($TCAFieldTypesAndImportedClasses->getTCAFieldTypes()[$commandFieldType][$fieldType]['importClass'] as $importClassFromField) {
                            if (in_array($importClass[$importClassFromField], $result) === false){
                                $result[] = $importClass[$importClassFromField];
                            }
                        }
                    }
                }
            }

            return implode("\n", $result);
        } else {
            return null;
        }
    }

    /**
     * @param $fields
     * @param $name
     * @param $commandFieldType
     * @param bool $isFieldInTCA
     * @return string
     * Return string of fields which needs to be imported to flexForm
     */
    public static function addFieldsToFlexForm($fields, $name, $commandFieldType, bool $isFieldInTCA)
    {
        if (!empty($fields)) {
            $fieldsToArray = GeneralUtility::makeInstance(GeneralCreateCommandUtility::class)->fieldsToArray($fields);
            $flexFormFieldTypes = GeneralUtility::makeInstance(FlexFormFieldTypes::class);
            $TCAFieldTypesAndImportedClasses = GeneralUtility::makeInstance(TCAFieldTypesAndImportedClasses::class);
            $result = [];
            foreach ($fieldsToArray as $field) {
                $fieldName = explode(',', $field)[0];
                $fieldType = explode(',', $field)[1];
                $fieldTitle = explode(',', $field)[2];
                $fieldItems = explode('*', explode(',', $field)[3]);
                array_pop($fieldItems);

                if ($isFieldInTCA === false) {
                    if ($flexFormFieldTypes->getFlexFormFieldTypes()[$commandFieldType][$fieldType]) {
                        $result[] = "<" . $fieldName . ">
                        <TCEforms>
                            <label>LLL:EXT:dw_page_types/Resources/Private/Language/locallang_db.xlf:plugin." . strtolower($name) . ".FlexForm.General.". $fieldName . "</label>
                            <config>
                                " . $flexFormFieldTypes->getFlexFormFieldTypes()[$commandFieldType][$fieldType]['config'] . "
                            </config>
                        </TCEforms>
                    </" . $fieldName . ">";

                        self::addTitleToTranslation(
                            'public/typo3conf/ext/dw_page_types/Resources/Private/Language/locallang_db.xlf',
                            "plugin." . strtolower($name) . ".FlexForm.General.". $fieldName,
                            $fieldTitle
                        );
                    } else {
                        throw new InvalidArgumentException('Field type ' . $fieldType . ' does not exist in FlexForm field types.');
                    }
                } else {
                    if ($TCAFieldTypesAndImportedClasses->getTCAFieldTypes()[$commandFieldType][$fieldType]['FlexFormItemsAllowed'] === true) {
                        foreach ($fieldItems as $fieldItem) {
                            $itemName = explode(';' ,$fieldItem)[0];
                            $itemType = explode(';' ,$fieldItem)[1];
                            $itemTitle = explode(';' ,$fieldItem)[2];
                            if ($flexFormFieldTypes->getFlexFormFieldTypes()[$commandFieldType][$itemType]) {
                                $result[] =  "<" . $itemName . ">
                    <TCEforms>
                        <label>LLL:EXT:dw_boilerplate/Resources/Private/Language/locallang_db.xlf:tt_content.dwboilerplate_" . strtolower($name) . ".pi_flexform." . strtolower($itemName) . "</label>
                        <config>
                            " . $flexFormFieldTypes->getFlexFormFieldTypes()[$commandFieldType][$itemType]['config'] . "
                        </config>
                    </TCEforms>
                </" . $itemName . ">";
                                self::addTitleToTranslation(
                                    'public/typo3conf/ext/dw_boilerplate/Resources/Private/Language/locallang_db.xlf',
                                    "tt_content.dwboilerplate_" . strtolower($name) . ".pi_flexform." . strtolower($itemName),
                                    $itemTitle
                                );
                            }
                            else {
                                throw new InvalidArgumentException('Field type ' . $fieldType . ' does not exist in FlexForm field types.');
                            }
                        }
                    } elseif (!empty($fieldItems) && $TCAFieldTypesAndImportedClasses->getTCAFieldTypes()[$commandFieldType][$fieldType]['TCAItemsAllowed'] !== true && $TCAFieldTypesAndImportedClasses->getTCAFieldTypes()[$commandFieldType][$fieldType]['inlineFieldsAllowed'] !== true) {
                        throw new InvalidArgumentException('You can not add items to ' . $fieldType . ', because fields items is not allowed.');
                    }
                }
            }
            return implode("\n                    ", $result);
        }
    }

    /**
     * @param $fields
     * @param $name
     * @param $commandFieldType
     * @param $extraSpace
     * @return string
     * Return field's name with --linebreak-- (format string)
     */
    public static function addFieldsToPalette($fields, $name, $commandFieldType, $extraSpace)
    {
        if (!empty($fields)) {
            $fieldsToArray = (new GeneralCreateCommandUtility)->fieldsToArray($fields);
            $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypesAndImportedClasses::class);
            $createdFields = [];


            foreach ($fieldsToArray as $field) {
                $fieldName = explode(',',$field)[0];
                $fieldType = explode(',', $field)[1];

                if ($TCAFieldTypes->getTCAFieldTypes()[$commandFieldType][$fieldType]['isFieldDefault']) {
                    $createdFields[] = '--linebreak--, ' . $fieldType;
                } elseif ($TCAFieldTypes->getTCAFieldTypes()[$commandFieldType][$fieldType]['isFieldDefault'] === false) {
                    $createdFields[] = '--linebreak--, ' . strtolower($name).'_'.$fieldName;
                } else {
//                    Fieldtype does not exist
                    throw new InvalidArgumentException('Field "' . $fieldType . '" does not exist.');
                }
            }
            return preg_replace('/--linebreak--, /', '', implode(',
            ' . $extraSpace, $createdFields),1);
        } else {
            return '';
        }
    }

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
    public static function addColumnsToTCA($table ,$contentElementName, $secondDesignation, $fields, $relativePath, $commandFieldType, $extraSpaces, $extensionName, $extraSpaces2, $relativePathToClass = '')
    {
        if (!empty($fields)) {
            $fieldsToArray = (new GeneralCreateCommandUtility)->fieldsToArray($fields);
            $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypesAndImportedClasses::class);
            $result = [];

            foreach ($fieldsToArray as $field) {
                $fieldName = explode(',', $field)[0];
                $fieldType = explode(',', $field)[1];
                $fieldItem = explode(';', explode('*', explode(',', $field)[3])[0]);

                if (!empty($TCAFieldTypes->getTCAFieldTypes()[$commandFieldType][$fieldType])) {
                    $fieldConfig = $TCAFieldTypes->getTCAFieldTypes($table, $contentElementName, $secondDesignation, $fieldName, $field, $relativePath, $fieldType, $commandFieldType, $fieldItem, $relativePathToClass)[$commandFieldType][$fieldType]['config'];
                    if (null !== $fieldConfig) {
                        $result[] = $TCAFieldTypes->generateFieldInTCA($fieldName, $secondDesignation, $table, $contentElementName, $fieldConfig, $extraSpaces, $extensionName);
                    }
                } else {
//                Field does not exist
                    throw new InvalidArgumentException('Field "' . $fieldType . '" does not exist.');
                }
            }

            return implode('
    ' . $extraSpaces2, $result);
        }

    }

    /**
     * @param $fields
     * @param $name
     * @param $commandFieldType
     * @return string
     * Return full field's name => protected name (format string)
     */
    public static function addFieldsToTyposcriptMapping($fields, $name, $commandFieldType)
    {
        if (!empty($fields)) {
            $fieldsToArray = (new GeneralCreateCommandUtility)->fieldsToArray($fields);
            $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypesAndImportedClasses::class);
            $createdFields = [];

            foreach ($fieldsToArray as $field) {
                $fieldName = explode(',',$field)[0];
                $fieldType = explode(',', $field)[1];

                if ($fieldName === $fieldType && $TCAFieldTypes->getTCAFieldTypes()[$commandFieldType][$fieldType]['isFieldDefault']) {
//                   Nothing to add (default fields)
                } elseif ($fieldName !== $fieldType && $TCAFieldTypes->getTCAFieldTypes()[$commandFieldType][$fieldType]['isFieldDefault']) {
                    $createdFields[] = $fieldType.'.mapOnProperty = '.str_replace(' ','',lcfirst(ucwords(str_replace('_',' ',$fieldName))));
                } elseif ($TCAFieldTypes->getTCAFieldTypes()[$commandFieldType][$fieldType]) {
                    $createdFields[] = strtolower($name).'_'.$fieldName.'.mapOnProperty = '.str_replace(' ','',lcfirst(ucwords(str_replace('_',' ',$fieldName))));
                } else {
//                    ErrorMessage (FieldType does not exist)
                    throw new InvalidArgumentException('Field "' . $fieldType . '" does not exist.');
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
     * @param $commandFieldType
     * @param $tableName
     * @param $recordType
     * @param $pathToModel
     * @return string
     * Return TypoScript Mapping (format string)
     */
    public static function getTyposcriptMapping($name, $fields, $commandFieldType, $tableName, $recordType, $pathToModel)
    {
        $mappingFields = self::addFieldsToTyposcriptMapping($fields, $name, $commandFieldType);
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
          tableName = ' . $tableName . '
          recordType = ' . $recordType . '' . $columnsMapOnProperty . '
        }
      }';


        return $template;
    }
}
