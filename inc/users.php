<?php

if( !defined( 'ABSPATH' ) ) exit;  // Exit if accessed directly

/**
* WooEnc_WPUsersAdminPage Class for add Admin Pages in Menu
*/
class WooEnc_WPUsersAdminPage
{
	// class instance
	static $instance;

	// WP_List_Table object
	public $users_obj;

	const MENU_PAGE_TITLE = 'WooCommerce GDPR Data Protection';

	const PAGE_SLUG = 'woo_gdpr_data_protection';

	const MENU_TITLE = 'WooCommerce GDPR';

	public function __construct()
	{
		add_filter( 'set-screen-option', array(  __CLASS__, 'set_screen' ), 10, 3 );
		add_action( 'admin_menu', array(  $this, 'plugin_menu' ), 1);
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_register_assets' ) );
	}

	public static function set_screen( $status, $option, $value ) {
		return $value;
	}

	/**
	 * Admin CSS and JS Files
	 */
	function admin_register_assets( $hook )
	{
       	wp_enqueue_style( 'wooenc-style', WOOENCRYPTION__PLUGIN_URL.'/assets/css/admin.css');
       	wp_enqueue_script('wooenc-script', WOOENCRYPTION__PLUGIN_URL.'/assets/js/admin.js', array( 'jquery' ), '1.0.0', true);
		wp_localize_script( 'wooenc-script', 'WooEncAjax_admin', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));
    }

    /**
	 * add Menu page
	 */
	public function plugin_menu() 
	{
		add_menu_page( self::MENU_PAGE_TITLE, self::MENU_TITLE, 'manage_options', self::PAGE_SLUG, '', 'dashicons-shield',75 );

		add_submenu_page( static::PAGE_SLUG, 'Settings', 'Settings', 'manage_options', self::PAGE_SLUG, array( $this, 'WooEnc_aboutPlugin' ) );

		//add_submenu_page( static::PAGE_SLUG, 'Data Export Request', 'Data Export Request', 'manage_options', 'wooenc_export_personal_data', array( $this, 'WooEnc_listOfDataExportRequests' ) );

		//add_submenu_page( static::PAGE_SLUG, 'Data Erasure Request', 'Data Erasure Request', 'manage_options', 'wooenc_remove_personal_data', array( $this, 'WooEnc_listOfDataErasureRequests' ) );

		$templatehook = add_submenu_page(static::PAGE_SLUG, 'All Encrypted Users', 'Encrypted Users', 'manage_options', 'wooenc-users-lists', array( $this, 'users_lists_function' ) );
		add_action( "load-$templatehook", array( $this, 'screen_option_users' ) );

		if (get_option('WooEncWoocommerceEnable')) {
			add_submenu_page(static::PAGE_SLUG, 'Decrypt Orders Data', 'Decrypt Orders Data', 'manage_options', 'wooenc-decrypt-orderdata', array( $this, 'WooEnc_decryptOrderData' ) );
		}

		add_submenu_page(static::PAGE_SLUG, 'Decrypt All Data', 'Decrypt All Data', 'manage_options', 'wooenc-decrypt-userdata', array( $this, 'WooEnc_decryptUserData' ) );
	}

	/**
	 * generate output for menu page from template
	 *
	 */
	public function WooEnc_aboutPlugin() {
		require_once WOOENCRYPTION__PLUGIN_DIR . '/inc/settings.php';
	}

	public function WooEnc_listOfDataExportRequests() {
		require_once WOOENCRYPTION__PLUGIN_DIR . '/inc/dataExportRequest.php';
	}

	public function WooEnc_listOfDataErasureRequests() {
		require_once WOOENCRYPTION__PLUGIN_DIR . '/inc/dataErasureRequest.php';
	}

	public function WooEnc_decryptUserData() {
		require_once WOOENCRYPTION__PLUGIN_DIR . '/inc/decryptUserData.php';
	}

	public function WooEnc_decryptOrderData() {
		require_once WOOENCRYPTION__PLUGIN_DIR . '/inc/decryptOrderData.php';
	}

	public function users_lists_function() {
		require_once WOOENCRYPTION__PLUGIN_DIR . '/inc/encryptedUsersList.php';
	}

	/**
	 * Screen options for Encrypted Users
	 */
	public function screen_option_users()
	{
		$option = 'per_page';
		$args   = array(
			'label'   => 'Encrypted Users',
			'default' => 20,
			'option'  => 'templates_per_page'
		);

		add_screen_option( $option, $args );

		$this->users_obj = new WooEnc_Encrypted_Users();
	}

	/** Singleton instance */
	public static function get_instance() 
	{
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}