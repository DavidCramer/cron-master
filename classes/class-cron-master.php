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
	 * Cron Master constructor.
	 */
	public function __construct() {

		// setup notifications
		add_action( 'init', array( $this, 'setup' ) );

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
	 * Register Admin Pages
	 *
	 * @since 1.0.0
	 * @uses "admin_menu" action
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

	}

	/**
	 * enqueue style and scripts for admin
	 *
	 * @since 1.0.0
	 */
	public function admin_render() {
		// render your admin screen
	}

	/**
	 * Setup hooks and text load domain
	 *
	 * @since 1.0.0
	 * @uses "init" action
	 */
	public function setup() {
		load_plugin_textdomain( 'cron-master', false, CRNMSR_CORE . '/languages' );
		add_action( 'admin_menu', array( $this, 'register_admin_pages' ) );

	}

}
