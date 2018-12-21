<?php
namespace Digitalwerk\ContentElementRegistry\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ContentElementRegistryUtility
 * @package Digitalwerk\ContentElementRegistry\Utility
 */
class ContentElementRegistryUtility
{

    /**
     * Converts given array to TypoScript
     *
     * @param array $typoScriptArray The array to convert to string
     * @param string $addKey Prefix given values with given key (eg. lib.whatever = {...})
     * @param integer $tab Internal
     * @param boolean $init Internal
     * @return string TypoScript
     */
    public static function convertArrayToTypoScript(array $typoScriptArray, $addKey = '', $tab = 0, $init = true)
    {
        $typoScript = '';
        if ($addKey !== '') {
            $typoScript .= str_repeat("\t", ($tab === 0) ? $tab : $tab - 1) . $addKey . " {\n";
            if ($init === true) {
                $tab++;
            }
        }
        $tab++;
        foreach ($typoScriptArray as $key => $value) {
            if (!is_array($value)) {
                if (GeneralUtility::isFirstPartOfStr($value, ":=") === true) {
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
            $tab--;
            $typoScript .= str_repeat("\t", ($tab === 0) ? $tab : $tab - 1) . '}';
            if ($init !== true) {
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
     * @return array|mixed
     * @throws \ReflectionException
     */
    public static function getNamespaceConfiguration($class, $key = null)
    {
        list($vendorName, $extensionName, $modelName) = GeneralUtility::trimExplode('\\', $class);
        $data = [
            'vendorName'    => $vendorName,
            'extensionName' => $extensionName,
            'modelName'     => $modelName,
        ];

        if (null !== $key and \array_key_exists($key, $data)) {
            return $data[$key];
        }

        return $data;
    }
}
