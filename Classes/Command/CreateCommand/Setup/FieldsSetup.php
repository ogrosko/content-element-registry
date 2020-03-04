<?php
namespace Digitalwerk\ContentElementRegistry\Command\CreateCommand\Setup;

use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Config\Typo3FieldTypes;
use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Run;
use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Setup\Fields\FlexForm;
use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Setup\Fields\InlineSetup;
use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Setup\Fields\ItemsSetup;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class FieldsSetup
 * @package Digitalwerk\ContentElementRegistry\Command\CreateCommand\Setup
 */
class FieldsSetup
{
    /**
     * @var Run
     */
    protected $run = null;

    /**
     * FieldsSetup constructor.
     * @param Run $run
     */
    public function __construct(Run $run)
    {
        $this->run = $run;
    }

    /**
     * @var string
     */
    protected $fields = '';

    /**
     * @return string
     */
    public function getFields(): string
    {
        return $this->fields;
    }

    /**
     * @param string $fields
     */
    public function setFields(string $fields): void
    {
        $this->fields = $fields;
    }

    public function createField()
    {
        $fieldName = $this->run->askFieldName();
        $fieldType = $this->run->askFieldType();
        $fieldTitle = $this->run->askFieldTitle();

        if (strlen(Run::getRawDeepLevel()) - strlen(Run::DEEP_LEVEL_SPACES) === strlen(Run::DEEP_LEVEL_SPACES)) {
            $table = $this->run->getTable();
            $fieldTypes = GeneralUtility::makeInstance(Typo3FieldTypes::class)->getTCAFieldTypes($table)[$table];
            $this->run->setFieldTypes($fieldTypes);
        } else {
            $fieldTypes = $this->run->getFieldTypes();
        }

        if ($fieldTypes[$fieldType]['TCAItemsAllowed']) {
            $this->goDeepLevel();
            $this->run->getOutput()->writeln(Run::getColoredDeepLevel() . 'Create at least one item.');
            $itemsSetup = new ItemsSetup($this->run);
            $itemsSetup->createItem();
            $field = $fieldName . ',' . $fieldType . ',' . $fieldTitle . ',' . $itemsSetup->getItems() . '/';
        } elseif ($fieldTypes[$fieldType]['inlineFieldsAllowed']) {
            $this->goDeepLevel();
            $this->run->getOutput()->writeln(Run::getColoredDeepLevel() . 'Configure inline field.');
            $inlineSetup = new InlineSetup($this->run);
            $inlineSetup->createInlineItem();
            $field = $fieldName . ',' . $fieldType . ',' . $fieldTitle . ',' . $inlineSetup->getInlineItem() . '/';
        } elseif ($fieldTypes[$fieldType]['FlexFormItemsAllowed']) {
            $this->goDeepLevel();
            $this->run->getOutput()->writeln(Run::getColoredDeepLevel() . 'Configure flexForm field.');
            $flexFormSetup = new FlexForm($this->run);
            $flexFormSetup->createFlexForm();
            $field = $fieldName . ',' . $fieldType . ',' . $fieldTitle . ',' . $flexFormSetup->getFlexFormItem() . '/';
        } else {
            $field = $fieldName . ',' . $fieldType . ',' . $fieldTitle . '/';
        }

        $this->setFields($this->getFields() . $field);
        if ($this->run->needCreateMoreFields()) {
            $this->createField();
        } else {
            Run::setDeepLevel(substr(Run::getRawDeepLevel(), 0, -strlen(Run::DEEP_LEVEL_SPACES)));
        }
    }


    public function goDeepLevel() {
        Run::setDeepLevel(Run::getRawDeepLevel() . Run::DEEP_LEVEL_SPACES);
    }
}
