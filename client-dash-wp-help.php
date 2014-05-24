<?php
/*
Plugin Name: Client Dash WP Help Add-on
Description: Integrates content from WP Help with Client Dash
Version: 0.1
Author: Kyle Maurer
Author URI: http://realbigmarketing.com/staff/kyle
*/
function cdwph_test() {
$result = wp_remote_get( add_query_arg( 'time', time(), 'http://realbigsites.com/?wp-help-key=72f514d73c4ded27362f2f1983e1347d' ) );
$body = wp_remote_retrieve_body( $result );
$body = json_decode($body);
foreach ( $body as $post ) {
	return $post->post_title;
}
}

add_shortcode('cdwph', 'cdwph_test');
?>