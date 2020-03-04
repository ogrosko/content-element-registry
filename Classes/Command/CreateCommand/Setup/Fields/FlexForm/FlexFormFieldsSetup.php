<?php
namespace Digitalwerk\ContentElementRegistry\Command\CreateCommand\Setup\Fields\FlexForm;

use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Run;

/**
 * Class FlexFormFieldsSetup
 * @package Digitalwerk\ContentElementRegistry\Command\CreateCommand\Setup\Fields\FlexForm
 */
class FlexFormFieldsSetup
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

        $field = $fieldName . ',' . $fieldType . ',' . $fieldTitle . '/';

        $this->setFields($this->getFields() . $field);

        if ($this->run->needCreateMoreFields()) {
            $this->createField();
        } else {
            Run::setDeepLevel(substr(Run::getRawDeepLevel(), 0, -strlen(Run::DEEP_LEVEL_SPACES)));
        }
    }
}
