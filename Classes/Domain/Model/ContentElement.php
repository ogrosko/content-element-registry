<?php
namespace Digitalwerk\ContentElementRegistry\Domain\Model;

use Digitalwerk\ContentElementRegistry\Utility\ContentElementRegistryUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class ContentElement
 * @package Digitalwerk\CEs\Domain\Model
 */
class ContentElement extends AbstractEntity
{

    /**
     * @var string
     */
    protected $CType = '';

    /**
     * @var bool
     */
    protected $sectionIndex = true;

    /**
     * @var string
     */
    protected $layout = 'Default';

    /**
     * @var string
     */
    protected $header = '';

    /**
     * @var string
     */
    protected $cssClass = '';

    /**
     * @var string
     */
    protected $cssClassPrefix = 'ce-';

    /**
     * Get Model name
     *
     * @return string
     * @throws \ReflectionException
     */
    public static function getModelName(): string
    {
        return (new \ReflectionClass(static::class))->getShortName();
    }

    /**
     * @return string
     */
    public function getCType(): string
    {
        return $this->CType;
    }

    /**
     * @return string
     */
    public function getLayout(): string
    {
        return $this->layout == '0' ? 'Default' : $this->layout;
    }

    /**
     * @return bool
     */
    public function isSectionIndex(): bool
    {
        return $this->sectionIndex;
    }

    /**
     * @return string
     */
    public function getHeader(): string
    {
        return $this->header;
    }

    /**
     * @return string
     * @throws \ReflectionException
     */
    public function getCssClass(): string
    {
        $cssClass = $this->cssClass ?: ContentElementRegistryUtility::camelCase2Dashed($this::getModelName());
        return \sprintf("%s%s", $this->cssClassPrefix, $cssClass);
    }

    /**
     * @return int
     */
    public function getLanguageUid()
    {
        return $this->_languageUid;
    }
}
