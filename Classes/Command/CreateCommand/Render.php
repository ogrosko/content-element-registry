<?php
namespace Digitalwerk\ContentElementRegistry\Command\CreateCommand;

use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Object\FieldsObject;
use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Render\ContentElementClass;
use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Render\Icon;
use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Render\Inline;
use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Render\Model;
use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Render\Register;
use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Render\TCA;
use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Render\PreviewImage;
use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Render\SQLDatabase;
use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Render\Template;
use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Render\Translation;
use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Render\TypoScript;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Render
 * @package Digitalwerk\ContentElementRegistry\Command\CreateCommand
 */
class Render
{
    /**
     * @var \Digitalwerk\ContentElementRegistry\Command\CreateCommand\Object\FieldsObject
     */
    protected $fields = null;

    /**
     * @var null
     */
    protected $optionalClass = null;

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
    protected $staticName = '';

    /**
     * @var string
     */
    protected $table = '';

    /**
     * @var string
     */
    protected $relativePathToClass = '';

    /**
     * @var string
     */
    protected $extensionName = '';

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var int
     */
    protected $deepLevel = 0;

    /**
     * @var string
     */
    protected $inlineRelativePath = '';

    /**
     * @var array
     */
    protected $inlineFields = [];

    /**
     * @var string
     */
    protected $extraSpaces = '';

    /**
     * @var string
     */
    protected $secondDesignation = '';

    /**
     * @var string
     */
    protected $betweenProtectedsAndGetters = '';

    /**
     * @var string
     */
    protected $modelNamespace = '';

    /**
     * @var int
     */
    protected $doktype = 0;

    /**
     * @return FieldsObject
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param FieldsObject $fields
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    /**
     * @return string
     */
    public function getRelativePathToClass(): string
    {
        return $this->relativePathToClass;
    }

    /**
     * @param string $relativePathToClass
     */
    public function setRelativePathToClass(string $relativePathToClass): void
    {
        $this->relativePathToClass = $relativePathToClass;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getStaticName(): string
    {
        return $this->staticName;
    }

    /**
     * @param string $staticName
     */
    public function setStaticName(string $staticName)
    {
        $this->staticName = $staticName;
    }

    /**
     * @return string
     */
    public function getExtraSpaces()
    {
        return $this->extraSpaces;
    }

    /**
     * @param string $extraSpaces
     */
    public function setExtraSpaces($extraSpaces)
    {
        $this->extraSpaces = $extraSpaces;
    }

    /**
     * @return string
     */
    public function getExtensionName()
    {
        return $this->extensionName;
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param string $table
     */
    public function setTable($table)
    {
        $this->table = $table;
    }

    /**
     * @param string $extensionName
     */
    public function setExtensionName($extensionName)
    {
        $this->extensionName = $extensionName;
    }

    /**
     * @return string
     */
    public function getSecondDesignation()
    {
        return $this->secondDesignation;
    }

    /**
     * @return int|null
     */
    public function getDoktype(): ? int
    {
        return $this->doktype;
    }

    /**
     * @param int|null $doktype
     */
    public function setDoktype(? int $doktype): void
    {
        $this->doktype = $doktype;
    }

    /**
     * @param string $secondDesignation
     */
    public function setSecondDesignation($secondDesignation)
    {
        $this->secondDesignation = $secondDesignation;
    }

    /**
     * @return string
     */
    public function getInlineRelativePath()
    {
        return $this->inlineRelativePath;
    }

    /**
     * @return string
     */
    public function getModelNamespace(): string
    {
        return $this->modelNamespace;
    }

    /**
     * @param string $modelNamespace
     */
    public function setModelNamespace(string $modelNamespace)
    {
        $this->modelNamespace = $modelNamespace;
    }

    /**
     * @param string $inlineRelativePath
     */
    public function setInlineRelativePath($inlineRelativePath)
    {
        $this->inlineRelativePath = $inlineRelativePath;
    }

    /**
     * @return null
     */
    public function getOptionalClass()
    {
        return $this->optionalClass;
    }

    /**
     * @param null $optionalClass
     */
    public function setOptionalClass($optionalClass)
    {
        $this->optionalClass = $optionalClass;
    }

    /**
     * @return string
     */
    public function getBetweenProtectedsAndGetters()
    {
        return $this->betweenProtectedsAndGetters;
    }

    /**
     * @param string $betweenProtectedsAndGetters
     */
    public function setBetweenProtectedsAndGetters($betweenProtectedsAndGetters)
    {
        $this->betweenProtectedsAndGetters = $betweenProtectedsAndGetters;
    }

    /**
     * @return array|null
     */
    public function getInlineFields(): ? array
    {
        return $this->inlineFields;
    }

    /**
     * @param array|null $inlineFields
     */
    public function setInlineFields(? array $inlineFields)
    {
        $this->inlineFields = $inlineFields;
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
     * @return int
     */
    public function getDeepLevel(): int
    {
        return $this->deepLevel;
    }

    /**
     * @param int $deepLevel
     */
    public function setDeepLevel(int $deepLevel)
    {
        $this->deepLevel = $deepLevel;
    }

    /**
     * @return ContentElementClass
     */
    public function contentElementClass()
    {
        return GeneralUtility::makeInstance(ContentElementClass::class, $this);
    }

    /**
     * @return Model
     */
    public function model()
    {
        return GeneralUtility::makeInstance(Model::class, $this);
    }

    /**
     * @return Template
     */
    public function template()
    {
        return GeneralUtility::makeInstance(Template::class, $this);
    }

    /**
     * @return TCA
     */
    public function tca()
    {
        return GeneralUtility::makeInstance(TCA::class, $this);
    }

    /**
     * @return Icon
     */
    public function icon()
    {
        return GeneralUtility::makeInstance(Icon::class, $this);
    }

    /**
     * @return PreviewImage
     */
    public function previewImage()
    {
        return GeneralUtility::makeInstance(PreviewImage::class, $this);
    }

    /**
     * @return Inline
     */
    public function inline()
    {
        return GeneralUtility::makeInstance(Inline::class, $this);
    }

    /**
     * @return TypoScript
     */
    public function typoScript()
    {
        return GeneralUtility::makeInstance(TypoScript::class, $this);
    }

    /**
     * @return SQLDatabase
     */
    public function sqlDatabase()
    {
        return GeneralUtility::makeInstance(SQLDatabase::class, $this);
    }

    /**
     * @return Translation
     */
    public function translation()
    {
        return GeneralUtility::makeInstance(Translation::class, $this);
    }

    /**
     * @return Register
     */
    public function register()
    {
        return GeneralUtility::makeInstance(Register::class, $this);
    }
}
