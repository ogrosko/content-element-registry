<?php

$contentElementsRegistry = \Digitalwerk\ContentElementRegistry\Core\ContentElementRegistry::getInstance();

return array_reduce($contentElementsRegistry->getContentElements(), static function ($carry, $item) {
    return array_merge($carry, $item->getPersistenceConfig());
}, $contentElementsRegistry->getBasePersistenceConfig());