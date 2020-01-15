<?php
namespace Digitalwerk\ContentElementRegistry\Command;

use Digitalwerk\ContentElementRegistry\Utility\CreateContentElementUtility;
use Digitalwerk\ContentElementRegistry\Utility\GeneralCreateCommandUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateContentElementBasic
 * @package Digitalwerk\ContentElementRegistry\Command
 */
class CreateContentElementBasic extends Command
{

    protected function configure()
    {
        $this->setDescription('Create basic content element.');
        $this->addArgument('name', InputArgument::REQUIRED,'Enter name of CE.');
        $this->addArgument('title', InputArgument::REQUIRED,'Enter title of new CE.');
        $this->addArgument('description', InputArgument::REQUIRED,'Enter description of new CE.');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $CeClass = "public/typo3conf/ext/dw_boilerplate/Classes/ContentElement/".$input->getArgument('name').".php";
        $CeClassContent = '<?php
declare(strict_types=1);
namespace Digitalwerk\DwBoilerplate\ContentElement;

use Digitalwerk\ContentElementRegistry\ContentElement\AbstractContentElementRegistryItem;

/**
 * Class '.$input->getArgument('name').'
 * @package Digitalwerk\DwBoilerplate\ContentElement
 */
class '.$input->getArgument('name').' extends AbstractContentElementRegistryItem
{
    /**
     * @var array
     */
    protected $columnsMapping = [
      
    ];

    /**
     * '.$input->getArgument('name').' constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();
        $this->addPalette(
            \'default\',
            \'title,\'
        );
    }

    /**
     * @return array
     */
    public function getColumnsOverrides()
    {
        return [
        
        ];
    }
}';
        $CeTemplate = "public/typo3conf/ext/dw_boilerplate/Resources/Private/Templates/ContentElements/".$input->getArgument('name').".html";
        $CeTemplateContent = '<html xmlns="http://www.w3.org/1999/xhtml" lang="en"
      xmlns:f="http://typo3.org/ns/TYPO3/Fluid/ViewHelpers"
      xmlns:v="http://typo3.org/ns/FluidTYPO3/Vhs/ViewHelpers"
      data-namespace-typo3-fluid="true">

<f:layout name="ContentElements/{contentElement.layout}" />

<f:section name="Main">
    <h1>{contentElement.title}</h1>
</f:section>

<f:section name="Preview">
    <h1>{contentElement.title}</h1>
</f:section>

</html>
';
        $CeIcon = "public/typo3conf/ext/dw_boilerplate/Resources/Public/Icons/ContentElement/dwboilerplate_".strtolower($input->getArgument('name')).".svg";
        $CePreviewImage = "public/typo3conf/ext/dw_boilerplate/Resources/Public/Images/ContentElementPreviews/common_dwboilerplate_".strtolower($input->getArgument('name')).".png";

        $CeModel = "public/typo3conf/ext/dw_boilerplate/Classes/Domain/Model/ContentElement/".$input->getArgument('name').".php";
        $CeModelContent = '<?php
declare(strict_types=1);
namespace Digitalwerk\DwBoilerplate\Domain\Model\ContentElement;

use Digitalwerk\ContentElementRegistry\Domain\Model\ContentElement;
use Digitalwerk\DwBoilerplate\Traits\ContentElement\TitleTrait;

/**
 * Class '.$input->getArgument('name').'
 * @package Digitalwerk\DwBoilerplate\Domain\Model\ContentElement
 */
class '.$input->getArgument('name').' extends ContentElement
{
    use TitleTrait;
}';



        file_put_contents($CeClass, $CeClassContent);
        file_put_contents($CeTemplate, $CeTemplateContent);
        file_put_contents($CeModel, $CeModelContent);
        copy("public/typo3conf/ext/content_element_registry/Resources/Public/Icons/CEDefaultIcon.svg", $CeIcon);
        copy("public/typo3conf/ext/dw_boilerplate/Resources/Public/Images/dummy.jpg", $CePreviewImage);
        GeneralCreateCommandUtility::addTitleToTranslation(
            'public/typo3conf/ext/dw_boilerplate/Resources/Private/Language/locallang_db.xlf',
            'tt_content.dwboilerplate_'. strtolower($input->getArgument('name')) . '.title',
            $input->getArgument('title')
        );
        GeneralCreateCommandUtility::addTitleToTranslation(
            'public/typo3conf/ext/dw_boilerplate/Resources/Private/Language/locallang_db.xlf',
            'tt_content.dwboilerplate_'. strtolower($input->getArgument('name')) . '.description',
            $input->getArgument('description')
        );
        $output->writeln('<bg=green;options=bold>Content element ' . $input->getArgument('name') . ' was created.</>');
        $output->writeln('<bg=red;options=bold>• Fill template: public/typo3conf/ext/dw_boilerplate/Resources/Private/Templates/ContentElements</>');
        $output->writeln('<bg=red;options=bold>• Change Content element Icon.</>');
        $output->writeln('<bg=red;options=bold>• Change Content element Preview image.</>');
    }
}
