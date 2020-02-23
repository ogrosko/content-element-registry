<?php
namespace Digitalwerk\ContentElementRegistry\Command\CreateCommand\Render;

use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Render;
use Digitalwerk\ContentElementRegistry\Utility\GeneralCreateCommandUtility;
use Symfony\Component\Console\Exception\InvalidArgumentException;

/**
 * Class SQLDatabase
 * @package Digitalwerk\ContentElementRegistry\Command\CreateCommand\Render
 */
class SQLDatabase
{
    /**
     * @var Render
     */
    protected $render = null;

    /**
     * SQLDatabase constructor.
     * @param Render|null $render
     */
    public function __construct(? Render $render)
    {
        $this->render = $render;
    }

    /**
     * @var array
     */
    protected $dataTypes = [
        'int' => 'int(11) DEFAULT 0 NOT NULL',
        'varchar255' => 'varchar(255) DEFAULT \'\' NOT NULL',
        'text' => 'text',
    ];

    /**
     * @return array
     */
    public function getDataTypes(): array
    {
        return $this->dataTypes;
    }

    /**
     * @return mixed
     */
    public function getIntDataType()
    {
        return $this->getDataTypes()['int'];
    }

    /**
     * @return mixed
     */
    public function getVarchar255DataType()
    {
        return $this->getDataTypes()['varchar255'];
    }

    /**
     * @return mixed
     */
    public function getTextDataType()
    {
        return $this->getDataTypes()['text'];
    }

    /**
     * @return string
     * Return CE sql table fields (format string)
     */
    public function getSqlFields()
    {
        $fields = $this->render->getFields();

        if ($fields) {
            $result = [];
            $name = $this->render->getName();
            foreach ($fields->getFields() as $field) {
                $fieldName = $field->getName();
                $fieldType = $field->getType();
                $items = $field->getItems();

                if ($field->exist()) {
                    if ($field->hasSqlDataType()) {
                        if (!self::isAllItemsNumeric($items)) {
                            $result[] = strtolower($name) . '_' . $fieldName.' ' . $this->getVarchar255DataType();
                        } else {
                            $result[] = strtolower($name) . '_' . $fieldName.' ' . $field->getSqlDataType();
                        }
                    }
                } else {
                    throw new InvalidArgumentException('Field "' . $fieldType . '" does not exist.3');
                }
            }

            return implode(",\n    ", $result);
        }
    }

    /**
     * @param $items
     * @return mixed
     */
    public function isAllItemsNumeric($items)
    {
        foreach ($items as $item) {
            if (!is_numeric($item->getValue())) {
                return false;
                break;
            }
        }

        return true;
    }

    /**
     * @param $fieldType
     */
    public function inlineFields($fieldType)
    {
        $extensionName = $this->render->getExtensionName();

        if ((!empty($this->render->getInlineFields()[$fieldType])) && !$this->render->getFields()->areDefault()) {
            GeneralCreateCommandUtility::importStringInToFileAfterString(
                'public/typo3conf/ext/' . $extensionName . '/ext_tables.sql',
                [
                    '    ' . $this->getSqlFields(). ", \n"
                ],
                [
                    "# Table structure for table 'tx_contentelementregistry_domain_model_relation'",
                    "#",
                    "CREATE TABLE tx_contentelementregistry_domain_model_relation (",
                ]
            );
            $output = $this->render->getOutput();
            $output->writeln('<bg=red;options=bold>• Update/Compare Typo3 database. (Inline : ' . $this->render->getName() . ')</>');
        }
    }

    public function fields()
    {
        $extensionName = $this->render->getExtensionName();
        $table = $this->render->getTable();

        if (!empty($fields) && !$fields->areDefault()) {
            GeneralCreateCommandUtility::importStringInToFileAfterString(
                'public/typo3conf/ext/' . $extensionName . '/ext_tables.sql',
                [
                    '    ' . $this->getSqlFields(). ", \n"
                ],
                [
                    "# Table structure for table '" . $table . "'",
                    "#",
                    'CREATE TABLE ' . $table . ' (',
                ]
            );
            $output = $this->render->getOutput();
            $output->writeln('<bg=red;options=bold>• Update/Compare Typo3 database.</>');
        }
    }
}
