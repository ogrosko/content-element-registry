<?php
namespace Digitalwerk\ContentElementRegistry\Command\CreateCommand\Setup\Fields;

use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Config\Typo3FieldTypes;
use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Run;
use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Setup\FieldsSetup;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class InlineSetup
 * @package Digitalwerk\ContentElementRegistry\Command\CreateCommand\Setup\Fields
 */
class InlineSetup
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
    protected $inlineItem = '';

    /**
     * @return string
     */
    public function getInlineItem(): string
    {
        return $this->inlineItem;
    }

    /**
     * @param string $inlineItem
     */
    public function setInlineItem(string $inlineItem): void
    {
        $this->inlineItem = $inlineItem;
    }

    /**
     * @return string
     */
    public function createInlineItem()
    {
        $inlineName = $this->run->askInlineClassName();

        $inlineKeysOfInlineFields = Run::getArrayKeyOfInlineFields();
        Run::setArrayKeyOfInlineFields(Run::getArrayKeyOfInlineFields() + 1);

        $inlineTitle = $this->run->askInlineTitle();

        $this->setInlineItem($inlineName . ';' . $inlineKeysOfInlineFields . ';' . $inlineTitle . '*');
        $this->run->getOutput()->writeln(Run::getColoredDeepLevel() . 'Create at least one inline field.');

        $table = $this->run->getInlineTable();
        $editedRunSetup = $this->run;
        $editedRunSetup->setFieldTypes(
            GeneralUtility::makeInstance(Typo3FieldTypes::class)->getTCAFieldTypes($table)[$table]
        );
        $newInlineFields = new FieldsSetup($editedRunSetup);
        $newInlineFields->createField();
        $inlineFields = Run::getInlineFields() + [$inlineKeysOfInlineFields => $newInlineFields->getFields()];

        Run::setInlineFields(
            $inlineFields
        );
    }
}
