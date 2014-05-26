<?php
/*
Plugin Name: Client Dash WP Help Add-on
Description: Integrates content from WP Help with Client Dash
Version: 0.2
Author: Kyle Maurer
Author URI: http://realbigmarketing.com/staff/kyle
*/
// Add settings
function cdwph_settings() {
	echo 'test settings';
}
add_action('settings_page_client-dash', 'cdwph_settings');

// Add the FAQ tab
function cdwph_add_tab( $tabs ) {
	$tabs['FAQ'] = 'faq';
	return $tabs;
}
add_filter('cd_add_tabs', 'cdwph_add_tab');

// Output the tab content
function cdwph() {
$result = wp_remote_get( add_query_arg( 'time', time(), 'http://realbigsites.com/?wp-help-key=72f514d73c4ded27362f2f1983e1347d' ) );
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
add_action('cd_add_to_faq_tab', 'cdwph');
?>