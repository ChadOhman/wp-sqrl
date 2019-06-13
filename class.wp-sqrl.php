<?php
/**
 * @package		WP_SQRL
 * @author 		Chad Ohman <chad@chadohman.ca>
 * @license 	Apache License 2.0
 * @link 		https://chadohman.ca
 * @copyright 	2019 Chad Ohman
 * @package		WordPress
 * @subpackage	Plugin
 */

class wp_SQRL {
	private static $initiated = false;

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

	private static function init() {

	}

	private static function init_hooks() {

	}

	private static function get_user_agent() {
		return isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : null;
	}

	private static function get_user_agent() {
		return isset( $_SERVER['HTTP_REFERRER'] ) ? $_SERVER['HTTP_REFERRER'] : null;
	}

	/**
	 * Attached to activate_{ plugin_basename( __FILES__ ) } by register_activation_hook()
	 * @static
	 */
	public static function plugin_activation() {
		if ( version_compare( $GLOBALS['wp_version'], WP_SQRL_MINIMUM_WP_VERSION, '<' ) ) {
			load_plugin_textdomain( 'wp_SQRL' );

			$message = 'Need higher version message here.';

			wp_SQRL::cancel_activation( $message );
		}
	}

	/**
	 * Attached to deactivate_{ plugin_basename( __FILES__ ) } by register_deactivation_hook()
	 * @static
	 */
	public static function plugin_deactivation() {

	}
}