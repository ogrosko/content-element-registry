<?php
namespace Digitalwerk\ContentElementRegistry\Command\CreateCommand\Setup\Fields;

use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Config\FlexFormFieldTypes;
use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Run;
use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Setup\Fields\FlexForm\FlexFormFieldsSetup;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class FlexForm
 * @package Digitalwerk\ContentElementRegistry\Command\CreateCommand\Setup\Fields
 */
class FlexForm
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
    protected $flexFormItem = '';

    /**
     * @return string
     */
    public function getFlexFormItem(): string
    {
        return $this->flexFormItem;
    }

    /**
     * @param string $flexFormItem
     */
    public function setFlexFormItem(string $flexFormItem): void
    {
        $this->flexFormItem = $flexFormItem;
    }

    /**
     * @return string
     */
    public function createFlexForm()
    {
        $flexFormName = 'NoDefined';

        $inlineKeysOfInlineFields = Run::getArrayKeyOfInlineFields();
        Run::setArrayKeyOfInlineFields(Run::getArrayKeyOfInlineFields() + 1);

        $flexFormTitle = 'NoDefined';

        $this->setFlexFormItem($flexFormName . ';' . $inlineKeysOfInlineFields . ';' . $flexFormTitle . '*');
        $this->run->getOutput()->writeln(Run::getColoredDeepLevel() . 'Create at least one flexForm field.');

        $editedRunSetup = $this->run;
        $editedRunSetup->setFieldTypes(
            GeneralUtility::makeInstance(FlexFormFieldTypes::class)->getFlexFormFieldTypes()
        );
        $newInlineFields = new FlexFormFieldsSetup($editedRunSetup);
        $newInlineFields->createField();
        $inlineFields = Run::getInlineFields() + [$inlineKeysOfInlineFields => $newInlineFields->getFields()];

        Run::setInlineFields(
            $inlineFields
        );
    }
}
