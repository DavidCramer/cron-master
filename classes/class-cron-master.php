<?php

/**
 * Cron Master Main Class
 *
 * @package   cron_master
 * @author    David Cramer
 * @license   GPL-2.0+
 * @link
 * @copyright 2016 David Cramer
 */
class Cron_Master{

	/**
	 * Holds instance of the class
	 *
	 * @since   1.0.0
	 *
	 * @var     Cron_Master
	 */
	private static $instance;

	/**
	 * Holds request data
	 *
	 * @since   1.0.0
	 *
	 * @var     array
	 */
	public $request_data;

	/**
	 * Holds the main admin page suffix
	 *
	 * @since   1.0.0
	 *
	 * @var     array
	 */
	public $admin_page;

	/**
	 * Holds the deamon instance
	 *
	 * @since   1.0.0
	 *
	 * @var     array
	 */
	public $daemon;


	/**
	 * Cron Master constructor.
	 */
	public function __construct() {

		// init daemon
		$this->daemon = daemonizer::init();

		// setup notifications
		add_action( 'init', array( $this, 'setup' ) );
		// Cron init ajax handler
		add_action( 'wp_ajax_init_cron_master', array( $this, 'init_cron' ) );
		add_action( 'wp_ajax_read_cron_master', array( $this, 'read_cron' ) );
		add_action( 'wp_ajax_stop_cron_master', array( $this, 'stop_cron' ) );
		add_action( 'wp_ajax_clear_logs_cron_master', array( $this, 'clear_logs' ) );


	}

	/**
	 * Return an instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return  Cron_Master  A single instance
	 */
	public static function init() {

		// If the single instance hasn't been set, set it now.
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * Set request vars
	 *
	 * @param array $request_data Array of request variables
	 *
	 * @since 1.0.0
	 */
	public function set_request_data( $request_data ) {
		$this->request_data = $request_data;
	}

	/**
	 * Start the Cron system
	 *
	 * @since 1.0.0
	 */
	public function init_cron() {

		wp_send_json_success( $this->daemon->start() );

	}

	/**
	 * Clear the Cron system logs
	 *
	 * @since 1.0.0
	 */
	public function clear_logs() {

		fclose( fopen( CRNMSR_PATH . 'includes/error.log', 'w+' ) );
		fclose( fopen( CRNMSR_PATH . 'includes/cron.log', 'w+' ) );

		wp_send_json_success();

	}


	/**
	 * Read the Cron system Status
	 *
	 * @since 1.0.0
	 */
	public function read_cron() {

		wp_send_json_success( $this->daemon->status() );
	}

	/**
	 * Stop the Cron system Status
	 *
	 * @since 1.0.0
	 */
	public function stop_cron() {

		wp_send_json_success( $this->daemon->stop() );
	}

	/**
	 * Register Admin Pages
	 *
	 * @since 1.0.0
	 * @uses  "admin_menu" action
	 */
	public function register_admin_pages() {
		$this->admin_page = add_menu_page( 'Cron Master', 'Cron Master', 'manage_options', 'cron-master', array(
			$this,
			'admin_render',
		) );
		// enqueue admin scripts and styles
		add_action( 'admin_print_styles-' . $this->admin_page, array( $this, 'style_scripts' ) );
	}

	/**
	 * enqueue style and scripts for admin
	 *
	 * @since 1.0.0
	 */
	public function style_scripts() {
		wp_enqueue_script( 'cron-master-admin', CRNMSR_URL . 'assets/js/admin.min.js', array( 'jquery' ), CRNMSR_VER );
	}

	/**
	 * enqueue style and scripts for admin
	 *
	 * @since 1.0.0
	 */
	public function admin_render() {
		include CRNMSR_PATH . 'includes/admin.php';
	}


	/**
	 * Setup hooks and text load domain
	 *
	 * @since 1.0.0
	 * @uses  "init" action
	 */
	public function setup() {
		load_plugin_textdomain( 'cron-master', false, CRNMSR_CORE . '/languages' );
		add_action( 'admin_menu', array( $this, 'register_admin_pages' ) );


	}

}
