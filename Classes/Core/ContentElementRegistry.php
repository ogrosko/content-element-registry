<?php
namespace Digitalwerk\ContentElementRegistry\Core;

use Composer\Autoload\ClassMapGenerator;
use Digitalwerk\ContentElementRegistry\ContentElement\AbstractContentElementRegistryItem;
use Digitalwerk\ContentElementRegistry\Utility\ContentElementRegistryUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Class ContentElementRegistry
 * @package Digitalwerk\ContentElementRegistry\Core
 */
class ContentElementRegistry implements SingletonInterface
{

    /**
     * Extension key
     */
    const EXTENSION_KEY = 'content_element_registry';

    /**
     * CEs (Content elements) register
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
        $this->registerContentElements();
        $this->emitRegisterContentElementRegistryClass();
    }

    /**
     * Register content elements based on extConf
     *
     * @throws ContentElementRegistryException
     */
    private function registerContentElements()
    {
        $contentElementsPaths = $this->getExtConf('contentElementsPaths');
        if ($contentElementsPaths) {
            $contentElementsPaths = GeneralUtility::trimExplode(',', $contentElementsPaths);
            foreach ($contentElementsPaths as $contentElementsPath) {
                if (GeneralUtility::isFirstPartOfStr($contentElementsPath, 'EXT:')) {
                    $contentElementsPath = GeneralUtility::getFileAbsFileName($contentElementsPath);
                }
                if (\file_exists($contentElementsPath) and \is_dir($contentElementsPath)) {
                    $contentElementsClasses = ClassMapGenerator::createMap($contentElementsPath);
                    foreach ($contentElementsClasses as $contentElementClass => $contentElementClassPath) {
                        $contentElement = GeneralUtility::makeInstance($contentElementClass);
                        if ($contentElement instanceof AbstractContentElementRegistryItem) {
                            $this->registerContentElement($contentElement);
                        }
                    }
                }
            }
        }
    }

    /**
     * Emit registerContentElementRegistryClass signal
     *
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    private function emitRegisterContentElementRegistryClass()
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
     * @param AbstractContentElementRegistryItem $element
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
        $this->sortContentElements();
        return $this->contentElements;
    }

    /**
     * @return void
     */
    private function sortContentElements()
    {
        \uasort($this->contentElements, function (AbstractContentElementRegistryItem $a, AbstractContentElementRegistryItem $b) {
            if ($a->getGroupName() === $b->getGroupName()) {
                return \strcmp($a->getIdentifier(), $b->getIdentifier());
            }
            return \strcmp($a->getGroupName(), $b->getGroupName());
        });
    }

    /**
     * CE getter
     *
     * @param string $elementIdentifier
     * @return AbstractContentElementRegistryItem
     * @throws ContentElementRegistryException
     */
    public function getContentElement(string $elementIdentifier)
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
    public function existsContentElement(string $elementIdentifier)
    {
        return \array_key_exists($elementIdentifier, $this->contentElements);
    }

    /**
     * Get config.tx_extbase for Domains. This generates something like this:
     *
     *  config.tx_extbase {
     *       persistence {
     *           classes {
     *                Digitalwerk\ContentElementRegistry\Domain\Model\ContentElement {
     *                   subclasses {
     *                       ...
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
     * @return array
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

        return $config;
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

    /**
     * Retrieve extension configuration
     *
     * @param null $configurationKey
     * @return array|mixed
     */
    private function getExtConf($configurationKey = null)
    {
        $extConf = [];

        if (\version_compare(VersionNumberUtility::getCurrentTypo3Version(), '9.0.0', '<')) {
            if (isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][self::EXTENSION_KEY])) {
                $extConf = \unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][self::EXTENSION_KEY]);
            }
        } else {
            $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get(self::EXTENSION_KEY);
        }

        if (null !== $configurationKey and isset($extConf[$configurationKey])) {
            return $extConf[$configurationKey];
        }

        return $extConf;
    }
}
