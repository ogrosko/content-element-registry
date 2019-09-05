<?php
namespace Digitalwerk\ContentElementRegistry\Domain\Model;

use Digitalwerk\ContentElementRegistry\Utility\ContentElementRegistryUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

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
     * @var int
     */
    protected $headerLayout = 0;

    /**
     * @var string
     */
    protected $headerPosition = '';

    /**
     * @var string
     */
    protected $headerLink = '';

    /**
     * @var string
     */
    protected $subheader = '';

    /**
     * @var string
     */
    protected $cssClass = '';

    /**
     * @var string
     */
    protected $cssClassPrefix = 'ce-';

    /**
     * @var array
     */
    protected $cssClassModifiers = [];

    /**
     * @var ContentObjectRenderer
     */
    protected $cObj = null;

    /**
     * @var array
     */
    protected $contentObjectConfiguration = [];

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
     * @return int
     */
    public function getHeaderLayout(): int
    {
        return $this->headerLayout;
    }

    /**
     * @return string
     */
    public function getHeaderPosition(): string
    {
        return $this->headerPosition;
    }

    /**
     * @return string
     */
    public function getHeaderLink(): string
    {
        return $this->headerLink;
    }

    /**
     * @return string
     */
    public function getSubheader(): string
    {
        return $this->subheader;
    }

    /**
     * Get Css class
     *
     * @return string
     * @throws \ReflectionException
     */
    public function getCssClass(): string
    {
        $cssClass = $this->cssClass ?: ContentElementRegistryUtility::camelCase2Dashed($this::getModelName());
        return \sprintf("%s%s", $this->cssClassPrefix, $cssClass);
    }

    /**
     * Set css class modifiers
     * @return void
     */
    public function initializeCssClassModifiers(): void
    {
    }

    /**
     * Get css class with modifiers string
     * @return string
     * @throws \ReflectionException
     */
    public function getCssClassWithModifiers(): string
    {
        $cssClass = $this->getCssClass();
        $this->initializeCssClassModifiers();
        $prefix = $this->getCssClass() . '--';

        if (!empty($this->cssClassModifiers)) {
            $cssClass .= ' ' . $prefix . implode(" {$prefix}", $this->cssClassModifiers);
        }

        return \trim($cssClass);
    }

    /**
     * @return int
     */
    public function getLanguageUid()
    {
        return $this->_languageUid;
    }

    /**
     * @return ContentObjectRenderer
     */
    public function getCObj(): ? ContentObjectRenderer
    {
        return $this->cObj;
    }

    /**
     * @param ContentObjectRenderer $cObj
     */
    public function setCObj(ContentObjectRenderer $cObj)
    {
        $this->cObj = $cObj;
    }

    /**
     * @return array
     */
    public function getContentObjectConfiguration(): array
    {
        return $this->contentObjectConfiguration;
    }

    /**
     * @param array $contentObjectConfiguration
     */
    public function setContentObjectConfiguration(array $contentObjectConfiguration)
    {
        $this->contentObjectConfiguration = $contentObjectConfiguration;
    }
}
