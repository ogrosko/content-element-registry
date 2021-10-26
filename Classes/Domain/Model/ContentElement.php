<?php
namespace Digitalwerk\ContentElementRegistry\Domain\Model;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Service\FlexFormService;

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
    public function getLanguageUid()
    {
        return $this->_languageUid;
    }

    /**
     * @return FlexFormService
     */
    protected function getFlexFormService()
    {
        return GeneralUtility::makeInstance(FlexFormService::class);
    }
}
