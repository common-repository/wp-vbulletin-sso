<?php

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$uploads = wp_upload_dir();
define('WVSSO_LOGGING_PATH', $uploads['basedir'] . '/sso-vbulletin-logs');
define('WVSSO_LOGGING_URL', $uploads['baseurl'] . '/sso-vbulletin-logs');
