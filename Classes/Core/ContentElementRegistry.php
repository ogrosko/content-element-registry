<?php
namespace Digitalwerk\ContentElementRegistry\Core;

use Composer\Autoload\ClassMapGenerator;
use Digitalwerk\ContentElementRegistry\ContentElement\AbstractContentElementRegistryItem;
use Digitalwerk\ContentElementRegistry\Domain\Model\ContentElement;
use Digitalwerk\ContentElementRegistry\Events\ContentElementRegistryClassEvent;
use Digitalwerk\ContentElementRegistry\Utility\ContentElementRegistryUtility;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
     * @throws ContentElementRegistryException
     */
    public function __construct()
    {
        if ($this->isHeadless() && !ExtensionManagementUtility::isLoaded('headless')) {
            throw new ContentElementRegistryException("ContentElementRegistry extension is in headless mode but 'headless' extension not loaded", 1602071578);
        }
        $this->registerContentElements();
        $this->generateExtbasePersistenceClasses();
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
                //                TODO: replace with str_starts_with() in PHP 8
                if (GeneralUtility::isFirstPartOfStr($contentElementsPath, 'EXT:')) {
                    $contentElementsPath = GeneralUtility::getFileAbsFileName($contentElementsPath);
                }
                if (\file_exists($contentElementsPath) and \is_dir($contentElementsPath)) {
                    $contentElementsClasses = ClassMapGenerator::createMap($contentElementsPath);
                    foreach ($contentElementsClasses as $contentElementClass => $contentElementClassPath) {
                        $reflection = new \ReflectionClass($contentElementClass);
                        if ($reflection->isInstantiable()
                            && $reflection->isSubclassOf(AbstractContentElementRegistryItem::class)
                        ) {
                            $this->registerContentElement(GeneralUtility::makeInstance($contentElementClass));
                        }
                    }
                }
            }
        }
    }

    /**
     * Emit registerContentElementRegistryClass signal
     */
    private function emitRegisterContentElementRegistryClass()
    {
        /** @var EventDispatcherInterface $dispatcher */
        $dispatcher = GeneralUtility::getContainer()->get(EventDispatcherInterface::class);
        $dispatcher->dispatch(new ContentElementRegistryClassEvent($this));
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
     * @throws ContentElementRegistryException|\ReflectionException
     */
    public function registerContentElement(AbstractContentElementRegistryItem $element)
    {
        if ($this->existsContentElement($element->getIdentifier())) {
            throw new ContentElementRegistryException("Content Element '{$element->getIdentifier()}' already registered", 1540825475);
        }
        $element->setIsHeadless($this->isHeadless());
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
     * @throws \ReflectionException
     */
    private function sortContentElements()
    {
        \uasort(
            $this->contentElements,
            function (AbstractContentElementRegistryItem $a, AbstractContentElementRegistryItem $b) {
                if ($a->getGroupName() === $b->getGroupName()) {
                    return \strcmp($a->getIdentifier(), $b->getIdentifier());
                }
                return \strcmp($a->getGroupName(), $b->getGroupName());
            }
        );
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
        $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get(self::EXTENSION_KEY);

        if (null !== $configurationKey and isset($extConf[$configurationKey])) {
            return $extConf[$configurationKey];
        }

        return $extConf;
    }

    /**
     * Check if CER is in headless mode
     * @return bool
     */
    protected function isHeadless(): bool
    {
        return $this->getExtConf('isHeadless') === '1';
    }

    /**
     * Generate Extbase persistence class file
     * @see https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.0/Breaking-87623-ReplaceConfigpersistenceclassesTyposcriptConfiguration.html
     */
    protected function generateExtbasePersistenceClasses()
    {
        if ($this->contentElements) {
            $persistenceClassesFile = GeneralUtility::getFileAbsFileName('EXT:'.self::EXTENSION_KEY.'/Configuration/Extbase/Persistence/Classes.php');

            $definedClasses = [
                ContentElement::class => [
                    'tableName' => 'tt_content',
                ],
            ];
            foreach ($this->columnsMapping as $columnMappingValue => $columnMappingKey) {
                $definedClasses[ContentElement::class]['properties'][$columnMappingKey] = [
                    'fieldName' => $columnMappingValue
                ];
            }

            /**
             * @var string $CType
             * @var AbstractContentElementRegistryItem $contentElement
             */
            foreach ($this->contentElements as $CType => $contentElement) {
                $contentElementObjectClass = $contentElement->getDomainModelClassName();
                $content = [
                    ContentElement::class => [
                        'subclasses' => [
                            $contentElementObjectClass => $contentElementObjectClass,
                        ]
                    ],
                    $contentElementObjectClass => [
                        'tableName' => 'tt_content',
                        'recordType' => $CType
                    ]
                ];

                if ($contentElement->getColumnsMapping()) {
                    foreach ($contentElement->getColumnsMapping() as $columnMappingValue => $columnMappingKey) {
                        $content[$contentElementObjectClass]['properties'][$columnMappingKey] = [
                            'fieldName' => $columnMappingValue
                        ];
                    }
                }

                ArrayUtility::mergeRecursiveWithOverrule(
                    $definedClasses,
                    $content,
                    true,
                    false
                );
            }

            file_put_contents($persistenceClassesFile, $this->generateExtbaseClassesFileFromArray($definedClasses));
        }
    }

    /**
     * Generate data for Extbase Persistence Classes
     *
     * @param array $definedClasses
     * @return string
     */
    private function generateExtbaseClassesFileFromArray(array $definedClasses)
    {
        $file[] = '<?php';
        $file[] = 'declare(strict_types=1);';
        $file[] = '';
        if (!empty($definedClasses)) {
            $file[] = 'return ' . var_export($definedClasses, true) . ';';
        }

        return implode("\n", $file);
    }
}
