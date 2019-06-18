<?php
namespace Digitalwerk\ContentElementRegistry\ContentElement;

use Digitalwerk\ContentElementRegistry\Core\ContentElementRegistry;
use Digitalwerk\ContentElementRegistry\DataProcessing\ContentElementObjectDataProcessor;
use Digitalwerk\ContentElementRegistry\Utility\ContentElementRegistryUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class AbstractContentElementRegistryItem
 * @package Digitalwerk\ContentElementRegistry\ContentElement
 */
abstract class AbstractContentElementRegistryItem
{

    /**
     * Palettes
     *
     * @var array
     */
    private $palettes = [];

    /**
     * Table columns mappings to Model properties
     *
     * @var array
     */
    protected $columnsMapping = [];

    /**
     * AbstractContentElementRegistryItem constructor.
     */
    public function __construct()
    {
    }

    /**
     * Get CE name
     *
     * @return string
     * @throws \ReflectionException
     */
    public function getName()
    {
        return (new \ReflectionClass($this))->getShortName();
    }

    /**
     * Get CE template name
     *
     * @return string
     * @throws \ReflectionException
     */
    public function getTemplateName()
    {
        return $this->getName();
    }

    /**
     * Get CE identifier
     *
     * @return string
     * @throws \ReflectionException
     */
    public function getIdentifier()
    {
        return \strtolower(\sprintf("%s_%s", $this->getExtensionName(), $this->getName()));
    }

    /**
     * get CE CType
     *
     * @return string
     * @throws \ReflectionException
     */
    public function getCType()
    {
        return $this->getIdentifier();
    }

    /**
     * Get extension key
     *
     * @return string
     * @throws \ReflectionException
     */
    public function getExtensionKey()
    {
        return GeneralUtility::camelCaseToLowerCaseUnderscored($this->getExtensionName());
    }

    /**
     * Get extension name
     *
     * @return string
     * @throws \ReflectionException
     */
    public function getExtensionName()
    {
        return ContentElementRegistryUtility::getNamespaceConfiguration(static::class, 'extensionName');
    }

    /**
     * Get CE icon identifier
     *
     * @return string
     * @throws \ReflectionException
     */
    public function getIconIdentifier()
    {
        return $this->getIdentifier();
    }

    /**
     * Get path to icons
     *
     * @return string
     * @throws \ReflectionException
     */
    public function getIconsPath(): string
    {
        return "EXT:{$this->getExtensionKey()}/Resources/Public/Icons/ContentElement/";
    }

    /**
     * Get CE icon path
     *
     * @return string
     * @throws \ReflectionException
     */
    public function getIconSrcPath()
    {
        $iconSource = "{$this->getIconsPath()}{$this->getIconIdentifier()}.svg";
        if (!file_exists(GeneralUtility::getFileAbsFileName($iconSource))) {
            $iconSource = "EXT:".ContentElementRegistry::EXTENSION_KEY."/Resources/Public/Icons/CEDefaultIcon.svg";
        }

        return $iconSource;
    }

    /**
     * Get CE LLL title
     *
     * @return string
     * @throws \ReflectionException
     */
    public function getTitle()
    {
        return "LLL:EXT:{$this->getExtensionKey()}/Resources/Private/Language/locallang_db.xlf:tt_content.{$this->getIdentifier()}.title";
    }

    /**
     * Get CE LLL description
     *
     * @return string
     * @throws \ReflectionException
     */
    public function getDescription()
    {
        return "LLL:EXT:{$this->getExtensionKey()}/Resources/Private/Language/locallang_db.xlf:tt_content.{$this->getIdentifier()}.description";
    }

    /**
     * Get CE wizard tab name [common, menu, special, forms, plugins]
     * Specify in which wizard tab should be element placed
     *
     * @return string
     */
    protected function getWizardTabName()
    {
        return 'common';
    }

    /**
     * Get CE wizard tab name
     *
     * @return string
     */
    protected function getWizardTabHeader()
    {
        return "LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:{$this->getWizardTabName()}";
    }

    /**
     * Get CE group
     *
     * @return string
     */
    public function getGroupName()
    {
        return $this->getWizardTabName();
    }

    /**
     * Get CE group label
     *
     * @return string
     */
    public function getGroupLabel()
    {
        return $this->getWizardTabHeader();
    }

    /**
     * Get CE PageTSconfig
     *
     * @return string
     * @throws \ReflectionException
     */
    public function getPageTSconfig()
    {
        $config = [
            $this->getWizardTabName() => [
                'elements' => [
                    $this->getCType() => [
                        'iconIdentifier' => $this->getIconIdentifier(),
                        'title' => $this->getTitle(),
                        'description' => $this->getDescription(),
                        'tt_content_defValues' => [
                            'CType' => $this->getCType(),
                        ],
                    ],
                ],
                'show' => ":= addToList({$this->getCType()})",
                'header' => $this->getWizardTabHeader(),
            ],
        ];

        return ContentElementRegistryUtility::convertArrayToTypoScript(
            $config,
            'mod.wizards.newContentElement.wizardItems'
        );
    }

    /**
     * Get CE TCA showitem config
     *
     * @return string
     */
    public function getTCAShowItemConfig()
    {
        return "--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                    --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.general;general,
                    {$this->getPalettesShowItemString()}
                    {$this->getAdditionalTCAConfig()}
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,
                    --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.frames;frames,
                    --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.appearanceLinks;appearanceLinks,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                    --palette--;;language,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                    --palette--;;hidden,
                    --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,
                    categories,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
                    rowDescription,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended";
    }


    /**
     * Get CE tt_content typoscript config
     *
     * @return string
     * @throws \ReflectionException
     */
    public function getTypoScriptConfiguration()
    {
        $config = [
            'tt_content' => [
                $this->getCType() => '< lib.contentElement',
                $this->getCType().'.' => [
                    'templateName' => $this->getTemplateName(),
                    'dataProcessing.' => [
                        '0' => ContentElementObjectDataProcessor::class
                    ],
                ]
            ],
        ];

        return ContentElementRegistryUtility::convertArrayToTypoScript($config);
    }

    /**
     * Get CE Extbase typoscript config
     *
     * @throws \ReflectionException
     */
    public function getTypoScriptPersistenceConfig()
    {
        $config = [];
        $className = $this->getDomainModelClassName();

        if ($className) {
            $config[$className] = [
                'mapping' => [
                    'tableName' => 'tt_content',
                    'recordType' => $this->getCType(),
                ],
            ];

            // Add columns mappings
            if (!empty($this->columnsMapping)) {
                foreach ($this->columnsMapping as $column => $property) {
                    $config[$className]['mapping']['columns'][$column] = [
                        'mapOnProperty' => $property,
                    ];
                }
            }
        }

        return ContentElementRegistryUtility::convertArrayToTypoScript(
            $config,
            'config.tx_extbase.persistence.classes'
        );
    }

    /**
     * Return related Domain Object class name
     *
     * @return bool|string
     * @throws \ReflectionException
     */
    public function getDomainModelClassName()
    {
        $modelNamespace = [
            ContentElementRegistryUtility::getNamespaceConfiguration(static::class, 'vendorName'),
            ContentElementRegistryUtility::getNamespaceConfiguration(static::class, 'extensionName'),
            'Domain',
            'Model',
            ContentElementRegistryUtility::getNamespaceConfiguration(static::class, 'modelName'),
            $this->getName(),
        ];
        $class = \implode('\\', $modelNamespace);

        return \class_exists($class) ? $class : false;
    }

    /**
     * Additional "showitem" configuration
     *
     * @return string
     */
    protected function getAdditionalTCAConfig()
    {
        return '';
    }

    /**
     * Additional columns overrides
     *
     * @return array
     */
    public function getColumnsOverrides()
    {
        return [];
    }

    /**
     * Add pallete to CE
     *
     * @param string $name Palette name
     * @param string $showItem Pallete showitem string
     * @throws \Exception
     */
    protected function addPalette($name, $showItem)
    {
        $paletteIdentifier = \sprintf("%s_%s", $this->getIdentifier(), $name);
        if (\array_key_exists($paletteIdentifier, $this->palettes)) {
            throw new \Exception("Palette with name {$paletteIdentifier} already exists", 1540890148);
        }

        $this->palettes[$paletteIdentifier] = [
            'label' => "LLL:EXT:{$this->getExtensionKey()}/Resources/Private/Language/locallang_db.xlf:tt_content.{$this->getIdentifier()}.palette.{$name}",
            'showitem' => $showItem,
        ];
    }

    /**
     * Get palettes showitem string
     *
     * @return string
     */
    private function getPalettesShowItemString()
    {
        $palettesString = '';
        foreach ($this->palettes as $paletteName => $paletteConfig) {
            $palettesString .= "--palette--;{$paletteConfig['label']};{$paletteName},";
        }

        return $palettesString;
    }

    /**
     * Get Ce Palettes
     *
     * @return array
     */
    public function getPalettes()
    {
        return $this->palettes;
    }

    /**
     * @return bool
     * @throws \ReflectionException
     */
    public function flexFormDefinitionExists(): bool
    {
        return \file_exists(
            GeneralUtility::getFileAbsFileName(
                substr($this->getFlexFormFormDefinition(), \strlen('FILE:'))
            )
        );
    }

    /**
     * @return string
     * @throws \ReflectionException
     */
    public function getFlexFormFormDefinition(): string
    {
        return "FILE:EXT:{$this->getExtensionKey()}/Configuration/FlexForms/ContentElement/{$this->getIdentifier()}.xml";
    }
}
