<?php
/*
Plugin Name: Client Dash WP Help Add-on
Description: Integrates content from WP Help with Client Dash by displaying it on the FAQ tab under the Help page.
Version: 0.3.1
Author: Kyle Maurer
Author URI: http://realbigmarketing.com/staff/kyle
*/

// Notices for if CD is not active
function cdwph_notices() {
	if (!is_plugin_active( 'client-dash/client-dash.php' )) {
	echo '<div class="error">Client Dash WP Help Add-on requires <b>Client Dash</b>. Please install <b>Client Dash</b> to continue using.</div>';
	}
}
add_action('admin_notices', 'cdwph_notices');

// Register settings
function cdwph_register_settings() {
	register_setting('cd_options', 'cdwph_url', 'esc_url_raw');
}
add_action('admin_init', 'cdwph_register_settings');

// Add settings
function cdwph_settings_display() { ?>
<table class="form-table">
	<tbody>
		<tr valign="top">
			<th scope="row"><h3>WP Help settings</th>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="cdwph_url">Source URL</label></th>
			<td><input type="text" id="cdwph_url" name="cdwph_url" value="<?php echo get_option('cdwph_url'); ?>" /></td>
		</tr>
	</tbody>
</table>
<?php }
add_action('cd_settings_general_tab', 'cdwph_settings_display', 11);

// Add the FAQ tab
function cdwph_add_tab( $tabs ) {
	$tabs['help']['FAQ'] = 'faq';
	return $tabs;
}
add_filter('cd_tabs', 'cdwph_add_tab');

// Output the tab content
function cdwph() {
$source_url = get_option('cdwph_url');
$result = wp_remote_get( add_query_arg( 'time', time(), $source_url ) );
if (is_wp_error( $result ) OR empty($result)) {
	echo '<h2>Please enter a valid source URL in <a href="'.cd_get_settings_url().'">Settings</a></h2>';
} else {
$posts = json_decode( $result['body'] );
	if ($posts) {
		echo '<ul>';
		foreach ($posts as $value) {
			$content = apply_filters('the_content', $value->post_content);
			?>
			<li><h3 class="cd-click" onclick="cd_updown('cd-<?php echo $value->post_name; ?>');">
					<?php echo $value->post_title; ?>
				</h3>
				<div id="cd-<?php echo $value->post_name; ?>" style="display: none;">
					<?php echo $content; ?>
				</div>
			</li>
		<?php }
		echo '</ul>';
	}
}
}
add_action('cd_help_faq_tab', 'cdwph');
?>