<?php
/**
 * @package		WP_SQRL
 * @author 		Chad Ohman <chad@chadohman.ca>
 * @license 	Apache License 2.0
 * @link 		https://chadohman.ca
 * @copyright 	2019 Chad Ohman
 * @package		WordPress
 * @subpackage	Plugin
 * @todo		Everything...
 *
 * @wordpress-plugin
 * Plugin Name:	wp-sqrl
 * Plugin URI:	
 * Description:	A WordPress plugin for GRC's SQRL authentication.
 * Version:		0.1a
 * Author:		Chad Ohman <chad@chadohman.ca>
 * Author URI:	https://chadohman.ca
 * License:		Apache License 2.0
 * License URI:	http://www.apache.org/licenses/
 * Text Domain:	wp-sqrl
 */

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_user' ) ) {
	echo 'Hello!  You shouldn not call me directly.';
	exit;
}

define( 'WP_SQRL_VERSION', '0.1b');
define( 'WP_SQRL_MINIMUM_WP_VERSION' , '4.0'); // revisit later
define( 'WP_SQRL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
//define( 'WP_SQRL_VARIABLE', 'something' );

register_activation_hook( __FILE__, array( 'WP_SQRL', 'plugin_activation' ) );

register_deactivation_hook( __FILE__, array( 'WP_SQRL', 'plugin_deactivation' ) );

register_uninstall_hook( __FILE__, array( 'WP_SQRL', 'plugin_delete' ) );

require_once( WP_SQRL_PLUGIN_DIR . 'class.wp-sqrl.php' );
require_once( WP_SQRL_PLUGIN_DIR . 'class.wp-sqrl-admin.php' );
require_once( WP_SQRL_PLUGIN_DIR . '/lib/phpqrcode/qrlib.php' ); // PHP QR Code

add_action( 'init', array( 'WP_SQRL', 'init' ) );

if ( is_admin() ) ) {
	require_once( WP_SQRL__PLUGIN_DIR . 'class.wp-sqrl-admin.php' );
	add_action( 'init', array( 'WP_SQRL_Admin', 'init' ) );
}