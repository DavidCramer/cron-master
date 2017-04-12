<?php
/**
 * Cron Master Helper Functions
 *
 * @package   cron_master
 * @author    David Cramer
 * @license   GPL-2.0+
 * @copyright 2017 David Cramer
 */


/**
 * Cron Master Object class autoloader.
 * It locates and finds class via classes folder structure.
 *
 * @since 1.0.0
 *
 * @param string $class class name to be checked and autoloaded
 */
function cron_master_autoload_class( $class ) {
	$parts = explode( '\\', $class );
	$name  = strtolower( str_replace( '_', '-', array_shift( $parts ) ) );
	if ( file_exists( CRNMSR_PATH . 'classes/' . $name ) ) {
		if ( ! empty( $parts ) ) {
			$name .= '/' . implode( '/', $parts );
		}
		$class_file = CRNMSR_PATH . 'classes/class-' . $name . '.php';
		if ( file_exists( $class_file ) ) {
			include_once $class_file;
		}
	} elseif ( empty( $parts ) && file_exists( CRNMSR_PATH . 'classes/class-' . $name . '.php' ) ) {
		include_once CRNMSR_PATH . 'classes/class-' . $name . '.php';
	}
}

/**
 * Cron Master Helper to load and manipulate the overall instance.
 *
 * @since 1.0.0
 * @return  Cron_Master  A single instance
 */
function cron_master() {
	$request_data = array(
		'post'    => $_POST,
		'get'     => $_GET,
		'files'   => $_FILES,
		'request' => $_REQUEST,
		'server'  => $_SERVER,
	);

	// init Context
	$instance = Cron_Master::init();
	$instance->set_request_data( $request_data );

	return $instance;
}
