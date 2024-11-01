<?php

if( !defined( 'ABSPATH' ) ) exit;  // Exit if accessed directly

/**
* WooEnc_Encrypted_Users Class
*/
class WooEnc_Encrypted_Users extends WP_List_Table {

	/** Class constructor */
	public function __construct() 
	{
		parent::__construct( array(
			'singular' => __( 'User', 'wp-encryption' ), //singular name of the listed records
			'plural'   => __( 'Users', 'wp-encryption' ), //plural name of the listed records
			'ajax'     => true //does this table support ajax?
		) );
	}

	/**
	 * Retrieve users data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public static function get_users( $per_page = 20, $page_number = 1 ) 
	{
		global $wpdb;
		$sql = "SELECT * FROM {$wpdb->prefix}usermeta AS um RIGHT JOIN {$wpdb->prefix}users AS u ON u.ID = um.user_id WHERE um.meta_key = 'user_logged_in_successfully' AND um.meta_value = 0";

		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;


		$result = $wpdb->get_results( $sql, 'ARRAY_A' );

		return $result;
	}

	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() 
	{
		global $wpdb;

		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}usermeta AS um RIGHT JOIN {$wpdb->prefix}users AS u ON u.ID = um.user_id WHERE um.meta_key = 'user_logged_in_successfully' AND um.meta_value = 0";

		return $wpdb->get_var( $sql );
	}

	/** Text displayed when no user data is available */
	public function no_items() 
	{
		_e( 'No users yet.', 'wp-encryption' );
	}

	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_id
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_id ) 
	{
		switch ( $column_id ) {
			case 'user_login':
			case 'user_name':
			case 'user_email':
			case 'display_name':
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() 
	{
		$columns = array( 
			'ID'      	=> __( 'User ID', 'wp-encryption' ),
			'user_login'    => __( 'Username', 'wp-encryption' ),
			'user_name'    => __( 'Name', 'wp-encryption' ),
			'user_email'	=> __( 'Email', 'wp-encryption' ),
			'display_name'	=> __( 'Display Name', 'wp-encryption' ),
		);

		return $columns;
	}

	/**
	 * Method for ID column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_ID( $item ) 
	{
		$id = '<strong>' . $item['ID'] . '</strong>';
		return $id;
	}

	/**
	 * Method for user_login column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_user_login( $item ) 
	{
		$data = $item['user_login'];
		if ( isset($_POST['encryptionKeyField']) )
   		{
			$data = $this->verify_data($data, $_POST['encryptionKeyField']);
		}
		$user_login = '<strong>' . $data . '</strong>';
		return $user_login;
	}

	/**
	 * Method for user_name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_user_name( $item ) 
	{
		global $wpdb;
		$usermetas = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}usermeta where user_id = ".$item['ID'] );

		foreach ( $usermetas as $usermeta ) 
		{
			if($usermeta->meta_key == 'first_name') {
        		$first_name = $usermeta->meta_value;
			}
			if($usermeta->meta_key == 'last_name') {
        		$last_name = $usermeta->meta_value;
			}
		}
		if ( isset($_POST['encryptionKeyField']) )
   		{
			$first_name = $this->verify_data($first_name, $_POST['encryptionKeyField']);
			$last_name = $this->verify_data($last_name, $_POST['encryptionKeyField']);
		}
		$user_login = $first_name .' '. $last_name;
		return $user_login;
	}

	/**
	 * Method for user_email column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_user_email( $item ) 
	{
		$data = $item['user_email'];
		if ( isset($_POST['encryptionKeyField']) )
   		{
			$data = $this->verify_data($data, $_POST['encryptionKeyField']);
		}
		$user_email = $data;
		return $user_email;
	}

	/**
	 * Method for display_name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_display_name( $item ) 
	{
		$data = $item['display_name'];
		if ( isset($_POST['encryptionKeyField']) )
   		{
			$data = $this->verify_data($data, $_POST['encryptionKeyField']);
		}
		$display_name = $data;
		return $display_name;
	}

	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() 
	{
		$this->_column_headers = $this->get_column_info();

		$per_page     = $this->get_items_per_page( 'templates_per_page', 20 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args( array(
			'total_items' => $total_items, 	//WE have to calculate the total number of items
			'per_page'    => $per_page 	//WE have to determine how many items to show on a page
		) );

		$this->items = self::get_users( $per_page, $current_page );
	}

	/**
	 * Verify encryption key and return data.
	 */
	public function verify_data($data, $passkey) {

		$key = WooEnc_get_key();

		$key = base64_encode($key);

		if($key == $passkey) {
			$passkey = base64_decode($passkey);
			return WooEnc_safeDecrypt($data, $passkey);
		} else {
			return $data;
		}
		return $data;
	}
}

add_filter('manage_users_columns', 'wooenc_add_user_email_column');
function wooenc_add_user_email_column($columns) {
    $columns['user_email'] = 'Encrypted Email';
    unset($columns['email']);
    return $columns;
}

add_action('manage_users_custom_column', 'wooenc_show_user_email_column_content', 10, 10);
function wooenc_show_user_email_column_content($value, $column_name, $user_id) {

	global $wpdb;
	$user_table = $wpdb->prefix . 'users';

	$user = $wpdb->get_row( "SELECT * FROM $user_table WHERE ID = $user_id" );

	if ( 'user_email' == $column_name )
		return $user->user_email;

    return $value;
}