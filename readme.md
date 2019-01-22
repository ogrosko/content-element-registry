![](./Resources/Public/Icons/Extension.svg)
# Content element registry
Typo3 extension simplify process of creating new content elements in [Typo3 way](https://docs.typo3.org/typo3cms/extensions/fluid_styled_content/7.6/AddingYourOwnContentElements/Index.html)

## Install
Install extension via composer `composer req digitalwerk/content-element-registry` and activate it in Extension module

## Setup
After activating extension, you have to define your Content elements configuration classes.
It can be done in two ways:
1. By defining paths in extension configuration (aka *extConf*). Can contain comma separated list of paths do directories
**Example:** `EXT:your_ext_1/Classes/ContentElements/,EXT:your_ext_2/Classes/ContentElements/` 
2. By registering Signal slot in `ext_localconf.php` of your extension as follows:
```php
<?php
$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
    \TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class
);
$signalSlotDispatcher->connect(
    \Digitalwerk\ContentElementRegistry\Core\ContentElementRegistry::class,
    'registerContentElementRegistryClass',
    \YourVendor\YourExtension\::class,
    'yourMethodName'
);
```
Method `\YourVendor\YourExtension\::yourMethodName` can looks like this:
```php
<?php
/**
 * @param \Digitalwerk\ContentElementRegistry\Core\ContentElementRegistry $contentElementRegistry
 */
public function registerContentElements(\Digitalwerk\ContentElementRegistry\Core\ContentElementRegistry $contentElementRegistry)
{
    $contentElementsClassMap = \Composer\Autoload\ClassMapGenerator::createMap(PATH_typo3conf.'ext/your_extension/Classes/ContentElement/');
    foreach ($contentElementsClassMap as $elementClass => $elementClassPath) {
        $contentElementRegistry->registerContentElement(\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($elementClass));
    }
}
```

## Creating new content element
To create new Content element you have to create new *Class* inside your folder defined in [Setup section](#setup) which extends `Digitalwerk\ContentElementRegistry\ContentElement\AbstractContentElementRegistryItem`
```php
<?php
namespace \YourVendor\YourExtension\ContentElement;

use Digitalwerk\ContentElementRegistry\ContentElement\AbstractContentElementRegistryItem;

class YourNewContentElement extends AbstractContentElementRegistryItem
{

}
```
After clearing typo3 caches you should now see new content element in wizard
![](./Resources/Public/Images/NewContentElement1.png)
As you can see there is either title nor description of the content element. These are automatically fetched and translated from locallang file inside of your extension:
`EXT:your_extension/Resources/Private/Language/locallang_db.xlf`. Yoy can now define your CE title and description as follows:
```xml
<trans-unit id="tt_content.yourextension_yournewcontentelement.title">
    <source>Your new content element</source>
</trans-unit>
<trans-unit id="tt_content.yourextension_yournewcontentelement.description">
    <source>Your new content element description</source>
</trans-unit>
```
