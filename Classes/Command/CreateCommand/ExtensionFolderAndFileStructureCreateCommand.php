<?php
namespace Digitalwerk\ContentElementRegistry\Command\CreateCommand;

/**
 * Class ExtensionFolderAndFileStructureCreateCommand
 * @package Digitalwerk\ContentElementRegistry\Command\CreateCommand
 */
class ExtensionFolderAndFileStructureCreateCommand
{
    /**
     * @var string
     */
    protected $extensionName = '';

    /**
     * CheckExtensionFolderStructureCreateCommand constructor.
     * @param string $extensionName
     */
    public function __construct(string $extensionName)
    {
        $this->extensionName = $extensionName;
    }

    public function checkContentElementCreateCommand()
    {
        $requiredFolders = [
            'public/typo3conf/ext/' . $this->extensionName . '/Classes/ContentElement',
            'public/typo3conf/ext/' . $this->extensionName . '/Classes/Domain/Model/ContentElement',
            'public/typo3conf/ext/' . $this->extensionName . '/Resources/Private/Templates/ContentElements',
            'public/typo3conf/ext/' . $this->extensionName . '/Configuration/TCA/Overrides',
            'public/typo3conf/ext/' . $this->extensionName . '/Resources/Public/Icons/ContentElement',
            'public/typo3conf/ext/' . $this->extensionName . '/Resources/Public/Images/ContentElementPreviews',
            'public/typo3conf/ext/' . $this->extensionName . '/Resources/Private/Language'
        ];
        $this->checkAndCreateFolders($requiredFolders);

        $requiredFiles = [
            'public/typo3conf/ext/' . $this->extensionName . '/ext_localconf.php' => [
                'path' => 'public/typo3conf/ext/' . $this->extensionName . '/ext_localconf.php',
                'data' => $this->extLocalConfContentElementCreateCommandBasicStructure()
            ],
            'public/typo3conf/ext/' . $this->extensionName . '/ext_tables.sql' => [
                'path' => 'public/typo3conf/ext/' . $this->extensionName . '/ext_tables.sql',
                'data' => "\n"
            ],
            'public/typo3conf/ext/' . $this->extensionName . '/ext_typoscript_setup.typoscript' => [
                'path' => 'public/typo3conf/ext/' . $this->extensionName . '/ext_typoscript_setup.typoscript',
                'data' => $this->typoScriptContentElementCreateCommandBasicStructure()
            ],
            'public/typo3conf/ext/' . $this->extensionName . '/Resources/Private/Language/locallang_db.xlf' => [
                'path' => 'public/typo3conf/ext/' . $this->extensionName . '/Resources/Private/Language/locallang_db.xlf',
                'data' => $this->localLangContentElementCreateCommandBasicStructure()
            ]
        ];
        $this->checkAndCreateFiles($requiredFiles);
    }

    /**
     * @param $requiredFolders
     */
    public function checkAndCreateFolders($requiredFolders)
    {
        foreach ($requiredFolders as $requiredFolder) {
            if (!file_exists($requiredFolder)) {
                mkdir($requiredFolder, 0777, true);
            }
        }
    }

    /**
     * @param $requiredFiles
     */
    public function checkAndCreateFiles($requiredFiles)
    {
        foreach ($requiredFiles as $requiredFile) {
            if (!file_exists($requiredFile)) {
                file_put_contents(
                    $requiredFile['path'],
                    $requiredFile['data']
                );
            }
        }
    }

    /**
     * @return string
     */
    public function typoScriptContentElementCreateCommandBasicStructure()
    {
        return 'config.tx_extbase {
  persistence {
    classes {

    }
  }
}';
    }

    /**
     * @return string
     */
    public function localLangContentElementCreateCommandBasicStructure()
    {
        return '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
<xliff version="1.0">
    <file source-language="en" datatype="plaintext" original="messages" date="2015-08-14T12:33:16Z" product-name="content_element_registry">
        <header/>
        <body>
        </body>
    </file>
</xliff>';
    }

    /**
     * @return string
     */
    public function extLocalConfContentElementCreateCommandBasicStructure()
    {
        return '<?php
if (!defined(\'TYPO3_MODE\')) {
    die(\'Access denied.\');
}

call_user_func(
    function ($extKey) {

    },
    $_EXTKEY
);
';
    }
}
