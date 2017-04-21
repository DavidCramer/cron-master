<?php

/**
 * daemonizer Main Class
 *
 * @package   cron_master
 * @author    David Cramer
 * @license   GPL-2.0+
 * @link
 * @copyright 2016 David Cramer
 */
class daemonizer{

	/**
	 * Holds instance of the class
	 *
	 * @since   1.0.0
	 *
	 * @var     daemonizer
	 */
	private static $instance;
	/**
	 * Holds the start timestamp
	 *
	 * @since   1.0.0
	 *
	 * @var     int
	 */
	private $start;
	/**
	 * Holds the start timestamp
	 *
	 * @since   1.0.0
	 *
	 * @var     int
	 */
	private $micro_start;
	/**
	 * Holds the start timestamp
	 *
	 * @since   1.0.0
	 *
	 * @var     int
	 */
	private $hook_start;
	/**
	 * Holds the start timestamp
	 *
	 * @since   1.0.0
	 *
	 * @var     int
	 */
	private $hook_micro_start;

	/**
	 * daemonizer constructor.
	 */
	public function __construct() {

		// daemon rest route
		add_action( 'rest_api_init', array( $this, 'register_rest' ) );
		// hook in logging
		add_filter( 'pre_set_transient_doing_cron', array( $this, 'start_record_cron' ) );
		add_action( 'delete_transient_doing_cron', array( $this, 'record_cron' ) );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return  daemonizer  A single instance
	 */
	public static function init() {

		// If the single instance hasn't been set, set it now.
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;

	}

	/**
	 * enqueue style and scripts for admin
	 *
	 * @since 1.0.0
	 */
	public function start_record_cron( $a ) {

		$time              = microtime();
		$time              = explode( ' ', $time );
		$time              = $time[1] + $time[0];
		$this->micro_start = $time;

		$this->start = time();

		$crons    = _get_cron_array();
		$gmt_time = microtime( true );
		foreach ( $crons as $timestamp => $cronhooks ) {
			if ( $timestamp > $gmt_time ) {
				break;
			}

			foreach ( $cronhooks as $hook => $keys ) {
				add_action( $hook, array( $this, 'reset_hook_times' ), 5 );
				add_action( $hook, array( $this, $hook ), 100 );
			}
		}

		return $a;
	}

	/**
	 * enqueue style and scripts for admin
	 *
	 * @since 1.0.0
	 */
	public function record_cron() {

		$total_time = $this->get_time();
		$this->log( "Cron run completed in " . $total_time, true );
	}

	private function get_time() {

		$diff = (int) abs( time() - $this->start );
		if ( empty( $diff ) ) {
			$time   = microtime();
			$time   = explode( ' ', $time );
			$time   = $time[1] + $time[0];
			$finish = $time;
			$diff   = round( ( $finish - $this->micro_start ), 4 );
		}
		if ( $diff < 60 ) {
			return $diff . ' Seconds';
		}

		return human_time_diff( $this->start );
	}

	/**
	 * Log message
	 *
	 * @since 1.0.0
	 *
	 * @param string $message The message to log to the cron log.
	 * @param bool   $break   Flag to insert a line break after.
	 *
	 */
	private function log( $message = false, $break = false ) {

		$log = fopen( CRNMSR_PATH . 'includes/cron.log', 'a' );
		if ( ! empty( $message ) ) {
			fwrite( $log, "[" . current_time( 'mysql' ) . "] " . $message . PHP_EOL );
		} else {
			$break = true;
		}
		if ( true === $break ) {
			fwrite( $log, PHP_EOL );
		}
		fclose( $log );

	}

	/**
	 * enqueue style and scripts for admin
	 *
	 * @since 1.0.0
	 */
	public function reset_hook_times() {
		$this->hook_start       = time();
		$time                   = microtime();
		$time                   = explode( ' ', $time );
		$time                   = $time[1] + $time[0];
		$this->hook_micro_start = $time;
	}

	/**
	 * enqueue style and scripts for admin
	 *
	 * @since 1.0.0
	 */
	public function __call( $tag, $args ) {


		$total_time = $this->get_hook_time();
		$this->log( $tag . " completed in " . $total_time );
		$this->update_process_count();
	}

	private function get_hook_time() {

		$diff = (int) abs( time() - $this->hook_start );
		if ( empty( $diff ) ) {
			$time   = microtime();
			$time   = explode( ' ', $time );
			$time   = $time[1] + $time[0];
			$finish = $time;

			return round( ( $finish - $this->hook_micro_start ), 4 ) . ' ms';
		}
		if ( $diff < 60 ) {
			return $diff . ' Seconds';
		}

		return human_time_diff( $this->start );
	}

	/**
	 * Updates the process count status
	 *
	 * @since 1.0.0
	 *
	 */
	private function update_process_count() {
		$status                   = $this->status();
		$status['processed_jobs'] += 1;
		$this->update_status( $status );
	}

	/**
	 * Gets the runner status
	 *
	 * @since 1.0.0
	 *
	 * @return array The runners status array
	 */
	public function status() {
		$status = array(
			'status' => 'stopped',
		);
		if ( file_exists( CRNMSR_PATH . 'includes/runner' ) ) {
			$file    = fopen( CRNMSR_PATH . 'includes/runner', "r" );
			$content = fread( $file, filesize( CRNMSR_PATH . 'includes/runner' ) );
			fclose( $file );
			$data                  = json_decode( $content, ARRAY_A );
			$status                = array_merge( $status, $data );
			$status['started']     = date( get_option( 'date_format' ) . ' @ ' . get_option( 'time_format' ), $data['timestamp'] );
			$status['status_time'] = human_time_diff( $data['timestamp'], current_time( 'timestamp' ) );
			$status['status_diff'] = current_time( 'timestamp' ) - $data['timestamp'];
		}

		return $status;
	}

	/**
	 * Update Status
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Status data to write to runner file.
	 *
	 */
	private function update_status( $data, $create = false ) {
		if ( file_exists( CRNMSR_PATH . 'includes/runner' ) || true === $create ) {
			if ( true === $create ) {
				$this->log( 'Creating runner' );
			}
			$runner = fopen( CRNMSR_PATH . 'includes/runner', 'w+' );
			fwrite( $runner, json_encode( (array) $data ) );
			fclose( $runner );
		}

	}

	/**
	 * Setup daemon
	 *
	 * @since 1.0.0
	 */
	public function daemon() {

		// set process to run without exiting on about. ( daemonize process in a loop )
		ignore_user_abort( true );
		// never-ending story.
		set_time_limit( 0 );
		$this->create_runner();
		register_shutdown_function( array( $this, 'shutdown' ) );
		set_error_handler( array( $this, 'error' ) );

		while ( ! $this->halt() ){
			// do cron caller
			$this->wp_cron();
		}

		return array( 'completed' );
	}

	/**
	 * Creates the runner flag file so we don't have multiple processes together.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if needs to be started and False if not.
	 */
	public function create_runner() {
		$data = array(
			'status'               => 'running',
			'timestamp'            => current_time( 'timestamp' ),
			'started'              => 0,
			'status_time'          => 0,
			'status_diff'          => 0,
			'tasks_run'            => 0,
			'average_process_time' => 0,
			'processed_jobs'       => 0,
		);
		$data = array_merge( $this->status(), $data );
		$this->update_status( $data, true );
		$this->log( 'Preparing System', true );
		$this->log( 'Starting Cron Master' );
		$this->log( '====================', true );
	}

	/**
	 * Checks if a halt file exists to end the process.
	 *
	 * @since 1.0.0
	 * @return bool true if halt
	 *
	 */
	private function halt() {
		return ! file_exists( CRNMSR_PATH . 'includes/runner' );
	}

	/**
	 * run a WP Cron instance
	 *
	 * @since 1.0.0
	 *
	 */
	public function wp_cron() {

		if ( ! $this->cron_locked() ) {
			// lock cron for next run
			$this->lock();
			// make a nonce for starting
			$args = array(
				'timeout'     => 1,
				'httpversion' => '1.1',
			);

			wp_remote_get( rest_url( 'cron-master/v1/cron' ), $args );
			$this->update_memory_use();
		}

	}

	/**
	 * Verify if a cron lock is in place
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if not and false if is
	 */
	public function cron_locked() {
		return file_exists( CRNMSR_PATH . 'includes/wp_cron_lock' );
	}

	/**
	 * Stops the daemon
	 *
	 * @since 1.0.0
	 *
	 */
	private function lock() {
		if ( ! file_exists( CRNMSR_PATH . 'includes/wp_cron_lock' ) ) {
			fclose( fopen( CRNMSR_PATH . 'includes/wp_cron_lock', 'a' ) );
		}
	}

	/**
	 * Updates the memory use for the status file.
	 *
	 * @since 1.0.0
	 *
	 */
	private function update_memory_use() {
		ob_flush();
		$status                = $this->status();
		$status['memory']      = size_format( memory_get_usage( true ) );
		$status['memory_peak'] = size_format( memory_get_peak_usage( true ) );
		$this->update_status( $status );
	}

	/**
	 * Stops the daemon
	 *
	 * @since 1.0.0
	 *
	 */
	public function stop() {
		$this->log( 'Stop Command issued.', true );
		if ( file_exists( CRNMSR_PATH . 'includes/runner' ) ) {
			$runner = fopen( CRNMSR_PATH . 'includes/runner', "r+" );
			while ( ! flock( $runner, LOCK_EX ) ){
				usleep( 5000 );
				$this->log( 'Attempting to lock runner... ' );
			}
			$this->log( 'Runner Locked. Stopping Process.' );
			fclose( $runner );
			unlink( CRNMSR_PATH . 'includes/runner' );

		}

		return $this->status();
	}

	/**
	 * Verify if a daemon should be started or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if needs to be started and False if not.
	 */
	public function verify() {
		$valid = false;
		if ( ! empty( $_SERVER['HTTP_WP_NONCE'] ) ) {
			$status = $this->status();
			if ( $status['status'] == 'stopped' ) {
				$nonce = get_transient( 'daemon' );
				if ( $nonce === $_SERVER['HTTP_WP_NONCE'] ) {
					delete_transient( 'daemon' );
					$valid = true;
				}
			}
		}

		return $valid;
	}

	/**
	 * Sets up the shutdown function for the cron in order to unlock the process
	 *
	 * @since 1.0.0
	 *
	 */
	public function shutdown_cron() {
		$this->unlock();
	}

	/**
	 * Stops the daemon
	 *
	 * @since 1.0.0
	 *
	 */
	private function unlock() {
		if ( file_exists( CRNMSR_PATH . 'includes/wp_cron_lock' ) ) {
			unlink( CRNMSR_PATH . 'includes/wp_cron_lock' );
		}
	}

	/**
	 * Sets up the shutdown function in case this closes for any reason,
	 * it will stop the running flag so it can be started again.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if needs to be started and False if not.
	 */
	public function shutdown() {
		$this->log( 'Shutdown detected!', true );
		$data = error_get_last();
		if ( ! empty( $data ) ) {
			$error = fopen( CRNMSR_PATH . 'includes/error.log', 'a' );
			foreach ( (array) $data as $error_line ) {
				fwrite( $error, $error_line . PHP_EOL );
			}
			fclose( $error );
		}
		$this->unlock();
		$status = $this->status();

		if ( $status['status'] == 'running' ) {
			// if was running but no is not, indicates the script stopped for some reason.
			if ( $status['status_diff'] <= 1 ) {
				// if only ran for a second, assume there's an error
				$status = array(
					'status'    => 'error',
					'error'     => 'Cron self terminated within a second. not restarting',
					'timestamp' => current_time( 'timestamp' ),
				);
				$this->update_status( $status );
			} else {
				// ran a little longer, so try again.
				//unlink( CRNMSR_PATH . 'includes/runner' );
				$status['status'] = 'stopped';
				if ( ! isset( $status['cycles'] ) ) {
					$status['cycles'] = 0;
				}
				$status['cycles'] += 1;
				$this->update_status( $status );
				$this->start();
			}
		}
	}

	/**
	 * Starts the daemon
	 *
	 * @since 1.0.0
	 *
	 */
	public function start() {

		// make a nonce for starting
		$nonce = wp_create_nonce( 'daemon' );
		set_transient( 'daemon', $nonce );
		$args = array(
			'timeout'     => 1,
			'httpversion' => '1.1',
			'headers'     => array(
				'wp-nonce' => $nonce,
			),
		);

		wp_remote_get( rest_url( 'cron-master/v1/daemon' ), $args );

		return $this->status();
	}

	/**
	 * run a WP Cron instance
	 *
	 * @since 1.0.0
	 *
	 */
	public function do_cron() {

		// register unlocker
		register_shutdown_function( array( $this, 'shutdown_cron' ) );
		// make a nonce for starting
		$args = array(
			'httpversion' => '1.1',
		);

		$req = wp_remote_get( site_url( 'wp-cron.php?doing_wp_cron' ), $args );
		if ( ! empty( $req['body'] ) ) {
			$this->log();
			$this->log( $req['body'], true );
		}
		$this->unlock();

	}

	/**
	 * Handles error logging in the event of a dirty shatdown
	 *
	 * @since 1.0.0
	 *
	 * @param int    $errno   The level of the error raised.
	 * @param string $errstr  The error message.
	 * @param string $errfile The filename that the error was raised in.
	 * @param int    $errline The line number the error was raised at.
	 *
	 * @return bool fallback to internal error handler if false.
	 */
	public function error( $errno, $errstr, $errfile, $errline ) {

		if ( ! ( error_reporting() & $errno ) ) {
			// This error code is not included in error_reporting, so let it fall
			// through to the standard PHP error handler
			return false;
		}
		$error = fopen( CRNMSR_PATH . 'includes/error.log', 'a' );
		fwrite( $error, "[" . $errno . "] " . $errstr );
		fwrite( $error, "  on line " . $errline . " in file " . $errfile . PHP_EOL );
		fwrite( $error, "  PHP " . PHP_VERSION . " (" . PHP_OS . ")" . PHP_EOL );


		fclose( $error );

		/* Don't execute PHP internal error handler */

		return true;

	}

	/**
	 * Register the REST daemon route init
	 *
	 * @since 1.0.0
	 *
	 */
	public function register_rest() {

		// Get runner status
		$route_args = array(
			'methods'  => 'GET',
			'callback' => array( $this, 'status' ),
		);
		register_rest_route( 'cron-master/v1', '/status', $route_args );
		// Set cron runner
		$route_args = array(
			'methods'  => 'GET',
			'callback' => array( $this, 'do_cron' ),
		);
		register_rest_route( 'cron-master/v1', '/cron', $route_args );
		// Run daemon Process.
		$route_args = array(
			'methods'             => 'GET',
			'permission_callback' => array( $this, 'verify' ),
			'callback'            => array( $this, 'daemon' ),
		);
		register_rest_route( 'cron-master/v1', '/daemon', $route_args );
	}

}
