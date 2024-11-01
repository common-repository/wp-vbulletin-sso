<?php

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $wvssoVbConfig;

if (defined('WVSSO_FORUM_PATH')) {
    chdir(WVSSO_FORUM_PATH);

    global $config, $vbulletin;
    require_once('./includes/config.php');

    require_once('./global.php');
    require_once(DIR . '/includes/functions_login.php');

    $$wvssoVbConfig = $config;
}

//Fix: some plugins (Disqus e.g.) doesn't work. see #3565
unset($_POST['ajax']);

//3 - id of "Users Awaiting Email Confirmation" group by default
define('WVSSO_VBULLETIN_USERS_AWAITING_CONFIRMATION_GROUP_ID', 3);

/**
 * Updates user data: email and password
 * @SuppressWarnings(PHPMD.EvalExpression)
 * @SuppressWarnings(PHPMD.StaticAccess)
 *
 * @param string $oldEmail    old email
 * @param string $newEmail    new email
 * @param string $newPassword new password
 * @param string $wpUsername
 *
 * @return mixed
 */
function wvsso_vb_update_user($oldEmail, $newEmail, $newPassword, $wpUsername = '')
{
    global $vbulletin;
    wvsso_log_info(
        "vBulletin update user stated: wp user name: '$wpUsername', email: '$oldEmail', new email: '$newEmail'"
    );
    $userdata = wvsso_vb_load_user($oldEmail);
    if (!$userdata) {
        wvsso_log_error(sprintf(WVSSO_UPDATE_USER_ERROR_TEXT, $oldEmail));

        return false;
    }

    if (empty($newEmail) && empty($newPassword)) {
        wvsso_log_warning(
            "vBulletin update user: user email '$oldEmail', Nothing to update, email and password is empty!"
        );

        return false;
    }

    if ('' !== $newPassword) {
        wvsso_log_info("vBulletin update user: '$oldEmail', set new password.");
        $userdata->set('password', $newPassword);
    }
    if ('' !== $newEmail) {
        wvsso_log_info("vBulletin update user: '$oldEmail', set new email: '$newEmail'.");
        $userdata->set('email', $newEmail);
    }

    wvsso_check_username($wpUsername, $userdata->existing['username'], 'Update');
    wvsso_vb_save_user($userdata);

    $userdata = datamanager_init('user', $vbulletin, ERRTYPE_STANDARD);
    $userdata->set_existing($vbulletin->userinfo);

    $vbulletin->GPC['newpassword'] = $newPassword;
    $vbulletin->GPC['email'] = $newEmail;

    ($hook = vBulletinHook::fetch_hook('profile_updatepassword_complete')) ? eval($hook) : false;
}

/**
 * Logs user out from vBulletin
 */
function wvsso_vb_logout()
{
    process_logout();
}

/**
 * Delete user out from vBulletin DB
 */
function wvsso_vb_delete($email, $wpUsername)
{
    wvsso_log_info("vBulletin delete user: '$email'.");
    $userinfo = wvsso_vb_load_user($email);

    if (!$userinfo) {
        wvsso_log_error(sprintf(WVSSO_DELETE_USER_ERROR_TEXT, $email));

        return false;
    }

    wvsso_check_username($wpUsername, $userinfo->existing['username'], 'Delete');

    if (!$userinfo->delete()) {
        if (empty($userinfo->errors)) {
            wvsso_log_error(sprintf(WVSSO_DELETE_USER_UNKNOWN_ERROR_TEXT, $email));
        }
        foreach ($userinfo->errors as $error) {
            wvsso_log_error($error);
        }
    }

    unset($userinfo);
}

/**
 * Logs user in to vBulletin
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 *
 * @param string $email
 * @param bool   $isRemember
 * @param string $username
 *
 * @return array|bool
 */
function wvsso_vb_login($email, $username, $isRemember = false)
{
    global $vbulletin;
    $user = $vbulletin->userinfo; // object exists for both guest and authenticated user always.

    if ($user['email'] != $email) {
        $userid = wvsso_get_userid_from_email($email);

        if ($userid === false) {
            wvsso_log_error(sprintf(WVSSO_LOGIN_USER_ERROR_TEXT, $email));

            return false;
        }

        $userinfo = wvsso_vb_load_user($email);

        if (!$userinfo) {
            return false;
        }

        $vbulletin->userinfo = $userinfo->existing;

        wvsso_check_username($username, $userinfo->existing['username']);

        if (wvsso_is_error_data_item($vbulletin->userinfo)) {
            return ['error' => $vbulletin->userinfo];
        }
        if ($user['userid'] != $vbulletin->userinfo['userid']) {
            vbsetcookie('userid', $vbulletin->userinfo['userid'], true, true, true);
            vbsetcookie('password', md5($vbulletin->userinfo['password'] . COOKIE_SALT), true, true, true);
            exec_unstrike_user($vbulletin->userinfo['username']);

            $logintype = ($vbulletin->userinfo['usergroupid'] == '6') ? 'cplogin' : '';
            process_new_login($logintype, $isRemember, true);
        }
    }
}

/**
 * Adds user in to vBulletin
 * @SuppressWarnings(PHPMD.EvalExpression)
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @global type  $vbulletin
 *
 * @param string $username
 * @param string $email
 * @param string $password
 * @param bool   $isActivate        false - if activation on WP side needed, true - if registration process called from
 *                                  wp-admin or Email Confirmation option is disabled
 *
 * @return mixed
 */
function wvsso_vb_user_register($username, $email, $password, $isActivate)
{
    global $db, $vbulletin;
    wvsso_log_info(
        "vBulletin register new user: '$username', email: '$email', registered: " . (int)$isActivate
    );

    $usergroupId =
        $isActivate ? wvsso_get_registered_user_group() : WVSSO_VBULLETIN_USERS_AWAITING_CONFIRMATION_GROUP_ID;
    wvsso_log_info("vBulletin register new user: user group id: $usergroupId");
    $userdata = datamanager_init('User', $vbulletin, ERRTYPE_ARRAY);
    $userdata->set('username', $username);
    $userdata->set('email', $email);
    $userdata->set('password', $password);
    $userdata->set('usergroupid', $usergroupId);
    $memberGroups = wvsso_get_additional_member_groups();
    if ($memberGroups) {
        $userdata->set('membergroupids', $memberGroups);
    }
    $userid = wvsso_vb_save_user($userdata);
    $userinfo = fetch_userinfo($userid);
    $vbulletin->userinfo = $userinfo;

    ($hook = vBulletinHook::fetch_hook('register_addmember_complete')) ? eval($hook) : false;

    return $userdata;
}

function wvsso_get_registered_user_group()
{
    //2 - id of "Registered users" group on Forum side by default
    $groupId = 2;

    if (!empty(get_option(WVSSO_FORUM_REGISTERED_USERS_GROUP_ID))) {
        $groupId = (int)get_option(WVSSO_FORUM_REGISTERED_USERS_GROUP_ID);
    }

    return $groupId;
}

function wvsso_get_additional_member_groups()
{
    global $vbulletin;
    $secondaryGroupsIds = $vbulletin->options['wvsso_secondary_user_groups'];
    $secondaryGroupsIds = explode(',', $secondaryGroupsIds);
    $secondaryGroupsIds = array_map('trim', $secondaryGroupsIds); // For 38, 39, 45
    $secondaryGroupsIds = array_filter($secondaryGroupsIds);
    $memberGroups = [];
    if ($secondaryGroupsIds) {
        $vbulletinGroupsIds = array_column($vbulletin->usergroupcache, 'title', 'usergroupid');
        foreach ($secondaryGroupsIds as $secondaryGroupId) {
            if (isset($vbulletinGroupsIds[$secondaryGroupId])) {
                $memberGroups[] = $secondaryGroupId;
                wvsso_log_info("Added secondary group '$vbulletinGroupsIds[$secondaryGroupId]' to user");
                continue;
            }
            // Error secondary group not found in vbulletin groups
            wvsso_log_error(
                "Register user action: Secondary group id '$secondaryGroupId' not found in vbulletin groups"
            );
        }
    }

    return array_filter($memberGroups);
}

/**
 * Assign user to "Registered users" group after his activation on WP
 *
 * @param string $email
 *
 * @return boolean
 */
function wvsso_vb_activate_user($email)
{
    $userdata = wvsso_vb_load_user($email);

    if (!$userdata) {
        wvsso_log_error(sprintf(WVSSO_ACTIVATE_USER_ERROR_TEXT, $email));

        return false;
    }

    wvsso_log_info("Activation process started. Email: $email");

    $isSet = $userdata->set('usergroupid', strval(wvsso_get_registered_user_group()));
    if (!$isSet && !$userdata->setfields['usergroupid'] && empty($userdata->errors)) {
        wvsso_log_error('Could not set `usergroupid` to activate. Email: ' . $email);

        return false;
    }
    wvsso_vb_save_user($userdata);
}

function wvsso_vb_load_user($email)
{
    global $vbulletin;

    $userid = wvsso_get_userid_from_email($email);
    $userdata = datamanager_init('User', $vbulletin, ERRTYPE_ARRAY);

    $userinfo = fetch_userinfo($userid);

    if (!is_array($userinfo)) {
        wvsso_log_error(sprintf(WVSSO_LOAD_USER_ERROR_TEXT, $email));

        return false;
    }

    $userdata->set_existing($userinfo);

    return $userdata;
}


/**
 * Returns user id from vBulletin by email
 * @global type  $vbulletin
 * @global type  $config
 *
 * @param string $email
 *
 * @return int|false
 */
function wvsso_get_userid_from_email($email)
{
    global $vbulletin, $wvssoVbConfig;

    $vbulletin->db->sql = "SELECT userid FROM " . $wvssoVbConfig['Database']['tableprefix'] . "user WHERE email = '"
        . $vbulletin->db->escape_string(trim($email)) . "'";
    $result = $vbulletin->db->execute_query(true, $vbulletin->db->connection_recent);

    $user = $vbulletin->db->fetch_array($result);
    if (!$user) {
        wvsso_log_error("vBulletin get user id: $email, user not found!");

        return false;
    }

    return $user['userid'];
}

function wvsso_vb_save_user($userdata)
{
    $userdata->pre_save();
    if (!empty($userdata->errors)) {
        wvsso_log_error(WVSSO_SAVE_USER_ERROR_TEXT);
        foreach ($userdata->errors as $error) {
            wvsso_log_error($error);
        }

        return false;
    }

    return $userdata->save();
}

function wvsso_is_error_data_item($data)
{
    return is_array($data) && isset($data['message']);
}

/**
 * Compares usernames from WordPress and from vBulletin and log error if they are not equal
 *
 * @param string $wpUsername
 * @param string $vbUsername
 * @param string $action Name of the action where comparison executed. 'Login', 'Update', 'Delete' etc
 *
 * @return boolean
 */
function wvsso_check_username($wpUsername, $vbUsername, $action = 'Login')
{
    if (strcasecmp($wpUsername, $vbUsername) !== 0) {
        wvsso_log_error(sprintf(WVSSO_USERNAME_DISCREPANCY_ERROR_TEXT, $action, $wpUsername, $vbUsername));

        return false;
    }

    return true;
}
