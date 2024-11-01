<?php

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * @SuppressWarnings(PHPMD.Superglobals)
 * @SuppressWarnings(PHPMD.ExitExpression)
 */
function wvsso_check_login_ajax()
{
    if (isset($_REQUEST['action']) && 'wvsso_check_login' === $_REQUEST['action'] && '' !== $_REQUEST['username']) {
        $errorStatus = 'error';
        $okStatus = 'ok';

        $status = '';
        $message = '';

        $username = $_REQUEST['username'];

        if (!wvsso_check_illegal_chars($username)) {
            $status = $errorStatus;
            $message = sprintf(WVSSO_ERROR_ILLEGAL_CHARS_TEXT, get_option(WVSSO_OPTION_NAME_ILLEGAL_CHARS));
        }
        if (strlen($username) > 25) {
            $status = $errorStatus;
            $message = WVSSO_ERROR_25_CHARS_TEXT;
        }
        if (false === username_exists($username) && !WVSSO_signup_get_user_by_username($username)) {
            $status = $okStatus;
            $message = WVSSO_USERNAME_VALID_MESSAGE;
        }

        echo(json_encode(['status' => $status, 'msg' => $message]));
    }
    exit();
}

//Extra functions for SignUp process
function wvsso_signup_get_user_by_username($username)
{
    global $wpdb;

    $username = sanitize_user($username);
    $sqlResult = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "signups WHERE user_login = %s", $username),
        ARRAY_A
    );

    return $sqlResult ? $sqlResult : false;
}

function wvsso_check_illegal_chars($username)
{
    $illegalChars = preg_split('/[ \r\n\t]+/', get_option(WVSSO_OPTION_NAME_ILLEGAL_CHARS), -1, PREG_SPLIT_NO_EMPTY);

    foreach ($illegalChars as $char) {
        if (strpos(strtolower($username), strtolower($char)) !== false) {
            return false;
        }
    }

    return true;
}
