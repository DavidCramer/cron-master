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
	fclose( $log );
	exit;
}
$config = json_decode( file_get_contents( 'config.json' ), true );
$cron = new RemoteCall( $config['cron'] );
$data = $cron->callServer();
fwrite( $log, $data['body'] );
fclose( $log );