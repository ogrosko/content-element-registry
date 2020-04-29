<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

(static function () {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_contentelementregistry_domain_model_relation');
})();
