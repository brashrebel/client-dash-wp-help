<?php
/*
Plugin Name: Client Dash WP Help Add-on
Description: Integrates content from WP Help with Client Dash by displaying it on the FAQ tab under the Help page.
Version: 0.3.2
Author: Kyle Maurer
Author URI: http://realbigmarketing.com/staff/kyle
*/

class CDWPHelp {

	/*
	* These variables you can change
	*/
	// Define the plugin name
	private $plugin = 'Client Dash WP Help Addon';
	// Setup your prefix
	private $pre = 'cdwph';
	// Set this to be name of your content block
	private $block_name = 'WP Help';
	// Set the tab slug and name (lowercase)
	private $tab = 'faq';
	// Set this to the page you want your tab to appear on (account, help and reports exist in Client Dash)
	private $page = 'help';

	// A URL/text field option
	private $source_url = '_source_url';

	// Set everything up
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'notices' ) );
		add_action( 'plugins_loaded', array( $this, 'content_block' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'cd_settings_general_tab', array( $this, 'settings_display' ), 11 );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_styles' ) );
	}

	// Per CD 1.4, content blocks are the way to go
	public function content_block() {
		cd_content_block( $this->block_name, $this->page, $this->tab, array( $this, 'block_contents' ) );
	}

	public function register_styles() {
		wp_register_style( $this->pre , plugin_dir_url(__FILE__).'style.css' );
		$page = get_current_screen();
		$tab = $_GET['tab'];

		if ( $page->id != 'dashboard_page_cd_'.$this->page && $tab != $this->tab )
			return;

		wp_enqueue_style( $this->pre );
	}

	// Notices for if CD is not active (no need to change)
	public function notices() {
		if ( !is_plugin_active( 'client-dash/client-dash.php' ) ) { ?>
		<div class="error">
			<p><?php echo $this->plugin; ?> requires <a href="http://w.org/plugins/client-dash">Client Dash</a> version 1.4 or greater.
			Please install and activate <b>Client Dash</b> to continue using.</p>
		</div>
		<?php
		}
	}

	// Register settings
	public function register_settings() {
		register_setting( 'cd_options_general', $this->pre.$this->source_url, 'esc_url_raw' );
	}

	// Add settings to General tab
	public function settings_display() {
		$source_url = $this->pre.$this->source_url;
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
					value="<?php echo get_option( $source_url ); ?>" />
				</td>
			</tr>
		</tbody>
	</table>
	<?php }

	// Insert the tab contents
	public function block_contents() {
		$source_url = get_option( $this->pre.$this->source_url );
		$result = wp_remote_get( add_query_arg( 'time', time(), $source_url ) );
		if ( is_wp_error( $result ) OR empty( $result ) ) {
		echo '<h2>Please enter a valid source URL in <a href="'.cd_get_settings_url().'">Settings</a></h2>';
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
				<?php }
				echo '</ul>';
			}
		}
	}
}
// Instantiate the class
$cdwph = new CDWPHelp;