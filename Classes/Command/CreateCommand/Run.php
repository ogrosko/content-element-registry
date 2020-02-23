<?php
namespace Digitalwerk\ContentElementRegistry\Command\CreateCommand;

use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Config\Typo3FieldTypes;
use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Setup\FieldsSetup;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Run
 * @package Digitalwerk\ContentElementRegistry\Command\CreateCommand
 */
class Run extends Command
{
    const CONTENT_ELEMENT = 'Content element';
    const PAGE_TYPE = 'Page Type';
    const PLUGIN = 'Plugin';
    const DEEP_LEVEL_SPACES = ">>>";

    const YES_SHORTCUT = 'y';
    const NO_SHORTCUT = 'n';
    const YES = 'Yes';
    const NO = 'No';

    /**
     * @var OutputInterface
     */
    protected $output = null;

    /**
     * @var InputInterface
     */
    protected $input = null;

    /**
     * @var string
     */
    public static $deepLevel = self::DEEP_LEVEL_SPACES;

    /**
     * @var mixed
     */
    protected $questionHelper = null;

    /**
     * @var array
     */
    public static $inlineFields = [];

    /**
     * @var array
     */
    protected $fieldTypes = [];

    /**
     * @var string
     */
    protected $table = '';

    /**
     * @var string
     */
    protected $inlineTable = 'tx_contentelementregistry_domain_model_relation';

    /**
     * @var int
     */
    public static $arrayKeyOfInlineFields = 0;

    /**
     * @return string
     */
    public static function getColoredDeepLevel(): string
    {
        return '<bg=red;options=bold>' . self::getRawDeepLevel() .  '</>';
    }

    /**
     * @return string
     */
    public static function getRawDeepLevel(): string
    {
        return self::$deepLevel;
    }

    /**
     * @param string $deepLevel
     */
    public static function setDeepLevel(string $deepLevel): void
    {
        self::$deepLevel = $deepLevel;
    }

    /**
     * @return mixed
     */
    public function getQuestionHelper()
    {
        return $this->questionHelper;
    }

    /**
     * @return array
     */
    public function getFieldTypes(): array
    {
        return $this->fieldTypes;
    }

    /**
     * @param array $fieldTypes
     */
    public function setFieldTypes(array $fieldTypes): void
    {
        $this->fieldTypes = $fieldTypes;
    }

    /**
     * @return string
     */
    public function getInlineTable(): string
    {
        return $this->inlineTable;
    }

    /**
     * @param mixed $questionHelper
     */
    public function setQuestionHelper($questionHelper): void
    {
        $this->questionHelper = $questionHelper;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @param string $table
     */
    public function setTable(string $table): void
    {
        $this->table = $table;
    }

    /**
     * @return InputInterface
     */
    public function getInput(): InputInterface
    {
        return $this->input;
    }

    /**
     * @param InputInterface $input
     */
    public function setInput(InputInterface $input): void
    {
        $this->input = $input;
    }

    /**
     * @return OutputInterface
     */
    public function getOutput(): OutputInterface
    {
        return $this->output;
    }

    /**
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    /**
     * @return int
     */
    public static function getArrayKeyOfInlineFields(): int
    {
        return self::$arrayKeyOfInlineFields;
    }

    /**
     * @param int $arrayKeyOfInlineFields
     */
    public static function setArrayKeyOfInlineFields(int $arrayKeyOfInlineFields): void
    {
        self::$arrayKeyOfInlineFields = $arrayKeyOfInlineFields;
    }

    /**
     * @return array
     */
    public static function getInlineFields(): array
    {
        return self::$inlineFields;
    }

    /**
     * @param array $inlineFields
     */
    public static function setInlineFields(array $inlineFields): void
    {
        self::$inlineFields = $inlineFields;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Welcome in content element registry');

        $this->setQuestionHelper(
            $this->getHelper('question')
        );
        $this->setInput($input);
        $this->setOutput($output);
        $helper = $this->getQuestionHelper();

        $question = new ChoiceQuestion(
            'What do you want to create?',
            [self::CONTENT_ELEMENT,self::PAGE_TYPE, self::PLUGIN]
        );

        if ($helper->ask($input, $output, $question) === self::CONTENT_ELEMENT) {
            $question = new Question('Content element name (etc. NewContentElement), without spaces: ');
            $name = $helper->ask($input, $output, $question);
            $question = new Question('Content element title (etc. New-Content-Element), space is - : ');
            $title = $helper->ask($input, $output, $question);
            $question = new Question('Content element description (etc. New-Content-Element-description), space is - : ');
            $description = $helper->ask($input, $output, $question);
            $question = new Question('Enter table of content element :');

            $this->setTable($helper->ask($input, $output, $question));
            $table = $this->getTable();

            $this->addArgument('name');
            $this->addArgument('title');
            $this->addArgument('description');
            $this->addArgument('fields');
            $this->addArgument('inline-fields');

            $input->setArgument(
                'name',
                $name
            );
            $input->setArgument(
                'title',
                $title
            );
            $input->setArgument(
                'description',
                $description
            );
            $question = new ChoiceQuestion(
                'Do you want to create some fields?',
                [self::YES_SHORTCUT => self::YES, self::NO_SHORTCUT => self::NO]
            );

            if ($helper->ask($input, $output, $question) === self::YES_SHORTCUT) {
                $this->setFieldTypes(
                    GeneralUtility::makeInstance(Typo3FieldTypes::class)->getTCAFieldTypes($table)[$table]
                );
                $fieldsSetup = new FieldsSetup($this);
                $fieldsSetup->createField();
                $input->setArgument(
                    'fields',
                    $fieldsSetup->getFields()
                );
                $input->setArgument(
                    'inline-fields',
                    self::getInlineFields()
                );

            } else {
                $input->setArgument(
                    'fields',
                    '-'
                );
            }
            GeneralUtility::makeInstance(ContentElement::class)->execute($input, $output);
        }
    }

    /**
     * @return mixed
     */
    public function askFieldName()
    {
        $question = new Question(self::getColoredDeepLevel() . 'Field name (etc. new_field): ');
        return $this->getQuestionHelper()->ask(
            $this->getInput(),
            $this->getOutput(),
            $question
        );
    }

    /**
     * @return mixed
     */
    public function askFieldType()
    {
        $question = new ChoiceQuestion(
            self::getColoredDeepLevel() . 'Field type:',
            array_keys(
                $this->getFieldTypes()
            )
        );
        return $this->getQuestionHelper()->ask(
            $this->getInput(),
            $this->getOutput(),
            $question
        );
    }

    /**
     * @return mixed
     */
    public function askFieldTitle()
    {
        $question = new Question(self::getColoredDeepLevel() . 'Field title (etc. New-Field): ');
        return $this->getQuestionHelper()->ask(
            $this->getInput(),
            $this->getOutput(),
            $question
        );
    }

    /**
     * @return mixed
     */
    public function askItemName()
    {
        $question = new Question(self::getColoredDeepLevel() . 'Item name (etc. item): ');
        return $this->getQuestionHelper()->ask(
            $this->getInput(),
            $this->getOutput(),
            $question
        );
    }

    /**
     * @return mixed
     */
    public function askItemValue()
    {
        $question = new Question(self::getColoredDeepLevel() . 'Item value (etc. 0 or some string): ');
        return $this->getQuestionHelper()->ask(
            $this->getInput(),
            $this->getOutput(),
            $question
        );
    }

    /**
     * @return mixed
     */
    public function askItemTitle()
    {
        $question = new Question(self::getColoredDeepLevel() . 'Item title (etc. New-Item): ');
        return $this->getQuestionHelper()->ask(
            $this->getInput(),
            $this->getOutput(),
            $question
        );
    }

    /**
     * @return mixed
     */
    public function askInlineClassName()
    {
        $question = new Question(self::getColoredDeepLevel() . 'Inline Class name (etc. Inline): ');
        return $this->getQuestionHelper()->ask(
            $this->getInput(),
            $this->getOutput(),
            $question
        );
    }

    /**
     * @return mixed
     */
    public function askInlineTitle()
    {
        $question = new Question(self::getColoredDeepLevel() . 'Inline title (etc. Inline-title): ');
        return $this->getQuestionHelper()->ask(
            $this->getInput(),
            $this->getOutput(),
            $question
        );
    }

    /**
     * @return bool
     */
    public function needCreateMoreFields()
    {
        $question = new ChoiceQuestion(
            self::getColoredDeepLevel() . 'Do you want to create more fields?',
            [self::YES_SHORTCUT => self::YES, self::NO_SHORTCUT => self::NO]
        );

        return $this->getQuestionHelper()
                ->ask($this->getInput(), $this->getOutput(), $question) === self::YES_SHORTCUT;
    }

    /**
     * @return bool
     */
    public function needCreateMoreItems()
    {
        $question = new ChoiceQuestion(
            self::getColoredDeepLevel() . 'Do you want to create more items?',
            [self::YES_SHORTCUT => self::YES, self::NO_SHORTCUT => self::NO]
        );

        return $this->getQuestionHelper()
                ->ask($this->getInput(), $this->getOutput(), $question) === self::YES_SHORTCUT;
    }
}
