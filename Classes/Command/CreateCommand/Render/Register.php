<?php
namespace Digitalwerk\ContentElementRegistry\Command\CreateCommand\Render;

use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Render;
use Digitalwerk\ContentElementRegistry\Utility\GeneralCreateCommandUtility;

/**
 * Class Register
 * @package Digitalwerk\ContentElementRegistry\Command\CreateCommand\Render
 */
class Register
{
    /**
     * @var Render
     */
    protected $render = null;

    public function __construct(Render $render)
    {
        $this->render = $render;
    }

    public function pageTypeToExtTables()
    {
        $pageTypeName = $this->render->getName();
        $extensionName = $this->render->getExtensionName();

        GeneralCreateCommandUtility::importStringInToFileAfterString(
            'public/typo3conf/ext/' . $extensionName . '/ext_tables.php',
            [
                "        Digitalwerk\DwPageTypes\Utility\PageTypeUtility::addPageDoktype(" . $pageTypeName . "::getDoktype()); \n"
            ],
            'call_user_func(',
            1
        );

        GeneralCreateCommandUtility::importStringInToFileAfterString(
            'public/typo3conf/ext/' . $extensionName . '/ext_tables.php',
            [
                "\nuse " . $this->render->getModelNamespace() . "\\" . $pageTypeName . ";"
            ],
            '<?php',
            0

        );
    }
}
