<?php
namespace Digitalwerk\ContentElementRegistry\Listeners;

use Digitalwerk\ContentElementRegistry\ContentElement\AbstractContentElementRegistryItem;
use Digitalwerk\ContentElementRegistry\Core\ContentElementRegistry;
use Digitalwerk\ContentElementRegistry\Domain\Model\ContentElement;
use Digitalwerk\ContentElementRegistry\Traits\Injection\InjectDataMapper;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\Event\PageContentPreviewRenderingEvent;
use TYPO3\CMS\Backend\View\PageLayoutContext;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use function Symfony\Component\String\u;

/**
 * Class ContentElementPreviewRenderer
 * @package Digitalwerk\ContentElementRegistry\Listeners
 */
class ContentElementPreviewRenderer
{
    use InjectDataMapper;

    /**
     * @param PageContentPreviewRenderingEvent $event
     * @return void
     * @throws \Digitalwerk\ContentElementRegistry\Core\ContentElementRegistryException
     * @throws \ReflectionException
     */
    public function __invoke(PageContentPreviewRenderingEvent $event): void
    {
        $row = $event->getRecord();
        $itemContent = $event->getPreviewContent();

        $contentElementRegistry = ContentElementRegistry::getInstance();
        if ($contentElementRegistry->existsContentElement($row['CType'])) {
            /** @var AbstractContentElementRegistryItem $contentElement */
            $contentElement = $contentElementRegistry->getContentElement($row['CType']);
            $drawItem = false;
            $view = GeneralUtility::makeInstance(StandaloneView::class);
            $view->setPartialRootPaths(["EXT:{$contentElement->getExtensionKey()}/Resources/Private/Partials"]);
            $view->setTemplatePathAndFilename("EXT:{$contentElement->getExtensionKey()}/Resources/Private/Templates/ContentElements/{$contentElement->getTemplateName()}.html");
            $contentElementObject = $this->dataMapper->map(
                ContentElement::class,
                [$row]
            )[0];

            /** Render preview */
            $preview = $view->renderSection(
                'Preview',
                [
                    'data' => $row,
                    'contentElement' => $contentElementObject,
                    'ce' => $contentElementObject,
                ],
                true
            );

            /** Check for using layout */
            $useLayout = file_exists(
                    GeneralUtility::getFileAbsFileName(
                        "EXT:{$contentElement->getExtensionKey()}/Resources/Private/Layouts/ContentElements/Preview.html"
                    )
                ) && !u($preview)->containsAny('<!--Without layout-->');

            if ($useLayout) {
                /** Create a Standalone view instance */
                /** @var StandaloneView $layout */
                $layout = GeneralUtility::makeInstance(StandaloneView::class);
                /** Set view Template */
                $layout->setTemplatePathAndFilename(
                    "EXT:{$contentElement->getExtensionKey()}/Resources/Private/Layouts/ContentElements/Preview.html"
                );
                /** Assign rendered preview to layout */
                $layout->assign('preview', $preview);
                /** Modify core preview */
                $itemContent .= $layout->render();
            } else {
                /** Modify core preview */
                $itemContent .= $preview;
            }
        }

        $event->setPreviewContent($itemContent ?: 'test');
    }
}
