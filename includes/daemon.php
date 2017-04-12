<?php
/**
 * Cron Master Daemon
 *
 * @package   cron_master
 * @author    David Cramer
 * @license   GPL-2.0+
 * @copyright 2017 David Cramer
 */


/**
 * Cron Master Daemonizer
 *
 * @since 1.0.0
 *
 */

ignore_user_abort( true );
include 'remote-call.php';
$log = fopen( 'cron.log', 'a' );
if ( ! file_exists( 'config.json' ) ) {
	fwrite( $log, "\r\nNo cron config file found. Exiting.\r\n" );
	fclose( $log );
	exit;
}
$config = json_decode( file_get_contents( 'config.json' ), true );
if ( isset( $_GET['run'] ) ) {
	// ensure the run file exists
	fclose( fopen( 'running', 'w+' ) );
	// ensure that file can run.
	$loop = 0;
	while ( $loop < 5 ){
		$cron = new RemoteCall( $config['handler'] );
		$cron->setTimeout( 1 );
		$cron->callServer();
		sleep( 1 );
		$loop ++;
		if ( file_exists( 'halt' ) ) {
			unlink( 'halt' );
			fwrite( $log, "Halt detected. ending.\r\n\r\n\r\n" );
			$log = 5;
			$end = true;
		}
	}
} else {
	if ( ! file_exists( 'running' ) ) {
		fwrite( $log, "\r\nStarting up. Waiting for zero precision [" . date( 'H:i:s' ) . "]:" );
		while ( date( 's' ) !== '00' ){
			fwrite( $log, "." );
			usleep( 1000000 );
		}
		fwrite( $log, "\r\nCron Master Started @ " . date( 'r' ) . "\r\n" );
	} else {
		fwrite( $log, "\r\nCron already running\r\n" );
		exit;
	}

}
fclose( $log );
if ( ! empty( $end ) ) {
	unlink( 'running' );
	exit;
}

$request = new RemoteCall( $config['daemon'] . '?run' );
$request->setTimeout( 1 );
$result = $request->callServer();
