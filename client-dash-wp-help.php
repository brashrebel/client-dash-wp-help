<?php
/*
Plugin Name: Client Dash WP Help Add-on
Description: Integrates content from WP Help with Client Dash
Version: 0.1
Author: Kyle Maurer
Author URI: http://realbigmarketing.com/staff/kyle
*/

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
		foreach ($posts as $value) { ?>
			<li><h3><?php echo $value->post_title; ?></h3>
				<?php echo $value->post_content; ?>
			</li>
		<?php }
		echo '</ul>';
	}
	echo '<pre>';
	print_r($posts);
	echo '</pre>';
}
add_action('cd_add_to_faq_tab', 'cdwph');
?>