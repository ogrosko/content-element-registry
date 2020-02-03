<?php
namespace Digitalwerk\ContentElementRegistry\Command\CreateCommand;

use Digitalwerk\ContentElementRegistry\Command\CreateCommand\Config\TCAFieldTypes;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class DefaultFieldTypes
 * @package Digitalwerk\ContentElementRegistry\Command\CreateCommand
 */
class DefaultFieldTypes extends Command
{

    protected function configure()
    {
        $this->addArgument('table', InputArgument::REQUIRED,'Enter table name.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = $input->getArgument('table');
        $defaultFieldTypes = array_keys(GeneralUtility::makeInstance(TCAFieldTypes::class)->getDefaultTCAFieldTypes($table, '','',true));
        print_r($defaultFieldTypes);
    }
}
