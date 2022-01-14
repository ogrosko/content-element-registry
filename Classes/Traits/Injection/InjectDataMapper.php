<?php
namespace Digitalwerk\ContentElementRegistry\Traits\Injection;

use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;

/**
 * Trait DataMapper
 * @package Digitalwerk\ContentElementRegistry\Traits\Injection
 */
trait InjectDataMapper
{
    /**
     * @var DataMapper
     */
    public $dataMapper = null;

    /**
     * @param DataMapper $dataMapper
     * @return void
     */
    public function injectDataMapper(DataMapper $dataMapper)
    {
        $this->dataMapper = $dataMapper;
    }
}
