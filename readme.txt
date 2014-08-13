=== Client Dash WP Help Add on ===

Contributors: BrashRebel
Tags: client, portal, dashboard, admin, help, tutorials, faq, documentation
Requires at least: 3.8.0
Tested up to: 3.9.2
Stable tag: 0.3.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

= What it does =

Now the awesome power of the [WP Help] (http://wordpress.org/plugins/wp-help) plugin can be harnessed and utilized within Client Dash! This plugin collects the help documents created by a source site using Mark Jaquith's super slick plugin.

= Requirements =

* Use of [WP Help] (http://wordpress.org/plugins/wp-help) plugin on another site
* [Client Dash] (http://wordpress.org/plugins/client-dash) plugin version 1.2.1 or higher

== Installation ==

Using this plugin is very simple. All you have to do is:

1. Upload the `client-dash-wp-help` folder to the `/wp-content/plugins/` directory

2. Activate the plugin through the 'Plugins' menu in WordPress


== Changelog ==

= 0.3.4 =

* Made compatible with Client Dash version 1.5

= 0.3.3 =

* Compatible with Client Dash version 1.4
* Optimized, reorganized code

= 0.3.2 =

* Fixed bug preventing setting from saving

= 0.3.1 =

* Output admin notice if `client-dash` is not active

= 0.3 =

* Register `cdwph_url` setting and render on Settings page
* Validate `cdwph_url` using `esc_url_raw` callback
* Get `cdwph_url` option in output on FAQ tab
* Display message if WP error or empty value on FAQ tab
* Link message to settings page using `cd_get_settings_url` function

= 0.2.1 =

* Output post content through `the_content` filter

= 0.2 =

* Successfully connects to source and retrieves data
* Creates FAQ tab on CD Help page
* Outputs data from source on FAQ tab in list
* Hides content for each item until heading is clicked on

= 0.1 =

* Initial version