<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(
    function () {
        ExtensionManagementUtility::allowTableOnStandardPages('tx_contentelementregistry_domain_model_relation');
    }
);
