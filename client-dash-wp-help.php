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
if ( $result['response']['code'] == 200 ) {
	$topics = new WP_Query( array( 'post_type' => self::POST_TYPE, 'posts_per_page' => -1, 'post_status' => 'publish' ) );
	$source_id_to_local_id = array();
	if ( $topics->posts ) {
		foreach ( $topics->posts as $p ) {
			if ( $this->is_slurped( $p->ID ) && $source_id = get_post_meta( $p->ID, '_cws_wp_help_slurp_id', true ) )
				$source_id_to_local_id[$source_id] = $p->ID;
		}
	}
	$posts = json_decode( $result['body'] );
	$source_post_ids = array();
	// First pass: just insert whatever is missing, without fixing post_parent
	foreach ( $posts as $p ) {
		$p = (array) $p;
		$source_post_ids[absint( $p['ID'] )] = absint( $p['ID'] );
		// These things are implied in the API, but we need to set them before inserting locally
		$p['post_type'] = self::POST_TYPE;
		$p['post_status'] = 'publish';
		// $p['menu_order'] += 100000;
		$copy = $p;
		if ( isset( $source_id_to_local_id[$p['ID']] ) ) {
			// Exists. We know the local ID.
			$copy['ID'] = $source_id_to_local_id[$p['ID']];
			wp_update_post( $copy );
		} else {
			// This is new. Insert it.
			unset( $copy['ID'] );
			$new_local_id = wp_insert_post( $copy );

			// Update our lookup table
			$source_id_to_local_id[$p['ID']] = $new_local_id;
			// Update postmeta
			update_post_meta( $new_local_id, '_cws_wp_help_slurp_id', absint( $p['ID'] ) );
			update_post_meta( $new_local_id, '_cws_wp_help_slurp_source', $this->get_slurp_source_key() );
		}
	}
	// Set the default document
	foreach ( $posts as $p ) {
		if ( isset( $p->default ) && isset( $source_id_to_local_id[ $p->ID ] ) ) {
			update_option( self::default_doc, $source_id_to_local_id[ $p->ID ] );
			break;
		}
	}
	// Delete any abandoned posts
	$topics = new WP_Query( array( 'post_type' => self::POST_TYPE, 'posts_per_page' => -1, 'post_status' => 'any', 'meta_query' => array( array( 'key' => '_cws_wp_help_slurp_id', 'value' => 0, 'compare' => '>', 'type' => 'NUMERIC' ) ) ) );
	if ( $topics->posts ) {
		foreach ( $topics->posts as $p ) {
			if ( $source_id = get_post_meta( $p->ID, '_cws_wp_help_slurp_id', true ) ) {
				// This was slurped. Was it absent from the API response? Or was it from a different source?
				if ( !$this->is_slurped( $p->ID ) || !isset( $source_post_ids[absint($source_id)] ) ) {
					// Wasn't in the response. Delete it.
					wp_delete_post( $p->ID );
				}
			}
		}
	}
	// Reparenting and link fixing
	$topics = new WP_Query( array( 'post_type' => self::POST_TYPE, 'posts_per_page' => -1, 'post_status' => 'publish', 'meta_query' => array( array( 'key' => '_cws_wp_help_slurp_id', 'value' => 0, 'compare' => '>', 'type' => 'NUMERIC' ) ) ) );
	if ( $topics->posts ) {
		foreach ( $topics->posts as $p ) {
			$new = array();
			if ( strpos( $p->post_content, 'http://wp-help-link/' ) !== false ) {
				$new['post_content'] = $this->make_links_local( $p->post_content );
				if ( $new['post_content'] === $p->post_content )
					unset( $new['post_content'] );
			}
			$new['post_parent'] = $this->local_id_from_slurp_id( $p->post_parent );
			if ( $new['post_parent'] === $p->post_parent )
				unset( $new['post_parent'] );
			if ( $new ) {
				$new['ID'] = $p->ID;
				wp_update_post( $new );
			}
		}
	}
}
}

add_shortcode('cdwph', 'cdwph_test');
?>