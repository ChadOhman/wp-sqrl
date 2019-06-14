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

		if ( !defined( 'SQRL_DISABLE' ) || !SQRL_DISABLE ) {
			add_filter( 'authenticate', array( $this, 'sqrlVerifyCodeAndUser' ), 999999999, 3 );
		}
	}

	/**
	 * Get the user capability needed for managing TFA users.
	 *
	 * @return String
	 */
	public function get_management_capability() {
		return apply_filters( 'sqrl_management_capability', 'manage_options' );
	}

	/**
	 * Get PHP errors
	 *
	 * @return boolean
	 */
	public function get_php_errors( $errno, $errstr, $errfile, $errline ) {
		if ( 0 == error_reporting() ) {
			return true;
		}
		$logfile = $this->php_error_to_logline( $errno, $errstr, $errfile, $errline );
		$this->logged[] = $logline;
		return true;
	}


	public function php_error_to_logline( $errno, $errstr, $errfile, $errline ) {
		switch ( $errno ) {
			case 1:
				$e_type = 'E_ERROR';
				break;
			case 2:
				$e_type = 'E_WARNING';
				break;
			case 4:
				$e_type = 'E_PARSE';
				break;
			case 8:
				$e_type = 'E_NOTICE';
				break;
			case 16:
				$e_type = 'E_CORE_ERROR';
				break;
			case 32:
				$e_type = 'E_CORE_WARNING';
				break;
			case 64:
				$e_type = 'E_COMPILE_ERROR';
				break;
			case 128:
				$e_type = 'E_COMPILE_WARNING';
				break;
			case 256:
				$e_type = 'E_USER_ERROR';
				break;
			case 512:
				$e_type = 'E_USER_WARNING';
				break;
			case 1024:
				$e_type = 'E_USER_NOTICE';
				break;
			case 2048:
				$e_type = 'E_STRICT';
				break;
			case 4096:
				$e_type = 'E_RECOVERABLE_ERROR';
				break;
			case 8192:
				$e_type = 'E_DEPRECATED';
				break;
			case 16384:
				$e_type = 'E_USER_DEPRECATED';
				break;
			case 30719:
				$e_type = 'E_ALL';
				break;
			default:
				$e_type = "E_UNKNOWN ($errno)";
				break;
		}
		if ( !is_string( $errstr ) ) {
			$errstr = serialize( $errstr );
		}
		if ( 0 == strpos( $errfile, ABSPATH ) ) {
			$errfile = substr( $errfile, strlen( ABSPATH ));
		}

		return "PHP event: code $e_type: $errstr (line $errline, $errfile)";
	}

	/**
	 * Runs on WP init call
	 * @since 0.1a
	 */
	public function init() {
		if ( !is_admin() && is_user_logged_in() && file_exists( WP_SQRL_PLUGIN_DIR . '/inc/sqrl_frontend.php' ) ) ) ) {
			$this->load_frontend();
		} else {
			add_shortcode( 'sqrl_user_settings', array( $this, 'sqrl_when_not_logged_in' ) ); 
		}
	}

	public function admin_notice_insufficient_php() {
		$this->show_admin_warning( sprintf( '<strong>Higher PHP version required</strong><br>The wp-sqrl plugin requires PHP version % or higher - your current version is %.', $this->php_required, PHP_VERSION ) );
	}

	public function show_admin_warning( $message, $class = "updated" ) {
		echo '<div class="sqrlamessage ' . $class . '">' . '<p>' . $message . '</p></div>';
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

	public function getSQRL() {
		if ( !class_exists( 'QRimage' ) ) {
			require_once( WP_SQRL_PLUGIN_DIR . '/lib/phpqrcode/qrlib.php' ) );
		}
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
	 */
	private function generate_gr_code( $qr_string ) {

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