<?php
namespace Digitalwerk\ContentElementRegistry\DataProcessing;

use Digitalwerk\ContentElementRegistry\Domain\Model\ContentElement;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

/**
 * Class ContentElementObjectDataProcessor
 * @package Digitalwerk\ContentElementRegistry\DataProcessing
 */
class HeadlessDataProcessor implements DataProcessorInterface
{

    /**
     * Process content object data
     *
     * @param ContentObjectRenderer $cObj The data of the content element or page
     * @param array $contentObjectConfiguration The configuration of Content Object
     * @param array $processorConfiguration The configuration of this processor
     * @param array $processedData Key/value store of processed data (e.g. to be passed to a Fluid View)
     * @return array the processed data as key/value store
     */
    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ) {
        /** @var ContentElement $contentElement */
        $contentElement = $this->getDataMapper()->map(
            ContentElement::class,
            [$cObj->data]
        )[0];

        $contentElement->setCObj($cObj);
        $contentElement->setContentObjectConfiguration($contentObjectConfiguration);

        $targetVariableName = $cObj->stdWrapValue('as', $processorConfiguration) ?: 'fields';

        $processedData[$targetVariableName] = $contentElement->jsonSerialize();

        return $processedData;
    }

    /**
     * @return DataMapper
     */
    protected function getDataMapper()
    {
        return GeneralUtility::makeInstance(ObjectManager::class)->get(DataMapper::class);
    }
}
