<?php
/*
Plugin Name: Client Dash WP Help Add-on
Description: Integrates content from WP Help with Client Dash by displaying it on the FAQ tab under the Help page.
Version: 0.3.4
Author: Kyle Maurer
Author URI: http://realbigmarketing.com/staff/kyle
*/

/**
 * The function to launch our plugin.
 *
 * This entire class is wrapped in this function because we have
 * to ensure that Client Dash has been loaded before our extension.
 */
function cd_wp_help() {

	if ( ! class_exists( 'ClientDash' ) ) {
		add_action( 'admin_notices', 'cdwph_notice' );

		return;
	}

	/**
	 * Class CDWPHelp
	 *
	 * Our main class for our plugin.
	 *
	 * @package WordPress
	 * @subpackage ClientDash WP Help
	 */
	class CDWPHelp extends ClientDash {

		/*
		* These variables you can change
		*/
		// Define the plugin name
		private $plugin = 'Client Dash WP Help Addon';
		// Setup your prefix
		private $pre = 'cdwph';
		// Set this to be name of your content block
		private $section_name = 'WP Help';
		// Set the tab slug and name (lowercase)
		private $tab = 'FAQ';
		// Set this to the page you want your tab to appear on (account, help and reports exist in Client Dash)
		private $page = 'Help';
		// The settings page tab
		private $settings_tab = 'WP Help';

		// A URL/text field option
		private $source_url = '_source_url';

		// Set everything up
		public function __construct() {

			add_action( 'admin_init', array( $this, 'register_settings' ) );
			add_action( 'cd_settings_general_tab', array( $this, 'settings_display' ), 11 );
			add_action( 'admin_enqueue_scripts', array( $this, 'register_styles' ) );

			$this->add_content_section(
				array(
					'name' => $this->section_name,
					'page' => $this->page,
					'tab' => $this->tab,
					'callback' => array( $this, 'block_contents' )
				)
			);

			$this->add_content_section(
				array(
					'name' => 'WP Help Settings',
					'page' => 'Settings',
					'tab' => $this->settings_tab,
					'callback' => array( $this, 'settings_display' )
				)
			);
		}

		public function register_styles() {

			wp_register_style( $this->pre, plugin_dir_url( __FILE__ ) . 'style.css' );

			$current_page = isset( $_GET['page'] ) ? $_GET['page'] : null;
			$current_tab  = isset( $_GET['tab'] ) ? $_GET['tab'] : null;

			$page_ID = $this->translate_name_to_id( $this->page );
			$tab_ID = $this->translate_name_to_id( $this->tab );
			$settings_tab_ID = $this->translate_name_to_id( $this->settings_tab );

			// Only add style if on extension tab or on extension settings tab
			if ( ( $current_page == $page_ID && $current_tab == $tab_ID )
			     || ( $current_page == 'cd_settings' && $current_tab == $settings_tab_ID ) ) {
				wp_enqueue_style( $this->pre );
			}

		}

		// Register settings
		public function register_settings() {

			register_setting(
				'cd_options_' . $this->translate_name_to_id( $this->settings_tab ),
				$this->pre . $this->source_url,
				'esc_url_raw'
			);
		}

		// Add settings to General tab
		public function settings_display() {

			$source_url = $this->pre . $this->source_url;
			?>
			<table class="form-table">
				<tbody>
				<tr valign="top">
					<th scope="row"><h3><?php echo $this->plugin; ?> settings</th>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="<?php echo $source_url; ?>">Source URL</label>
					</th>
					<td><input type="text"
					           id="<?php echo $source_url; ?>"
					           name="<?php echo $source_url; ?>"
					           value="<?php echo get_option( $source_url ); ?>"/>
					</td>
				</tr>
				</tbody>
			</table>
		<?php
		}

		// Insert the tab contents
		public function block_contents() {

			$source_url = get_option( $this->pre . $this->source_url );
			$result     = wp_remote_get( add_query_arg( 'time', time(), $source_url ) );
			if ( is_wp_error( $result ) OR empty( $result ) ) {
				$this->error_nag( 'Please enter a valid source URL in <a href="' . $this->get_settings_url() . '">Settings</a>' );
			} else {
				$posts = json_decode( $result['body'] );
				if ( $posts ) {
					echo '<ul>';
					foreach ( $posts as $value ) {
						$content = apply_filters( 'the_content', $value->post_content );
						?>
						<li><h3 class="cd-click" onclick="cd_updown('cd-<?php echo $value->post_name; ?>');">
								<?php echo $value->post_title; ?>
							</h3>

							<div id="cd-<?php echo $value->post_name; ?>" style="display: none;">
								<?php echo $content; ?>
							</div>
						</li>
					<?php
					}
					echo '</ul>';
				}
			}
		}
	}

	// Instantiate the class
	new CDWPHelp();
}

add_action( 'plugins_loaded', 'cd_wp_help' );

/**
 * Notices for if CD is not active (no need to change)
 */
function cdwph_notice() {

	?>
	<div class="error">
		<p>You have activated a plugin that requires <a href="http://w.org/plugins/client-dash">Client Dash</a>
			version 1.5 or greater.
			Please install and activate <b>Client Dash</b> to continue using.</p>
	</div>
<?php
}