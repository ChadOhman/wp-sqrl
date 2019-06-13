<?php
/**
 * @package		WP_SQRL
 */

class wp_SQRL {
	private static $initiated = false;

	/**
	 * Called when a new site is created in a WPMU environment.
	 * @since 0.1a
	 * @static
	 */
	public static activate_new_site( $blog_id ) {
		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}
		switch_to_blog( $blog_id );
		self::activation();
		restore_current_blog();
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
	 * Emergency stop button for borked plugin activations.
	 * @since 0.1a
	 * @static
	 */
	private static function cancel_activation( $message ) {
?>
<!doctype html>
<html>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<style>
* {
	text-align: center;
	margin: 0;
	padding: 0;
	font-family: "Lucida Grande",Verdana,Arial,"Bitstream Vera Sans",sans-serif;
}
p {
	margin-top: 15px;
	margin-left: 15px;
	font-size: 18px;
}
</style>
</head>
<body>
<p><?php echo esc_html( $message ); ?></p>
</body>
</html>
<?php
	}

	/**
	 * init()
	 * @since 0.1a
	 * @static
	 */
	private static function init() {
		if ! ( self::$initiated ) {
			self::init_hooks();
		}
	}

	/**
	 * Initialize WP hooks.
	 * @since 0.1a
	 * @static
	 */
	private static function init_hooks() {
		self::$initiated = true;

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
		if ( version_compare( $GLOBALS['wp_version'], WP_SQRL_MINIMUM_WP_VERSION, '<' ) ) {
			load_plugin_textdomain( 'wp_SQRL' );

			$message = 'Need higher version message here.';

			wp_SQRL::cancel_activation( $message );
		}
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			if ( $multisite ) {
				$blog_ids = self::get_blog_ids();
				foreach ( $blog_ids as $blog_id ) {
					switch_to_blog( $blog_id );
					self::activation();
				}
				restore_current_blog();
			} else {
				self::activation();
			}
		} else {
			self::activation();
		}
	}

	/**
	 * Called on to activate plugin on a single WP site or individual blogs within a WPMU environment.
	 * @since 0.1a
	 * @static
	 */
	private static function activation() {

	}

	/**
	 * Attached to deactivate_{ plugin_basename( __FILES__ ) } by register_deactivation_hook()
	 * @since 0.1a
	 * @static
	 */
	public static function plugin_deactivation() {

	}

	/**
	 * Called on to deactivate plugin on a single WP site or individual blogs within a WPMU environment.
	 * @since 0.1a
	 * @static
	 */
	private static function deactivation() {
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			if ( $multisite ) {
				$blog_ids = self::get_blog_ids();
				foreach ( $blog_ids as $blog_id ) {
					switch_to_blog( $blog_id );
					self::deactivation();
				}
				restore_current_blog();
			} else {
				self::deactivation();
			}
		} else {
			self::deactivation();
		}
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