<?php

declare(strict_types=1);

namespace Digitalwerk\ContentElementRegistry\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ContentElementRegistryUtility
 */
class ContentElementRegistryUtility
{
    /**
     * Converts given array to TypoScript
     *
     * @param array $typoScriptArray The array to convert to string
     * @param string $addKey Prefix given values with given key (eg. lib.whatever = {...})
     * @param int $tab Internal
     * @param bool $init Internal
     *
     * @return string TypoScript
     */
    public static function convertArrayToTypoScript(array $typoScriptArray, $addKey = '', $tab = 0, $init = true)
    {
        $typoScript = '';
        if ($addKey !== '') {
            $typoScript .= str_repeat("\t", ($tab === 0) ? $tab : $tab - 1) . $addKey . " {\n";
            if ($init) {
                ++$tab;
            }
        }
        ++$tab;
        foreach ($typoScriptArray as $key => $value) {
            if (!is_array($value)) {
                if (GeneralUtility::isFirstPartOfStr($value, ':=')) {
                    $typoScript .= str_repeat("\t", ($tab === 0) ? $tab : $tab - 1) . "$key $value\n";
                } elseif (strpos($value, "\n") === false) {
                    $typoScript .= str_repeat("\t", ($tab === 0) ? $tab : $tab - 1) . "$key = $value\n";
                } else {
                    $typoScript .= str_repeat("\t", ($tab === 0) ? $tab : $tab - 1) . "$key (\n$value\n" . str_repeat("\t", ($tab === 0) ? $tab : $tab - 1) . ")\n";
                }
            } else {
                $typoScript .= self::convertArrayToTypoScript($value, $key, $tab, false);
            }
        }
        if ($addKey !== '') {
            --$tab;
            $typoScript .= str_repeat("\t", ($tab === 0) ? $tab : $tab - 1) . '}';
            if (!$init) {
                $typoScript .= "\n";
            }
        }

        return $typoScript;
    }

    /**
     * Gets namespace information
     *
     * @param string $class
     * @param string $key
     *
     * @return array|mixed
     *
     * @throws \ReflectionException
     */
    public static function getNamespaceConfiguration($class, $key = null)
    {
        [$vendorName, $extensionName, $modelName] = GeneralUtility::trimExplode('\\', $class);
        $data = [
            'vendorName' => $vendorName,
            'extensionName' => $extensionName,
            'modelName' => $modelName,
        ];

        if (null !== $key and \array_key_exists($key, $data)) {
            return $data[$key];
        }

        return $data;
    }

    /**
     * Convert camelCaseString to camel-case-dashed-string
     *
     * @param string $string
     *
     * @return string
     */
    public static function camelCase2Dashed(string $string): string
    {
        return strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $string));
    }

    /**
     * SVG icon registration helper
     *
     * @param array $icons
     * @param string $extKey
     */
    public static function registerIcons(array $icons, string $extKey): void
    {
        $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
        foreach ($icons as $icon) {
            $explode = explode('/', $icon);
            $iconName = stripos($icon, '/') === false ? $icon : end($explode);
            $iconRegistry->registerIcon(
                $iconName,
                \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
                ['source' => "EXT:{$extKey}/Resources/Public/Icons/{$icon}.svg"]
            );
        }
    }
}
