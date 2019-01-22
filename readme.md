![](./Resources/Public/Icons/Extension.svg)
# Content element registry
Typo3 extension simplify process of creating new content elements (CE) in [Typo3 way](https://docs.typo3.org/typo3cms/extensions/fluid_styled_content/7.6/AddingYourOwnContentElements/Index.html)

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
`EXT:your_extension/Resources/Private/Language/locallang_db.xlf`. You can now define your CE title and description as follows:
```xml
<trans-unit id="tt_content.yourextension_yournewcontentelement.title">
    <source>Your new content element</source>
</trans-unit>
<trans-unit id="tt_content.yourextension_yournewcontentelement.description">
    <source>Your new content element description</source>
</trans-unit>
```

When you add this new CE it will contains only default CE fields:
![](./Resources/Public/Images/NewContentElement2.png)

### Adding CE fields
To add new fields you have to define it in `\YourVendor\YourExtension\ContentElement\YourNewContentElement`:
```php
<?php
namespace \YourVendor\YourExtension\ContentElement;

use Digitalwerk\ContentElementRegistry\ContentElement\AbstractContentElementRegistryItem;

class YourNewContentElement extends AbstractContentElementRegistryItem
{

    /**
     * YourNewContentElement constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();
        $this->addPalette(
            'default',
            'header, --linebreak--, bodytext'
        );
    }
}
```

By this, we defined new CE *palette* with name `default` with two fields `header` and `bodytext`.

**Code description:**
1. Name of the palette must be unique per CE. Label for palette can be defined in `locallang_db.xlf` with following key: `tt_content.yourextension_yournewcontentelement.palette.default`
2. Fields definition syntax must follows [TCA palette showitem syntax](https://docs.typo3.org/typo3cms/TCAReference/Palettes/Index.html#showitem)
3. Used fields must be properly configured in [Typo3 TCA](https://docs.typo3.org/typo3cms/TCAReference/8.7/)
4. You can add as many palettes as you need ;)

Our CE now should looks like this:
![](./Resources/Public/Images/NewContentElement3.png)

If you need to override field configuration you can do this in this way: (In following example we enabled rich text editor for `bodytext` field)
```php
<?php
    /**
     * @return array
     */
    public function getColumnsOverrides()
    {
        return [
            'bodytext' => [
                'config' => [
                    'enableRichtext' => true,
                ],
            ],
        ];
    }
```

### CE Template

