<?php

if( !defined( 'ABSPATH' ) ) exit;  // Exit if accessed directly

/**
 * WooEnc_Requests_Table class.
 *
 * @since 4.9.6
 */
class WooEnc_Requests_Table extends WP_List_Table {

	/**
	 * Action name for the requests this table will work with. Classes
	 * which inherit from WooEnc_Requests_Table should define this.
	 *
	 * Example: 'export_personal_data'.
	 *
	 * @since 4.9.6
	 *
	 * @var string $request_type Name of action.
	 */
	protected $request_type = 'INVALID';

	/**
	 * Post type to be used.
	 *
	 * @since 4.9.6
	 *
	 * @var string $post_type The post type.
	 */
	protected $post_type = 'INVALID';

	/**
	 * Get columns to show in the list table.
	 *
	 * @since 4.9.6
	 *
	 * @return array Array of columns.
	 */
	public function get_columns() {
		$columns = array(
			'cb'                => '<input type="checkbox" />',
			'email'             => __( 'Requester' ),
			'status'            => __( 'Status' ),
			'created_timestamp' => __( 'Requested' ),
			'next_steps'        => __( 'Next Steps' ),
		);
		return $columns;
	}

	/**
	 * Get a list of sortable columns.
	 *
	 * @since 4.9.6
	 *
	 * @return array Default sortable columns.
	 */
	protected function get_sortable_columns() {
		return array();
	}

	/**
	 * Default primary column.
	 *
	 * @since 4.9.6
	 *
	 * @return string Default primary column name.
	 */
	protected function get_default_primary_column_name() {
		return 'email';
	}

	/**
	 * Count number of requests for each status.
	 *
	 * @since 4.9.6
	 *
	 * @return object Number of posts for each status.
	 */
	protected function get_request_counts() {
		global $wpdb;

		$cache_key = $this->post_type . '-' . $this->request_type;
		$counts    = wp_cache_get( $cache_key, 'counts' );

		if ( false !== $counts ) {
			return $counts;
		}

		$query = "
			SELECT post_status, COUNT( * ) AS num_posts
			FROM {$wpdb->posts}
			WHERE post_type = %s
			AND post_name = %s
			GROUP BY post_status";

		$results = (array) $wpdb->get_results( $wpdb->prepare( $query, $this->post_type, $this->request_type ), ARRAY_A );
		$counts  = array_fill_keys( get_post_stati(), 0 );

		foreach ( $results as $row ) {
			$counts[ $row['post_status'] ] = $row['num_posts'];
		}

		$counts = (object) $counts;
		wp_cache_set( $cache_key, $counts, 'counts' );

		return $counts;
	}

	/**
	 * Get an associative array ( id => link ) with the list of views available on this table.
	 *
	 * @since 4.9.6
	 *
	 * @return array Associative array of views in the format of $view_name => $view_markup.
	 */
	protected function get_views() {
		$current_status = isset( $_REQUEST['filter-status'] ) ? sanitize_text_field( $_REQUEST['filter-status'] ) : '';
		$statuses       = _wp_privacy_statuses();
		$views          = array();
		$admin_url      = admin_url( 'admin.php?page=' . $this->request_type );
		$counts         = $this->get_request_counts();

		$current_link_attributes = empty( $current_status ) ? ' class="current" aria-current="page"' : '';
		$views['all']            = '<a href="' . esc_url( $admin_url ) . "\" $current_link_attributes>" . esc_html__( 'All' ) . ' (' . absint( array_sum( (array) $counts ) ) . ')</a>';

		foreach ( $statuses as $status => $label ) {
			$current_link_attributes = $status === $current_status ? ' class="current" aria-current="page"' : '';
			$views[ $status ]        = '<a href="' . esc_url( add_query_arg( 'filter-status', $status, $admin_url ) ) . "\" $current_link_attributes>" . esc_html( $label ) . ' (' . absint( $counts->$status ) . ')</a>';
		}

		return $views;
	}

	/**
	 * Get bulk actions.
	 *
	 * @since 4.9.6
	 *
	 * @return array List of bulk actions.
	 */
	protected function get_bulk_actions() {
		return array(
			'delete' => __( 'Remove' ),
			'resend' => __( 'Resend email' ),
		);
	}

	/**
	 * Process bulk actions.
	 *
	 * @since 4.9.6
	 */
	public function process_bulk_action() {
		$action      = $this->current_action();
		$request_ids = isset( $_REQUEST['request_id'] ) ? wp_parse_id_list( wp_unslash( $_REQUEST['request_id'] ) ) : array(); // WPCS: input var ok, CSRF ok.
		$count       = 0;

		if ( $request_ids ) {
			check_admin_referer( 'bulk-privacy_requests' );
		}

		switch ( $action ) {
			case 'delete':
				foreach ( $request_ids as $request_id ) {
					if ( wp_delete_post( $request_id, true ) ) {
						$count ++;
					}
				}

				add_settings_error(
					'bulk_action',
					'bulk_action',
					/* translators: %d: number of requests */
					sprintf( _n( 'Deleted %d request', 'Deleted %d requests', $count ), $count ),
					'updated'
				);
				break;
			case 'resend':
				foreach ( $request_ids as $request_id ) {
					$resend = _wp_privacy_resend_request( $request_id );

					if ( $resend && ! is_wp_error( $resend ) ) {
						$count++;
					}
				}

				add_settings_error(
					'bulk_action',
					'bulk_action',
					/* translators: %d: number of requests */
					sprintf( _n( 'Re-sent %d request', 'Re-sent %d requests', $count ), $count ),
					'updated'
				);
				break;
		}
	}

	/**
	 * Prepare items to output.
	 *
	 * @since 4.9.6
	 */
	public function prepare_items() {
		global $wpdb;

		$primary               = $this->get_primary_column_name();
		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns(),
			$primary,
		);

		$this->items    = array();
		$posts_per_page = 20;
		$args           = array(
			'post_type'      => $this->post_type,
			'post_name__in'  => array( $this->request_type ),
			'posts_per_page' => $posts_per_page,
			'offset'         => isset( $_REQUEST['paged'] ) ? max( 0, absint( $_REQUEST['paged'] ) - 1 ) * $posts_per_page : 0,
			'post_status'    => 'any',
			's'              => isset( $_REQUEST['s'] ) ? sanitize_text_field( $_REQUEST['s'] ) : '',
		);

		if ( ! empty( $_REQUEST['filter-status'] ) ) {
			$filter_status       = isset( $_REQUEST['filter-status'] ) ? sanitize_text_field( $_REQUEST['filter-status'] ) : '';
			$args['post_status'] = $filter_status;
		}

		$requests_query = new WP_Query( $args );
		$requests       = $requests_query->posts;

		foreach ( $requests as $request ) {
			$this->items[] = wp_get_user_request_data( $request->ID );
		}

		$this->items = array_filter( $this->items );

		$this->set_pagination_args(
			array(
				'total_items' => $requests_query->found_posts,
				'per_page'    => $posts_per_page,
			)
		);
	}

	/**
	 * Checkbox column.
	 *
	 * @since 4.9.6
	 *
	 * @param WP_User_Request $item Item being shown.
	 * @return string Checkbox column markup.
	 */
	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="request_id[]" value="%1$s" /><span class="spinner"></span>', esc_attr( $item->ID ) );
	}

	/**
	 * Status column.
	 *
	 * @since 4.9.6
	 *
	 * @param WP_User_Request $item Item being shown.
	 * @return string Status column markup.
	 */
	public function column_status( $item ) {
		$status        = get_post_status( $item->ID );
		$status_object = get_post_status_object( $status );

		if ( ! $status_object || empty( $status_object->label ) ) {
			return '-';
		}

		$timestamp = false;

		switch ( $status ) {
			case 'request-confirmed':
				$timestamp = $item->confirmed_timestamp;
				break;
			case 'request-completed':
				$timestamp = $item->completed_timestamp;
				break;
		}

		echo '<span class="status-label status-' . esc_attr( $status ) . '">';
		echo esc_html( $status_object->label );

		if ( $timestamp ) {
			echo ' (' . $this->get_timestamp_as_date( $timestamp ) . ')';
		}

		echo '</span>';
	}

	/**
	 * Convert timestamp for display.
	 *
	 * @since 4.9.6
	 *
	 * @param int $timestamp Event timestamp.
	 * @return string Human readable date.
	 */
	protected function get_timestamp_as_date( $timestamp ) {
		if ( empty( $timestamp ) ) {
			return '';
		}

		$time_diff = current_time( 'timestamp', true ) - $timestamp;

		if ( $time_diff >= 0 && $time_diff < DAY_IN_SECONDS ) {
			/* translators: human readable timestamp */
			return sprintf( __( '%s ago' ), human_time_diff( $timestamp ) );
		}

		return date_i18n( get_option( 'date_format' ), $timestamp );
	}

	/**
	 * Default column handler.
	 *
	 * @since 4.9.6
	 *
	 * @param WP_User_Request $item        Item being shown.
	 * @param string          $column_name Name of column being shown.
	 * @return string Default column output.
	 */
	public function column_default( $item, $column_name ) {
		$cell_value = $item->$column_name;

		if ( in_array( $column_name, array( 'created_timestamp' ), true ) ) {
			return $this->get_timestamp_as_date( $cell_value );
		}

		return $cell_value;
	}

	/**
	 * Actions column. Overridden by children.
	 *
	 * @since 4.9.6
	 *
	 * @param WP_User_Request $item Item being shown.
	 * @return string Email column markup.
	 */
	public function column_email( $item ) {
		return sprintf( '<a href="%1$s">%2$s</a> %3$s', esc_url( 'mailto:' . $item->email ), $item->email, $this->row_actions( array() ) );
	}

	/**
	 * Next steps column. Overridden by children.
	 *
	 * @since 4.9.6
	 *
	 * @param WP_User_Request $item Item being shown.
	 */
	public function column_next_steps( $item ) {}

	/**
	 * Generates content for a single row of the table,
	 *
	 * @param WP_User_Request $item The current item.
	 */
	public function single_row( $item ) {
		$status = $item->status;

		echo '<tr id="request-' . esc_attr( $item->ID ) . '" class="status-' . esc_attr( $status ) . '">';
		$this->single_row_columns( $item );
		echo '</tr>';
	}

	/**
	 * Embed scripts used to perform actions. Overridden by children.
	 */
	public function embed_scripts() {}
}

/**
 * WooEnc_Data_Export_Requests_Table class.
 */
class WooEnc_Data_Export_Requests_Table extends WooEnc_Requests_Table {
	/**
	 * Action name for the requests this table will work with.
	 *
	 * @var string $request_type Name of action.
	 */
	protected $request_type = 'wooenc_export_personal_data';

	/**
	 * Post type for the requests.
	 *
	 * @var string $post_type The post type.
	 */
	protected $post_type = 'user_request';

	/**
	 * Actions column.
	 *
	 * @param WP_User_Request $item Item being shown.
	 * @return string Email column markup.
	 */
	public function column_email( $item ) {
		$exporters       = apply_filters( 'wp_privacy_personal_data_exporters', array() );
		$exporters_count = count( $exporters );
		$request_id      = $item->ID;
		$nonce           = wp_create_nonce( 'wp-privacy-export-personal-data-' . $request_id );

		$download_data_markup = '<div class="export-personal-data" ' .
			'data-exporters-count="' . esc_attr( $exporters_count ) . '" ' .
			'data-request-id="' . esc_attr( $request_id ) . '" ' .
			'data-nonce="' . esc_attr( $nonce ) .
			'">';

		$download_data_markup .= '<span class="export-personal-data-idle"><button type="button" class="button-link export-personal-data-handle">' . __( 'Download Personal Data' ) . '</button></span>' .
			'<span style="display:none" class="export-personal-data-processing" >' . __( 'Downloading Data...' ) . '</span>' .
			'<span style="display:none" class="export-personal-data-success"><button type="button" class="button-link export-personal-data-handle">' . __( 'Download Personal Data Again' ) . '</button></span>' .
			'<span style="display:none" class="export-personal-data-failed">' . __( 'Download has failed.' ) . ' <button type="button" class="button-link">' . __( 'Retry' ) . '</button></span>';

		$download_data_markup .= '</div>';

		$row_actions = array(
			'download-data' => $download_data_markup,
		);

		return sprintf( '<a href="%1$s">%2$s</a> %3$s', esc_url( 'mailto:' . $item->email ), $item->email, $this->row_actions( $row_actions ) );
	}

	/**
	 * Displays the next steps column.
	 *
	 * @param WP_User_Request $item Item being shown.
	 */
	public function column_next_steps( $item ) {
		$status = $item->status;

		switch ( $status ) {
			case 'request-pending':
				esc_html_e( 'Waiting for confirmation' );
				break;
			case 'request-confirmed':
				$exporters       = apply_filters( 'wp_privacy_personal_data_exporters', array() );
				$exporters_count = count( $exporters );
				$request_id      = $item->ID;
				$nonce           = wp_create_nonce( 'wp-privacy-export-personal-data-' . $request_id );

				echo '<div class="export-personal-data" ' .
					'data-send-as-email="1" ' .
					'data-exporters-count="' . esc_attr( $exporters_count ) . '" ' .
					'data-request-id="' . esc_attr( $request_id ) . '" ' .
					'data-nonce="' . esc_attr( $nonce ) .
					'">';

				?>
				<span class="export-personal-data-idle"><button type="button" class="button export-personal-data-handle"><?php _e( 'Email Data' ); ?></button></span>
				<span style="display:none" class="export-personal-data-processing button updating-message" ><?php _e( 'Sending Email...' ); ?></span>
				<span style="display:none" class="export-personal-data-success success-message" ><?php _e( 'Email sent.' ); ?></span>
				<span style="display:none" class="export-personal-data-failed"><?php _e( 'Email could not be sent.' ); ?> <button type="button" class="button export-personal-data-handle"><?php _e( 'Retry' ); ?></button></span>
				<?php

				echo '</div>';
				break;
			case 'request-failed':
				submit_button( __( 'Retry' ), 'secondary', 'privacy_action_email_retry[' . $item->ID . ']', false );
				break;
			case 'request-completed':
				echo '<a href="' . esc_url( wp_nonce_url( add_query_arg( array(
					'action'     => 'delete',
					'request_id' => array( $item->ID ),
				), admin_url( 'admin.php?page=wooenc_export_personal_data' ) ), 'bulk-privacy_requests' ) ) . '" class="button">' . esc_html__( 'Remove request' ) . '</a>';
				break;
		}
	}
}

/**
 * WooEnc_Data_Removal_Requests_Table class.
 */
class WooEnc_Data_Removal_Requests_Table extends WooEnc_Requests_Table {
	/**
	 * Action name for the requests this table will work with.
	 *
	 * @var string $request_type Name of action.
	 */
	protected $request_type = 'wooenc_remove_personal_data';

	/**
	 * Post type for the requests.
	 *
	 * @var string $post_type The post type.
	 */
	protected $post_type = 'user_request';

	/**
	 * Actions column.
	 *
	 * @param WP_User_Request $item Item being shown.
	 * @return string Email column markup.
	 */
	public function column_email( $item ) {
		$row_actions = array();

		// Allow the administrator to "force remove" the personal data even if confirmation has not yet been received.
		$status = $item->status;
		if ( 'request-confirmed' !== $status ) {
			$erasers       = apply_filters( 'wp_privacy_personal_data_erasers', array() );
			$erasers_count = count( $erasers );
			$request_id    = $item->ID;
			$nonce         = wp_create_nonce( 'wp-privacy-erase-personal-data-' . $request_id );

			$remove_data_markup = '<div class="remove-personal-data force-remove-personal-data" ' .
				'data-erasers-count="' . esc_attr( $erasers_count ) . '" ' .
				'data-request-id="' . esc_attr( $request_id ) . '" ' .
				'data-nonce="' . esc_attr( $nonce ) .
				'">';

			$remove_data_markup .= '<span class="remove-personal-data-idle"><button type="button" class="button-link remove-personal-data-handle">' . __( 'Force Erase Personal Data' ) . '</button></span>' .
				'<span style="display:none" class="remove-personal-data-processing" >' . __( 'Erasing Data...' ) . '</span>' .
				'<span style="display:none" class="remove-personal-data-failed">' . __( 'Force Erase has failed.' ) . ' <button type="button" class="button-link remove-personal-data-handle">' . __( 'Retry' ) . '</button></span>';

			$remove_data_markup .= '</div>';

			$row_actions = array(
				'remove-data' => $remove_data_markup,
			);
		}

		return sprintf( '<a href="%1$s">%2$s</a> %3$s', esc_url( 'mailto:' . $item->email ), $item->email, $this->row_actions( $row_actions ) );
	}

	/**
	 * Next steps column.
	 *
	 * @param WP_User_Request $item Item being shown.
	 */
	public function column_next_steps( $item ) {
		$status = $item->status;

		switch ( $status ) {
			case 'request-pending':
				esc_html_e( 'Waiting for confirmation' );
				break;
			case 'request-confirmed':
				$erasers       = apply_filters( 'wp_privacy_personal_data_erasers', array() );
				$erasers_count = count( $erasers );
				$request_id    = $item->ID;
				$nonce         = wp_create_nonce( 'wp-privacy-erase-personal-data-' . $request_id );

				echo '<div class="remove-personal-data" ' .
					'data-force-erase="1" ' .
					'data-erasers-count="' . esc_attr( $erasers_count ) . '" ' .
					'data-request-id="' . esc_attr( $request_id ) . '" ' .
					'data-nonce="' . esc_attr( $nonce ) .
					'">';

				?>
				<span class="remove-personal-data-idle"><button type="button" class="button remove-personal-data-handle"><?php _e( 'Erase Personal Data' ); ?></button></span>
				<span style="display:none" class="remove-personal-data-processing button updating-message" ><?php _e( 'Erasing Data...' ); ?></span>
				<span style="display:none" class="remove-personal-data-failed"><?php _e( 'Erasing Data has failed.' ); ?> <button type="button" class="button remove-personal-data-handle"><?php _e( 'Retry' ); ?></button></span>
				<?php

				echo '</div>';

				break;
			case 'request-failed':
				submit_button( __( 'Retry' ), 'secondary', 'privacy_action_email_retry[' . $item->ID . ']', false );
				break;
			case 'request-completed':
				echo '<a href="' . esc_url( wp_nonce_url( add_query_arg( array(
					'action'     => 'delete',
					'request_id' => array( $item->ID ),
				), admin_url( 'admin.php?page=wooenc_remove_personal_data' ) ), 'bulk-privacy_requests' ) ) . '" class="button">' . esc_html__( 'Remove request' ) . '</a>';
				break;
		}
	}

}
