<?php
namespace Digitalwerk\ContentElementRegistry\Command\CreateCommand\Object;

use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Object\Fields\AddTo;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class Fields
 * @package Digitalwerk\ContentElementRegistry\Command\CreateCommand\Object
 */
class Fields
{
    /**
     * @var ObjectStorage<\Digitalwerk\ContentElementRegistry\Command\CreateCommand\Object\Fields\Field>
     */
    protected $fields = null;

    /**
     * @return AddTo|null
     */
    public function addTo(): ? Fields\AddTo
    {
        return GeneralUtility::makeInstance(AddTo::class);
    }

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
}
