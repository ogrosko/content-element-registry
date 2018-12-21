<?php
namespace Digitalwerk\ContentElementRegistry\Core;

use Composer\Autoload\ClassMapGenerator;
use Digitalwerk\ContentElementRegistry\ContentElement\AbstractContentElementRegistryItem;
use Digitalwerk\ContentElementRegistry\Utility\ContentElementRegistryUtility;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Class ContentElementRegistry
 * @package Digitalwerk\ContentElementRegistry\Core
 */
class ContentElementRegistry implements SingletonInterface
{

    /**
     * Registered CEs
     *
     * @var array
     */
    protected $contentElements = [];

    /**
     * Base columns mapping common for all CEs
     *
     * @var array
     */
    protected $columnsMapping = [
        'CType' => 'CType',
        'sectionIndex' => 'sectionIndex',
        'header' => 'header',
    ];

    /**
     * ContentElementsRegistry constructor.
     */
    public function __construct()
    {
        $dispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        $dispatcher->dispatch(__CLASS__, 'registerContentElementRegistryClass', [$this]);
    }

    /**
     * @return ContentElementRegistry
     */
    public static function getInstance()
    {
        return GeneralUtility::makeInstance(self::class);
    }

    /**
     * Registration of new CE
     *
     * @param string $name
     * @param string $palette
     * @throws ContentElementRegistryException
     */
    public function registerContentElement(AbstractContentElementRegistryItem $element)
    {
        if ($this->existsContentElement($element->getIdentifier())) {
            throw new ContentElementRegistryException("Content Element '{$element->getIdentifier()}' already registered", 1540825475);
        }
        $this->contentElements[$element->getIdentifier()] = $element;
    }

    /**
     * Getter for registered CEs
     *
     * @return array<AbstractContentElementRegistryItem>
     */
    public function getContentElements()
    {
        return $this->contentElements;
    }

    /**
     * CE getter
     *
     * @param string $elementIdentifier
     * @return AbstractContentElementRegistryItem
     */
    public function getContentElement($elementIdentifier)
    {
        if ($this->existsContentElement($elementIdentifier)) {
            return $this->contentElements[$elementIdentifier];
        } else {
            throw new ContentElementRegistryException("Content Element '{$elementIdentifier}' doesn't exists or is not registered", 1542115424);
        }
    }

    /**
     * Check if CE is registered
     *
     * @param string $elementIdentifier
     * @return bool
     */
    public function existsContentElement($elementIdentifier)
    {
        return \array_key_exists($elementIdentifier, $this->contentElements);
    }

    /**
     * Get config.tx_extbase for Domains. This generates something like this:
     *
     *  config.tx_extbase {
     *       persistence {
     *           classes {
     *               BinaryBay\BbBoilerplate\Domain\Model\ContentElement {
     *                   subclasses {
     *                       BinaryBay\BbBoilerplate\Domain\Model\ContentElement\RegularTextElement = BinaryBay\BbBoilerplate\Domain\Model\ContentElement\RegularTextElement
     *                   }
     *                   mapping {
     *                       tableName = tt_content
     *                       columns {
     *                           CType.mapOnProperty = CType
     *                           sectionIndex.mapOnProperty = sectionIndex
     *                           header.mapOnProperty = header
     *                       }
     *                   }
     *               }
     *           }
     *       }
     *   }
     *
     * @return string
     * @throws \ReflectionException
     */
    public function getBaseTypoScriptPersistenceConfig()
    {
        $config = [];
        $className = $this->getDomainModelClassName();

        if ($className) {
            $config[$className] = [
                'mapping' => [
                    'tableName' => 'tt_content',
                ],
            ];

            // Add Subclasses
            if (!empty($this->contentElements)) {
                /** @var AbstractContentElementRegistryItem $contentElement */
                foreach ($this->contentElements as $contentElement) {
                    $ceClassName = $contentElement->getDomainModelClassName();
                    if ($ceClassName) {
                        $config[$className]['subclasses'][$ceClassName] = $ceClassName;
                    }
                }
            }

            // Add columns mappings
            if (!empty($this->columnsMapping)) {
                foreach ($this->columnsMapping as $column => $property) {
                    $config[$className]['mapping']['columns'][$column] = [
                        'mapOnProperty' => $property,
                    ];
                }
            }
        }

        return ContentElementRegistryUtility::convertArrayToTypoScript($config, 'config.tx_extbase.persistence.classes');
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
            'ContentElement',
        ];

        $class = \implode('\\', $modelNamespace);

        return \class_exists($class) ? $class : false;
    }
}
