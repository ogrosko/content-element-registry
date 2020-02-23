<?php
namespace Digitalwerk\ContentElementRegistry\Command\CreateCommand\Setup;

use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Run;
use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Setup\Fields\InlineSetup;
use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Setup\Fields\ItemsSetup;

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

        $fieldTypes = $this->run->getFieldTypes();
        if ($fieldTypes[$fieldType]['TCAItemsAllowed']) {
            Run::setDeepLevel(Run::getRawDeepLevel() . Run::DEEP_LEVEL_SPACES);
            $this->run->getOutput()->writeln(Run::getColoredDeepLevel() . 'Create at least one item.');
            $itemsSetup = new ItemsSetup($this->run);
            $itemsSetup->createItem();
            $field = $fieldName . ',' . $fieldType . ',' . $fieldTitle . ',' . $itemsSetup->getItems() . '/';
        } elseif ($fieldTypes[$fieldType]['inlineFieldsAllowed']) {
            Run::setDeepLevel(Run::getRawDeepLevel() . Run::DEEP_LEVEL_SPACES);
            $this->run->getOutput()->writeln(Run::getColoredDeepLevel() . 'Configure inline field.');
            $inlineSetup = new InlineSetup($this->run);
            $inlineSetup->createInlineItem();
            $field = $fieldName . ',' . $fieldType . ',' . $fieldTitle . ',' . $inlineSetup->getInlineItem() . '/';
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
}
