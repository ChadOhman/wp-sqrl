<?php
/**
 * @package		WP_SQRL
 */

class wp_SQRL {
	private static $initiated = false;
	protected $plugin_slug = 'wp-sqrl';

	/**
	 * Constructor run on plugin initiation
	 * @since 0.1a
	 */
	public function __construct() {

		if ( version_compare( PHP_VERSION, $this->php_required, '<' ) ) {
			add_action( 'all_admin_notices', array( $this, 'admin_notice_insufficient_php' ) );
			$abort = true;
		}

		if ( !empty( $abort ) ) {
			return;
		}

		if ( is_admin() ) {
			add_action( 'admin_init', array( $this, 'check_possible_reset') );
			add_action( 'admin_menu', array( $this, 'menu_entry_for_admin') );

			$plugin = plugin_basename( __FILE__ );
			add_filter( 'plugin_action_links_' . $plugin, array( $this, 'addPluginSettingsLink' ) );
			add_filter( 'network_admin_plugin_action_links_' . $plugin, array( $this, 'addPluginSettingsLink' ) );

			add_action( 'network_admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );

			// add SQRL column on users list.
			add_action( 'manauge_users_columns', array( $this, 'manageUsersColumnsSQRL' ), 10 , 1 );
			add_action( 'manage_users_custom_column', array( $this, 'manageUsersCustomColumnSQRL', 10, 3 ) );
		} else {
			add_action( 'init', array( $this, 'check_possible_reset' ) );
		}

		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );

		if ( isset( $GLOBALS['pagenow'] ) && $GLOBALS['pagenow'] == 'wp_login.php' ) {
			add_action( 'init', array( $this, 'login_enqueue_scripts' ), -999999999 );
		} else {
			add_action( 'login_enqueue_scripts', array( $this, 'login_enqueue_scripts' ), -999999999 );
		}

		

	}

	/**
	 * Add rewrite rule to support SQRL endpoint URLs.
	 * @since 0.1a
	 * @static
	 */
	private static add_rewrite_rules() {
		$regex = '^sqrlauth\/+\?(.*)$';
		$query = 'index.php?pagename=sqrlauth&$matches[1]';
		add_rewrite_rule( $regex, $query, 'top' );
	}


	/**
	 * init()
	 * @since 0.1a
	 * @static
	 */
	private static function init() {
		
	}

	/**
	 * Initialize WP hooks.
	 * @since 0.1a
	 * @static
	 */
	private static function init_hooks() {
		//add_action();
	}

	/**
	 * Returns all blog ids of blogs within a WPMU environment that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 * @since 0.1a
	 * @static
	 */
	private static function get_blog_ids() {
		global $wpdb;

		$sql = "SELECT blog_id FROM $wpdb-blogs
				WHERE archived = '0' AND spam = '0'
				AND deleted = '0'";

		return $wpdb->get_col( $sql );
	}

	/**
	 * Returns User Agent.
	 * @since 0.1a
	 * @static
	 */
	private static function get_user_agent() {
		return isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : null;
	}

	/**
	 * Returns User Agent Referrer.
	 * @since 0.1a
	 * @static
	 */
	private static function get_user_agent() {
		return isset( $_SERVER['HTTP_REFERRER'] ) ? $_SERVER['HTTP_REFERRER'] : null;
	}

	/**
	 * Generate a QR code using PHP QRCode class
	 * @since 0.1a
	 * @param string $qr_string String for generating QR image
	 * @return array
	 * @static
	 */
	private static function generate_gr_code( $qr_string ) {

	}

	/**
	 * Attached to activate_{ plugin_basename( __FILES__ ) } by register_activation_hook()
	 * @since 0.1a
	 * @param boolean $multisite True with WPMU superadmin activates plugin on multisite network.
	 * @static
	 */
	public static function plugin_activation( $multisite ) {

	}

	/**
	 * Attached to deactivate_{ plugin_basename( __FILES__ ) } by register_deactivation_hook()
	 * @since 0.1a
	 * @static
	 */
	public static function plugin_deactivation() {

	}

	}

	/**
	 * Attached to uninstall_{ plugin_basename( __FILES__ ) } by register_uninstall_hook()
	 * @since 0.1a
	 * @static
	 */
	public static function plugin_delete() {

	}
}