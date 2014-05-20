<?php
/*
Plugin Name: Client Dash WP Help Add-on
Description: Integrates content from WP Help with Client Dash
Version: 0.1
Author: Kyle Maurer
Author URI: http://realbigmarketing.com/staff/kyle
*/
function cdwph_test() {
echo 'test';
}

add_shortcode('cdga', 'cdwph_test');
?>