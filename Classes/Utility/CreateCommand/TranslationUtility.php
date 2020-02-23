<?php
namespace Digitalwerk\ContentElementRegistry\Utility\CreateCommand;

use DOMDocument;
use SimpleXMLElement;

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
     * @param $id
     * @return string
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
