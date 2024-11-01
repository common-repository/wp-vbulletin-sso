=== WP vBulletin SSO ===

Contributors: extremeidea
Tags: sso, single sign-on, login, registration, user management, authentication, vbulletin, bridge
Requires at least: 4.4
Tested up to: 5.5
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=LM25KRQVLRLDS 
Contact Us: https://www.extreme-idea.com/

== Description ==

Looking for SSO tool for your WordPress and vBulletin sites?

Try WP vBulletin SSO module for FREE.

WP vBulletin SSO consists of two synchronization vBulletin and WordPress lightweight extensions, where WordPress holds the master users database and all the user-related operations are managed there. The solution does migrate the users data from vBulletin to WordPress (email, username) only. It does not migrate password and other user-related data like avatars, or MailChimp settings, Facebook users, other fields like first or last name etc. Only email, password, username is synced.

The plugin is developed and supported by <a href="https://www.extreme-idea.com/">Extreme Idea LLC</a>. Our entire team is ready to help you. Ask your questions in the support forum, or <a href="https://www.extreme-idea.com/contact-us/">contact us directly</a>.

== Installation == 

To Install the SSO plugin on WordPress: 
1. Log in as administrator to WordPress Admin Panel. 
2. Navigate to Plugins > press Add New button > press Upload Plugin button. 
3. Browse for sso vbulletin.zip file > press Install Now button. 
4. The plugin should be successfully installed (navigate to Plugins > press Installed Plugins button > navigate to the WordPress vBulletin SSO extension). 

To Install the SSO extension on vBulletin site: 
1. Log in to forum’s /admincp/ Control Panel as administrator: 
2. Navigate to Plugins & Products section. 
3. Expand section and click on the Manage Products link. 
4. Scroll down right frame until you find Add/Import Product link. 
5. Click on the link and choose for sso vbulletin.xml file (extract the file from the archive). 
6. Click on the Import button. 
7. Change vBulletin Login Link: Log in as vBulletin Administrator → Open Styles and Templates → Add next changes to the current theme:

Comment or remove next lines out in template `header`: <form id="navbar_loginform" ... till </form>. 
Paste the link right after the commented out form: <a rel="nofollow" class="guest-login" href="{vb:raw vboptions.sso_login_url}">Login</a> (or simply replace all code with the last link of this form).

== Uninstallation ==

To Uninstall the SSO extension: 
1. Log in as WordPress administrator to WordPress Admin Panel: 
2. Navigate to Plugins > press Installed Plugins button > navigate to the SSO vBulletin extension. 
3. Press Deactivate button. 
4. Press Delete button. The plugin should be successfully deleted. 

To Uninstall the extension via the vBulletin dashboard: 
1. Log in to your forum’s /admincp/ control panel as administrator. 
2. Navigate to the Plugins & Products section. 
3. Expand section and click on the Manage Products link. 
4. Find vBulletin SSO extension and select Uninstall it. 

== Upgrade Notice ==

To update the plugin:

1. Log in as administrator to Admin Panel.
2. Uninstall the plugin (see Uninstall chapter).
3. (Re)install the plugin (see Install chapter).
The plugin should be successfully re-installed.

== Configuration ==

To open WordPress plugin`s settings page: Log in as WordPress administrator > Settings > SSO vBulletin.

Here you can:
* Enable / Disable Email Notification (by default this features is disabled).
* Set Email Address(es) for Email Notifications.
* Set Illegal User names and characters.

To open vBulletin plugin`s settings page navigate to: Settings > Options > SSO vBulletin.

There are available next redirection fields:

* “Login Url” field - enter the URL you would like to be redirected to (after Login button is pressed).
* “Register Url” field - enter the URL you would like to be redirected to (after Register button is pressed).
* “Lost Password Url ”field - enter the URL you would like to be redirected to (after Lost Password button is pressed).
* “Change Password and Email Url” field - enter the URL you would like to be redirected to (after Change Password and Email button is pressed).

== Error Log ==

Errors are stored at WORDPRESS_ROOT/wp-content/uploads/sso-vbulletin-logs

== Changelog

= 1.3.5 =  2019-02-26

* Fixed vBullettin config variable.

= 1.3.4 = 2019-02-15

* Fixed username validation.

= 1.3.3 = 2019-01-23

* Defect #6193: Plugin logged error message if secondary group isn't set: Register user action: Secondary group id '' not found in vbulletin groups.

= 1.3.2 = 2018-12-18

* Support for Gutenberg Editor (available in WordPress 5.0.1).

= 1.3.1 = 2018-12-14

* Renamed the official plugin name to WP vBulltin SSO.

= 1.3.0 = 2018-12-10

* Added a possibility to add Primary User Group: admin can specify category ID to synchronize newly registered users with this group(s).
* Renamed the official plugin name to WordPress vBulltin SSO.

= 1.2.0 = 2018-10-25

* Added Secondary User Groups feature: Admin can specify category ID to synchronize newly registered users with this group(s).
* Fixed an issue during adding new user by Admin (via wp-admin).

= 1.1.2 = 2018-12-03

* Fixed plugin`s version.

= 1.1.1 = 2018-12-03

* Fixed an issue when user`s primary usergroup is "Users Awaiting Email Confirmation" after email confirmation via Admin Panel.

= 1.1.0 = 2018-19-02

* Changed logger instance, added log section to a settings page.

= 1.0.4 = 2017-12-01

* Fixed an error during profile update if user has an empty character in user name.

= 1.0.3 = 2017-12-01

* Fixed an error during reset password.

= 1.0.2 = 2017-06-07

* Added unique function names, defines, and classnames.
* Changed the place of saving its files (outside of the plugins folder).  
* Vanished the Hardcode.

= 1.0.1 =  2017-05-29

* Changed the plugin name.
* Renamed function names, defines, and classnames.
* Added sanitization, escape, and validation to plugin POST calls.

= 1.0.0 = 2017-05-18

* First release.

<a href=" https://www.extreme-idea.com/plugins/wp_vbulletin_sso">More info about the plugin</a>
