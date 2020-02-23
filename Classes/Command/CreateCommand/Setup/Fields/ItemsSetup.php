<?php
namespace Digitalwerk\ContentElementRegistry\Command\CreateCommand\Setup\Fields;

use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Run;

/**
 * Class ItemsSetup
 * @package Digitalwerk\ContentElementRegistry\Command\CreateCommand\Setup\Fields
 */
class ItemsSetup
{
    /**
     * @var Run
     */
    protected $run = null;

    /**
     * FieldsSetup constructor.
     * @param Run $run
     */
    public function __construct(Run $run)
    {
        $this->run = $run;
    }

    /**
     * @var string
     */
    protected $items = '';

    /**
     * @return string
     */
    public function getItems(): string
    {
        return $this->items;
    }

    /**
     * @param string $items
     */
    public function setItems(string $items): void
    {
        $this->items = $items;
    }


    public function createItem()
    {
        $itemName = $this->run->askItemName();
        $itemValue = $this->run->askItemValue();
        $itemTitle = $this->run->askItemTitle();

        $item = $itemName . ';' . $itemValue . ';' . $itemTitle . '*';

        $this->setItems($this->getItems() . $item);

        if ($this->run->needCreateMoreItems()) {
            $this->createItem();
        } else {
            Run::setDeepLevel(substr(Run::getRawDeepLevel(), 0, -strlen(Run::DEEP_LEVEL_SPACES)));
        }
    }
}
