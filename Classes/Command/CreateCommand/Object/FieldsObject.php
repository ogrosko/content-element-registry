<?php
namespace Digitalwerk\ContentElementRegistry\Command\CreateCommand\Object;

use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class FieldsObject
 * @package Digitalwerk\ContentElementRegistry\Command\CreateCommand\Object
 */
class FieldsObject
{
    /**
     * @var ObjectStorage<\Digitalwerk\ContentElementRegistry\Command\CreateCommand\Object\Fields\FieldObject>
     */
    protected $fields = null;

    /**
     * @var bool
     */
    protected $areDefault = false;

    /**
     * @return ObjectStorage
     */
    public function getFields(): ObjectStorage
    {
        return $this->fields;
    }

    /**
     * @param ObjectStorage $fields
     */
    public function setFields(ObjectStorage $fields): void
    {
        $this->fields = $fields;
    }

    /**
     * @return bool
     */
    public function areDefault(): bool
    {
        return $this->areDefault;
    }

    /**
     * @param bool $areDefault
     */
    public function setAreDefault(bool $areDefault)
    {
        $this->areDefault = $areDefault;
    }
}
