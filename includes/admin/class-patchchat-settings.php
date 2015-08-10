<?php
/**
 * PatchChat Settings
 *
 * Registers and renders the 'Settings' menu and page
 *
 * @class PatchChat_Settings
 * @author caseypatrickdriscoll
 * @created 2015-07-24 22:47:49
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class PatchChat_Settings
 */
class PatchChat_Settings {

	/**
	 * Option key, and option page slug
	 * @var string
	 */
	static $key = 'patchchat_settings';


	/**
	 * Options page metabox id
	 * @var string
	 */
	private $metabox_id = 'myprefix_option_metabox';


	/**
	 * Options Page title
	 * @var string
	 */
	static $title = 'PatchChat Settings';


	/**
	 * Options Page hook
	 * @var string
	 */
	protected $options_page = 'patchchat_settings';

	/**
	 * Default option for js debugging in localize() function
	 *
	 * Must be string, not boolean, as false value is passed to js as empty string
	 * http://wordpress.stackexchange.com/questions/105426/passing-boolean-values-with-wp-localize-script
	 * 
	 * @var string
	 */
	static $debug = 'false';


	/**
	 * Default option for js messenger GET pulse on user side
	 *
	 * Defaults to 3 seconds, can be set in patchchat_settings admin
	 * @var string
	 */
	static $user_pulse_time = '3000';


	/**
	 * Default option for js messenger GET pulse on admin side
	 *
	 * Defaults to 1 second, can be set in patchchat_settings admin
	 * @var string
	 */
	static $admin_pulse_time = '1000';


	/**
	 * Initialize the menu registration
	 *
	 * @edited 2015-08-09 16:43:55 - Adds cmb2
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );

		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );

		add_action( 'cmb2_init', array( __CLASS__, 'register_fields' ) );
	}


	/**
	 * 
	 */
	public static function register_settings() {
		register_setting( self::$key, self::$key );
	}


	/**
	 * Register the submenu
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @created 2015-07-24 22:49:09
	 */
	public static function register_menu() {
		add_submenu_page(
			'patchchat',
			self::$title,
			'Settings',
			'manage_options',
			self::$key,
			'PatchChat_Settings::render'
		);
	}


	/**
	 * Registers all the cmb2 fields
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @created 2015-08-09 16:44:44
	 */
	public static function register_fields() {

		$cmb = new_cmb2_box( array(
			'id'         => self::$key,
			'hookup'     => false,
			'show_on'    => array(
				'key'   => 'options-page',
				'value' => array( self::$key, )
			),
		) );

		$cmb->add_field( array(
			'name' => __( 'Welcome Header', 'patchchat' ),
			'desc' => __( 'The text that appears on the chat box header.', 'patchchat' ),
			'id'   => 'welcome-text',
			'type' => 'text',
			'attributes' => array(
				'placeholder' => 'PatchChat',
			),
		) );

		$cmb->add_field( array(
			'name' => __( 'Javascript Debugger', 'patchchat' ),
			'desc' => __( 'Will output values to js console', 'patchchat' ),
			'id'   => 'js-debug',
			'type' => 'checkbox',
		) );

		$cmb->add_field( array(
			'name' => __( 'Admin Pulse Time', 'patchchat' ),
			'desc' => __( 'How fast the admin chat updates, in milliseconds.', 'patchchat' ),
			'id'   => 'admin-pulse-time',
			'type' => 'text_small',
			'attributes' => array(
				'placeholder' => self::$admin_pulse_time,
			),
		) );

		$cmb->add_field( array(
			'name' => __( 'User Pulse Time', 'patchchat' ),
			'desc' => __( 'How fast the user chat updates, in milliseconds.', 'patchchat' ),
			'id'   => 'user-pulse-time',
			'type' => 'text_small',
			'attributes' => array(
				'placeholder' => self::$user_pulse_time,
			),
		) );


	}


	/**
	 * Renders the settings page
	 *
	 * @author caseypatrickdriscoll
	 */
	static function render() {

		?>
		<h2><?php _e( self::$title ); ?></h2>
		<div class="wrap cmb2-options-page">
			<?php cmb2_metabox_form( self::$key, self::$key ); ?>
		</div>
		<?php

	}


	/**
	 * Prepares the 'patchchat' js object variable for wp_localize_script
	 *
	 * Sets defaults if patchchat_settings option array doesn't exist
	 *
	 * @author caseypatrickdriscoll
	 *
	 * @created 2015-08-09 18:33:09
	 * 
	 * @return array $settings The array of settings
	 */
	static function localize() {

		$data = array(
			'ajaxurl' => '/wp-admin/admin-ajax.php',
		);

		$settings = get_option( self::$key );

		// If the 'patchchat_settings' option hasn't been initialized or is missing
		//   set the array with default settings
		if ( $settings === false ) {

			$data['debug']          = self::$debug;
			$data['userpulsetime']  = self::$user_pulse_time;
			$data['adminpulsetime'] = self::$admin_pulse_time;

		} else {

			// Convert js-debug checkbox value to true/false
			// true/false must be string, as wp_localize_script() will convert to string anyways
			// http://wordpress.stackexchange.com/questions/105426/passing-boolean-values-with-wp-localize-script
			if ( array_key_exists( 'js-debug', $settings ) && $settings['js-debug'] == 'on' ) {
				$data['debug'] = 'true';
			} else {
				$data['debug'] = 'false';
			}

			// If not set by admin, set admin pulse time from class default
			if ( array_key_exists( 'admin-pulse-time', $settings ) ) {
				$data['adminpulsetime'] = $settings['admin-pulse-time'];
			} else {
				$data['adminpulsetime'] = self::$admin_pulse_time;
			}

			// If not set by admin, set user pulse time from class default
			if ( array_key_exists( 'user-pulse-time', $settings ) ) {
				$data['userpulsetime'] = $settings['user-pulse-time'];
			} else {
				$data['userpulsetime']  = self::$user_pulse_time;
			}

		}


		return $data;

	}

}