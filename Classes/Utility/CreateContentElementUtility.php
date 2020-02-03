<?php
namespace Digitalwerk\ContentElementRegistry\Utility;


use Digitalwerk\ContentElementRegistry\Command\TCAFieldTypesAndImportedClasses;
use Digitalwerk\ContentElementRegistry\Utility\CreateCommand\ModelUtility;
use Digitalwerk\ContentElementRegistry\Utility\CreateCommand\SQLUtility;
use Digitalwerk\ContentElementRegistry\Utility\CreateCommand\TCAUtility;
use Digitalwerk\ContentElementRegistry\Utility\CreateCommand\TranslationUtility;
use Digitalwerk\ContentElementRegistry\Utility\CreateCommand\TypoScriptUtility;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class CreateContentElementUtility
 * @package Digitalwerk\DwBoilerplate\Utility
 */
class CreateContentElementUtility
{
    /**
     * @param $fields
     * @param $type
     * @return bool
     */
    public static function areAFlexFormOnlyInCEFields($fields, $type)
    {
        if (!empty($fields)) {
            $fieldsToArray = GeneralUtility::makeInstance(GeneralCreateCommandUtility::class)->fieldsToArray($fields);
            $result = false;
            foreach ($fieldsToArray as $field) {
                $fieldType = explode(',', $field)[1];

                if ('pi_flexform' === $fieldType && $type === 'contentElementField') {
                    $result = true;
                }
                if ('pi_flexform' === $fieldType && $type === 'inlineRelationField') {
//                    You can not use flexForm and tx_contentelementregistry_relations in inline relation
                    throw new InvalidArgumentException('Field ' . $fieldType . ' you can use only as content element field.');
                }
            }
            return $result;
        }
    }

    /**
     * @param $fields
     * @param $type
     * @return bool
     */
    public static function areTxContentelementregistryRelationsOnlyInCEFields($fields, $type)
    {
        if (!empty($fields)) {
            $fieldsToArray = GeneralUtility::makeInstance(GeneralCreateCommandUtility::class)->fieldsToArray($fields);
            foreach ($fieldsToArray as $field) {
                $fieldType = explode(',', $field)[1];

                if ('tx_contentelementregistry_relations' === $fieldType && $type === 'inlineRelationField') {
//                    You can not use flexForm and tx_contentelementregistry_relations in inline relation
                    throw new InvalidArgumentException('Field ' . $fieldType . ' you can use only as content element field.');
                }
            }
        }
    }
}
