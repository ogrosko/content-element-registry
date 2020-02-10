<?php
namespace Digitalwerk\ContentElementRegistry\Command\CreateCommand\Object\Fields;

use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Object\Fields\AddTo\ContentElementClass;
use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Object\Fields\AddTo\Model;
use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Object\Fields\AddTo\TCA;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class AddTo
 * @package Digitalwerk\ContentElementRegistry\Command\CreateCommand\Object\Fields
 */
class AddTo
{
    /**
     * @return AddTo\ContentElementClass
     */
    public function contentElementClass(): AddTo\ContentElementClass
    {
        return GeneralUtility::makeInstance(ContentElementClass::class);
    }

    /**
     * @return Model
     */
    public function model(): Model
    {
        return GeneralUtility::makeInstance(Model::class);
    }

    /**
     * @return AddTo\TCA
     */
    public function TCA(): AddTo\TCA
    {
        return GeneralUtility::makeInstance(TCA::class);
    }
}
