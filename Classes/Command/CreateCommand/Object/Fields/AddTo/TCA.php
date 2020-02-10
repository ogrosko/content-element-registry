<?php
namespace Digitalwerk\ContentElementRegistry\Command\CreateCommand\Object\Fields\AddTo;

use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Object\Fields;
use Digitalwerk\ContentElementRegistry\Utility\CreateCommand\FlexFormUtility;
use Digitalwerk\ContentElementRegistry\Utility\CreateCommand\TCAUtility;
use InvalidArgumentException;

/**
 * Class TCA
 * @package Digitalwerk\ContentElementRegistry\Command\CreateCommand\Object\Fields\AddTo
 */
class TCA
{
    /**
     * @param $table
     * @param $contentElementName
     * @param $secondDesignation
     * @param Fields $fields
     * @param $extraSpaces
     * @param $extensionName
     * @param $extraSpaces2
     * Return fields formatted like in TCA (format string)
     * @return string
     */
    public function column($table ,$contentElementName, $secondDesignation, Fields $fields, $extraSpaces, $extensionName, $extraSpaces2)
    {
        $result = [];

        foreach ($fields->getFields() as $field) {
            $fieldName = $field->getName();
            $fieldType = $field->getType();
            $fieldItems = $field->getItems();

            if ($field->exist()) {
                $fieldConfig = $field->getConfig();
                if (null !== $fieldConfig) {
                    $result[] = (new TCAUtility)->generateFieldInTCA($fieldName, $secondDesignation, $table, $contentElementName, $fieldConfig, $extraSpaces, $extensionName);
                }

                if ($field->isFlexFormItemsAllowed()) {
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
