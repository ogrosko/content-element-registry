<?php
namespace Digitalwerk\ContentElementRegistry\Utility\CreateCommand;

use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Config\TCAFieldTypes;
use Digitalwerk\ContentElementRegistry\Utility\GeneralCreateCommandUtility;
use DOMDocument;
use SimpleXMLElement;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class TranslationUtility
 * @package Digitalwerk\ContentElementRegistry\Utility\CreateCommand
 */
class TranslationUtility
{
    /**
     * @param $file
     * @param $translationId
     * @param $translationValue
     */
    public static function addStringToTranslation($file, $translationId, $translationValue)
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
     * @param $file
     * @param $table
     * @param $contentElementName
     * @param $secondDesignation
     * @param $fields
     * Create and add fields translation (XML format)
     * @param $extensionName
     */
    public static function addFieldsTitleToTranslation($file, $table, $contentElementName, $secondDesignation, $fields, $extensionName)
    {
        $fieldsToArray = GeneralUtility::makeInstance(GeneralCreateCommandUtility::class)->fieldsToArray($fields);
        $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypes::class)->getTCAFieldTypes($table);
        $xml = simplexml_load_file($file);
        $body = $xml->file->body;

        foreach ($fieldsToArray as $field) {
            $fieldName = explode(',',$field)[0];
            $fieldType = explode(',',$field)[1];
            $fieldTitle = explode(',',$field)[2];
            $fieldItems = explode('*', explode(',', $field)[3]);
            array_pop($fieldItems);

            if ($fieldTitle !== $TCAFieldTypes[$table][$fieldType]['defaultFieldTitle'])
            {
                $transUnitField = $body->addChild('trans-unit');
                $transUnitField->addAttribute('id',$table.'.' . strtolower($extensionName) . '_'. strtolower($contentElementName).'.'. strtolower($secondDesignation).'_'. strtolower($fieldName).'');
                $transUnitField->addChild('source', ''.str_replace('-',' ',$fieldTitle).'');
            }

            if ($TCAFieldTypes[$table][$fieldType]['TCAItemsAllowed'] === true) {
                foreach ($fieldItems as $fieldItem) {
                    $itemName = explode(';' ,$fieldItem)[0];
                    $itemTitle = explode(';' ,$fieldItem)[1];

                    $transUnitFieldValue1 = $body->addChild('trans-unit');
                    $transUnitFieldValue1->addAttribute('id',$table . '.' . strtolower($extensionName) . '_'. strtolower($contentElementName).'.'. strtolower($secondDesignation).'_'. strtolower($fieldName) . '.' . strtolower($itemName));
                    $transUnitFieldValue1->addChild('source', str_replace('-',' ',$itemTitle));
                }
            } elseif (!empty($fieldItems) && $TCAFieldTypes[$table][$fieldType]['FlexFormItemsAllowed'] !== true && $TCAFieldTypes[$table][$fieldType]['inlineFieldsAllowed'] !== true) {
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
     * @param $id
     */
    public static function getSourceByFileNameAndId($file, $id)
    {
        $xml = simplexml_load_file($file);
        if ($xml->file->body) {
            $children = $xml->file->body->children();

            foreach ($children as $child) {
                if ((string) $child->attributes()->id[0] === $id) {
                    return (string) $child->source[0];
                }
            }
        }
    }
}
