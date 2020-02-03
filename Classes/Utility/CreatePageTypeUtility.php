<?php
namespace Digitalwerk\ContentElementRegistry\Utility;

use Symfony\Component\Console\Exception\InvalidArgumentException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class CreatePageTypeUtility
 * @package Digitalwerk\ContentElementRegistry\Utility
 */
class CreatePageTypeUtility
{
    /**
     * @param $autoHeader
     * @param $pageTypeName
     */
    public static function checkDefaultTemplateOptionalAndAddAutoHeader($autoHeader, $pageTypeName)
    {
        if ($autoHeader !== 'true' && $autoHeader !== 'false') {
            throw new InvalidArgumentException('Syntax error in field "auto-header" : ' . $autoHeader . ' (must be false or true)');
        }
                $pageTypeTemplate = 'public/typo3conf/ext/dw_boilerplate/Resources/Private/Partials/PageType/' . $pageTypeName . '/Header.html';
        $pageTypeTemplateContent = '<html xmlns="http://www.w3.org/1999/xhtml" lang="en"
      xmlns:f="http://typo3.org/ns/TYPO3/Fluid/ViewHelpers"
      xmlns:v="http://typo3.org/ns/FluidTYPO3/Vhs/ViewHelpers"
      data-namespace-typo3-fluid="true">

<f:alias map="{' . strtolower($pageTypeName) . ':dwPageType}">

</f:alias>

</html>';

        if ($autoHeader === 'true') {
    //        Check default template if it is set to optional
            $defaultTemplate = 'public/typo3conf/ext/dw_boilerplate/Resources/Private/Templates/Page/Default.html';
            $defaultTemplateLines = file($defaultTemplate);
            if (!(in_array('<f:render partial="PageType/{dwPageType.modelName}/Header" optional="1" arguments="{dwPageType:dwPageType}" />', array_map('trim', $defaultTemplateLines))))
            {
                GeneralCreateCommandUtility::importStringInToFileAfterString(
                    $defaultTemplate,
                    ["    <f:render partial=\"PageType/{dwPageType.modelName}/Header\" optional=\"1\" arguments=\"{dwPageType:dwPageType}\" /> \n"],
                    ['<!--TYPO3SEARCH_begin-->']
                );
            }

            if (!file_exists('public/typo3conf/ext/dw_boilerplate/Resources/Private/Partials/PageType')) {
                mkdir('public/typo3conf/ext/dw_boilerplate/Resources/Private/Partials/PageType', 0777, true);
            }
            if (!file_exists('public/typo3conf/ext/dw_boilerplate/Resources/Private/Partials/PageType/' . $pageTypeName)) {
                mkdir('public/typo3conf/ext/dw_boilerplate/Resources/Private/Partials/PageType/' . $pageTypeName, 0777, true);
            }
            file_put_contents($pageTypeTemplate, $pageTypeTemplateContent);
        }
    }
}
