<?php
namespace Digitalwerk\ContentElementRegistry\Command\CreateCommand\Object\Fields\AddTo;

use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Config\TCAFieldTypes;
use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Object\Fields;
use Digitalwerk\ContentElementRegistry\Utility\CreateCommand\TranslationUtility;
use InvalidArgumentException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ContentElementClass
 * @package Digitalwerk\ContentElementRegistry\Command\CreateCommand\Object\Fields\AddTo
 */
class ContentElementClass
{
    /**
     * @param Fields $fields
     * @param $name
     * @param $extraSpaces
     * @return string
     */
    public function toMapping(Fields $fields, $name, $extraSpaces)
    {
        $createdFields = [];

        foreach ($fields->getFields() as $field) {
            if ($field->getName() === $field->getType() && $field->isDefault()) {
                //Default fields (no action)
            } elseif ($field->getName() !== $field->getType() && $field->isDefault()) {
                $createdFields[] = '"' . $field->getType() . '" => "' . str_replace(' ','',lcfirst(ucwords(str_replace('_',' ', $field->getName())))) . '"';
            } elseif ($field->exist()) {
                $createdFields[] = '"' .  strtolower($name) . '_' . $field->getName() . '" => "' . str_replace(' ','',lcfirst(ucwords(str_replace('_',' ', $field->getName())))) . '"';
            } else {
                throw new InvalidArgumentException('Field "' . $field->getType() . '" does not exist.6');
            }
        }

        return implode(",\n" . $extraSpaces, $createdFields);
    }

    /**
     * @param Fields $fields
     * @param $name
     * @param $table
     * @param $extraSpace
     * @return string|string[]|null
     */
    public function toPalette(Fields $fields, $name, $extraSpace)
    {
        $createdFields = [];

        foreach ($fields->getFields() as $field) {
            if ($field->isDefault()) {
                $createdFields[] = '--linebreak--, ' . $field->getType();
            } elseif (!$field->isDefault()) {
                $createdFields[] = '--linebreak--, ' . strtolower($name) . '_' . $field->getName();
            } else {
//                    Fieldtype does not exist
                throw new InvalidArgumentException('Field "' . $field->getType() . '" does not exist.1');
            }
        }
        return preg_replace('/--linebreak--, /', '', implode(",\n" . $extraSpace, $createdFields),1);
    }

    /**
     * @param $table
     * @param $contentElementName
     * @param $secondDesignation
     * @param Fields $fields
     * @param string $extraSpaces
     * @return string
     */
    public function toColumnsOverrides($table, $contentElementName, $secondDesignation, Fields $fields)
    {
        $defaultFieldsWithAnotherTitle = [];
        $TCAFieldTypes = GeneralUtility::makeInstance(TCAFieldTypes::class)->getTCAFieldTypes($table);

        foreach ($fields->getFields() as $field) {
            $fieldName = $field->getName();
            $fieldType = $field->getType();
            $fieldTitle = $field->getTitle();
            if ($fieldTitle !== $field->getDefaultTitle() && $field->isDefault())
            {
                if ($TCAFieldTypes[$table][$fieldType]['inlineFieldsAllowed']) {
                    $fieldItemName = $field->getFirstItem()->getName();

                    $defaultFieldsWithAnotherTitle[] =
            '\''.$fieldType.'\' => [
                \'label\' => \'LLL:EXT:dw_boilerplate/Resources/Private/Language/locallang_db.xlf:' . $table . '.dwboilerplate_'.strtolower($contentElementName).'.'. strtolower($secondDesignation).'_'. strtolower($fieldName).'\',
                \'config\' => [
                    \'overrideChildTca\' => [
                        \'columns\' => [
                            \'type\' => [
                                \'config\' => [
                                    \'items\' => [
                                        [\'LLL:EXT:dw_boilerplate/Resources/Private/Language/locallang_db.xlf:tt_content.dwboilerplate_'.strtolower($contentElementName).'_'.strtolower($fieldItemName).'\', self::CONTENT_RELATION_'.strtoupper($fieldItemName).'],
                                    ],
                                    \'default\' => self::CONTENT_RELATION_'.strtoupper($fieldItemName).'
                                ],
                            ],
                        ],
                    ],
                ],
            ],';
                    TranslationUtility::addStringToTranslation('public/typo3conf/ext/dw_boilerplate/Resources/Private/Language/locallang_db.xlf','tt_content.dwboilerplate_'.strtolower($contentElementName).'_'.strtolower($fieldItemName),str_replace('-', ' ', $field->getFirstItem()->getTitle()));
                } else {
                    $defaultFieldsWithAnotherTitle[] =
            '\''.$fieldType.'\' => [
                \'label\' => \'LLL:EXT:dw_boilerplate/Resources/Private/Language/locallang_db.xlf:' . $table . '.dwboilerplate_'.strtolower($contentElementName).'.'. strtolower($secondDesignation).'_'. strtolower($fieldName).'\',
            ],';
                }

            }
        }

        return implode("\n" . '            ', $defaultFieldsWithAnotherTitle);
    }
}
