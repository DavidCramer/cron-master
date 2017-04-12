<?php
/**
 * Cron Master Bootstrapper
 *
 * @package   cron_master
 * @author    David Cramer
 * @license   GPL-2.0+
 * @link
 * @copyright 2016 David Cramer
 *
 */
// If this file is called directly, abort.
if ( defined( 'WPINC' ) ) {

	if ( ! defined( 'DEBUG_SCRIPTS' ) ) {
		define( 'CRNMSR_ASSET_DEBUG', '.min' );
	} else {
		define( 'CRNMSR_ASSET_DEBUG', '' );
	}

	// include context helper functions and autoloader.
	require_once( CRNMSR_PATH . 'includes/functions.php' );

	// register cron master autoloader
	spl_autoload_register( 'cron_master_autoload_class', true, false );

	// bootstrap plugin load
	add_action( 'plugins_loaded', 'cron_master' );
}
