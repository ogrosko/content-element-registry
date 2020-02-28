<?php
namespace Digitalwerk\ContentElementRegistry\Command\CreateCommand\Render;

use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Render;
use Digitalwerk\ContentElementRegistry\Utility\GeneralCreateCommandUtility;
use InvalidArgumentException;

/**
 * Class TypoScript
 * @package Digitalwerk\ContentElementRegistry\Command\CreateCommand\Render
 */
class TypoScript
{
    /**
     * @var Render
     */
    protected $render = null;

    /**
     * TypoScript constructor.
     * @param Render $render
     */
    public function __construct(Render $render)
    {
        $this->render = $render;
    }

    /**
     * @return string
     */
    public function addFieldsToTypoScriptMapping()
    {
        $fields = $this->render->getFields();

        if (!empty($fields)) {
            $name = $this->render->getName();
            $createdFields = [];

            foreach ($fields->getFields() as $field) {
                $fieldName = $field->getName();
                $fieldType = $field->getType();

                if ($fieldName === $fieldType && $field->isDefault()) {
//                   Nothing to add (default fields)
                } elseif ($fieldName !== $fieldType && $field->isDefault()) {
                    $createdFields[] = $fieldType.'.mapOnProperty = '.str_replace(' ','',lcfirst(ucwords(str_replace('_',' ',$fieldName))));
                } elseif ($field->exist()) {
                    $createdFields[] = strtolower($name).'_'.$fieldName.'.mapOnProperty = '.str_replace(' ','',lcfirst(ucwords(str_replace('_',' ',$fieldName))));
                } else {
                    throw new InvalidArgumentException('Field "' . $fieldType . '" does not exist.2');
                }
            }

            return  implode('
            ', $createdFields);
        }
    }

    /**
     * @param null $recordType
     * @return string
     * Return TypoScript Mapping (format string)
     */
    public function getTypoScriptMapping($recordType = null)
    {
        $mappingFields = $this->addFieldsToTypoScriptMapping();
        $table = $this->render->getTable();
        $pathToModel = $this->render->getModelNamespace();
        if (empty($recordType)) {
            $recordType =
                str_replace('_', '', $this->render->getExtensionName()) .
                '_' .
                strtolower($this->render->getName()) .
                '_' . strtolower(
                    end(
                        explode('\\', $pathToModel)
                    )
                );
        }

        $template[] = '     ' . $pathToModel . ' {';
        $template[] = '        mapping {';
        $template[] = '          tableName = ' . $table;
        $template[] = '          recordType = ' . $recordType;
        if ($mappingFields) {
            $template[] = '          columns {';
            $template[] = '            ' . $mappingFields;
            $template[] = '          }';
        }

        $template[] = '        }';
        $template[] = '      }';

        return implode("\n", $template);
    }

    public function inlineMapping()
    {
        $extensionName = $this->render->getExtensionName();

        GeneralCreateCommandUtility::importStringInToFileAfterString(
            'public/typo3conf/ext/' . $extensionName . '/ext_typoscript_setup.typoscript',
            [
                ' ' . $this->getTypoScriptMapping() . "\n"
            ],
            'config.tx_extbase {',
            2
        );
    }

    public function pageTypeTypoScriptRegister()
    {
        $extensionName = $this->render->getExtensionName();
        $pageTypeName = $this->render->getName();
        $modelNameSpace = $this->render->getModelNamespace();
        GeneralCreateCommandUtility::importStringInToFileAfterString(
            'public/typo3conf/ext/' . $extensionName . '/ext_typoscript_setup.typoscript',
            [
                $this->getTypoScriptMapping('{$PAGE_DOKTYPE_' . strtoupper($pageTypeName) . '}') . " \n"
            ],
            'config.tx_extbase {',
            2
        );

        GeneralCreateCommandUtility::importStringInToFileAfterString(
            'public/typo3conf/ext/' . $extensionName . '/ext_typoscript_setup.typoscript',
            [
                "          " . $modelNameSpace . "\\" . $pageTypeName . " = " . $modelNameSpace . "\\" . $pageTypeName. " \n"
            ],
            'Digitalwerk\DwPageTypes\Domain\Model\DefaultPage {',
            5
        );
    }
}
