<?php

declare(strict_types=1);

namespace Digitalwerk\ContentElementRegistry\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class Relation
 */
abstract class Relation extends AbstractEntity
{
    /**
     * @var string
     */
    protected $type = '';

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     */
    protected $media;

    /**
     * @var \Digitalwerk\ContentElementRegistry\Domain\Model\ContentElement
     */
    protected $contentElement;

    /**
     * Relation constructor.
     */
    public function __construct()
    {
        $this->media = new ObjectStorage();
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return ObjectStorage
     */
    public function getMedia(): ObjectStorage
    {
        return $this->media;
    }

    /**
     * @return ContentElement
     */
    public function getContentElement(): ContentElement
    {
        return $this->contentElement;
    }
}
