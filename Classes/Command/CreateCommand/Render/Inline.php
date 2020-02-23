<?php
namespace Digitalwerk\ContentElementRegistry\Command\CreateCommand\Render;

use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Render;
use Digitalwerk\ContentElementRegistry\Utility\FieldsUtility;
use Digitalwerk\ContentElementRegistry\Utility\GeneralCreateCommandUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Inline
 * @package Digitalwerk\ContentElementRegistry\Command\CreateCommand\Render
 */
class Inline
{
    /**
     * @var Render
     */
    protected $render = null;

    public function __construct(Render $render)
    {
        $this->render = $render;
    }

    public function render()
    {
        $fields = $this->render->getFields();
        if (!empty($fields)) {
            $extensionName = $this->render->getExtensionName();
            $name = $this->render->getName();
            $staticName = $this->render->getStaticName();

            if ($this->render->getDeepLevel() === 0) {
                $table = $this->render->getTable();
            } else {
                $table = 'tx_contentelementregistry_domain_model_relation';
            }

            $this->render->setDeepLevel($this->render->getDeepLevel() + 1);

            foreach ($fields->getFields() as $field) {
                if ($field->isInlineItemsAllowed()) {
                    $firstFieldItemName = $field->getFirstItem()->getName();
                    $firstFieldItemType = $field->getFirstItem()->getType();

//                    add constant
                    GeneralCreateCommandUtility::importStringInToFileAfterString(
                        'public/typo3conf/ext/' . $extensionName . '/Classes/ContentElement/' . $staticName . '.php',
                        ['   const CONTENT_RELATION_' . strtoupper($firstFieldItemName) . ' = \'' . str_replace('_', '', $extensionName) . '_' . strtolower($staticName) . '_' . strtolower($firstFieldItemName) . '\';' . "\n"],
                        [
                            'class ' . $staticName . ' extends AbstractContentElementRegistryItem',
                            '{'
                        ]
                    );

                    $newRender = GeneralUtility::makeInstance(Render::class);
                    $newRender->setFields(
                        GeneralUtility::makeInstance(FieldsUtility::class)->generateObject(
                            $this->render->getInlineFields()[$firstFieldItemType],
                            $this->render->getTable()
                        )
                    );
                    $newRender->setExtensionName($this->render->getExtensionName());
                    $newRender->setInlineRelativePath($this->render->getInlineRelativePath() . '/' .  $name);
                    $newRender->setName($firstFieldItemName);
                    $newRender->setTable($table);
                    $newRender->setStaticName($this->render->getStaticName());
                    $newRender->setInlineFields($this->render->getInlineFields());
                    $newRender->setModelNamespace($this->render->getModelNamespace() . '\\' . $name);
                    $newRender->setRelativePathToClass($this->render->getRelativePathToClass());
                    $newRender->setOutput($this->render->getOutput());
                    $newRender->setInput($this->render->getInput());

                    $newRender->model()->contentElementAndInlinetemplate();
                    $newRender->tca()->inlineTemplate();

                    $newRender->translation()->addFieldsTitleToTranslation(
                        'public/typo3conf/ext/' . $extensionName . '/Resources/Private/Language/locallang_db.xlf'
                    );
                    $newRender->typoScript()->inlineMapping();
                    $newRender->icon()->copyAndRegisterInlineDefaultIcon();
                    $newRender->sqlDatabase()->inlineFields($firstFieldItemType);

                    $newRender->inline()->render();
                }
            }
        }
    }
}
