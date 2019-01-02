<?php
namespace Digitalwerk\ContentElementRegistry\ContentElement;

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
     * Prefix for all CEs
     */
    const PREFIX = 'dwContentElementRegistry';

    /**
     * Palettes
     *
     * @var array
     */
    private $palettes = [];

    /**
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
     * @return string
     * @throws \ReflectionException
     */
    public function getName()
    {
        return (new \ReflectionClass($this))->getShortName();
    }

    /**
     * @return string
     * @throws \ReflectionException
     */
    public function getTemplateName()
    {
        return $this->getName();
    }

    /**
     * @return mixed
     */
    public function getIdentifier()
    {
        return \strtolower(\sprintf("%s_%s", static::PREFIX, $this->getName()));
    }

    /**
     * @return string
     */
    public function getCType()
    {
        return $this->getIdentifier();
    }

    /**
     * @return string
     * @throws \ReflectionException
     */
    public function getExtensionKey()
    {
        return GeneralUtility::camelCaseToLowerCaseUnderscored(
            ContentElementRegistryUtility::getNamespaceConfiguration(static::class, 'extensionName')
        );
    }

    /**
     * @return mixed
     */
    public function getIconIdentifier()
    {
        return $this->getIdentifier();
    }

    /**
     * @return string
     */
    public function getIconPath()
    {
        $iconSource = "EXT:{$this->getExtensionKey()}/Resources/Public/Icons/ContentElement/{$this->getIconIdentifier()}.svg";
        if (!file_exists(\TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($iconSource))) {
            $iconSource = "EXT:core/Resources/Public/Icons/T3Icons/default/default-not-found.svg";
        }

        return $iconSource;
    }

    /**
     * @return null|string
     */
    public function getTitle()
    {
        return "LLL:EXT:{$this->getExtensionKey()}/Resources/Private/Language/locallang_db.xlf:tt_content.{$this->getIdentifier()}.title";
    }

    /**
     * @return null|string
     */
    public function getDescription()
    {
        return "LLL:EXT:{$this->getExtensionKey()}/Resources/Private/Language/locallang_db.xlf:tt_content.{$this->getIdentifier()}.description";
    }

    /**
     * @return string
     */
    protected function getWizardTabName()
    {
        return 'common';
    }

    /**
     * @return string
     */
    protected function getWizardTabHeader()
    {
        return 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:common';
    }

    /**
     * @return string
     */
    public function getGroupName()
    {
        return $this->getWizardTabName();
    }

    /**
     * @return string
     */
    public function getGroupLabel()
    {
        return $this->getWizardTabHeader();
    }

    /**
     * @return string
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
     * @return array
     */
    public function getColumnsOverrides()
    {
        return [];
    }

    /**
     * @param string $name
     * @param string $showItem
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
     * @return array
     */
    public function getPalettes()
    {
        return $this->palettes;
    }
}
