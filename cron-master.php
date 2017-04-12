<?php
/*
 * Plugin Name: Cron Master
 * Plugin URI: http://cramer.co.za
 * Description: Take control of your Cron
 * Version: 1.0.0
 * Author: David Cramer
 * Author URI: http://cramer.co.za
 * Text Domain: cron-master
 * License: GPL2+
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Constants
define( 'CRNMSR_PATH', plugin_dir_path( __FILE__ ) );
define( 'CRNMSR_CORE', __FILE__ );
define( 'CRNMSR_URL', plugin_dir_url( __FILE__ ) );
define( 'CRNMSR_VER', '1.0.0' );

if ( ! version_compare( PHP_VERSION, '5.3.0', '>=' ) ) {
	if ( is_admin() ) {
		add_action( 'admin_notices', 'cron_master_php_ver' );
	}
} else {
	//Includes and run
	include_once CRNMSR_PATH . 'cron-master-bootstrap.php';
}

function cron_master_php_ver() {
	$message = __( 'Cron Master requires PHP version 5.3 or later. We strongly recommend PHP 5.5 or later for security and performance reasons.', 'cron-master' );
	echo '<div id="cron_master_error" class="error notice notice-error"><p>' . $message . '</p></div>';
}