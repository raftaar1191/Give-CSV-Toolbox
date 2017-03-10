<?php
/**
 * Payments Export Class.
 *
 * This class handles payment export in batches.
 *
 * @package     Give
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_CSV_Toolbox_Donations_Export Class
 *
 * @since 1.0
 */
class Give_CSV_Toolbox_Donations_Export extends Give_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions.
	 *
	 * @var string
	 * @since 1.0
	 */
	public $export_type = 'payments';

	/**
	 * Form submission data
	 *
	 * @var array
	 * @since 1.0
	 */
	private $data = array();

	/**
	 * Form ID
	 *
	 * @var string
	 * @since 1.0
	 */
	private $form_id = '';

	/**
	 * Set the properties specific to the Customers export
	 *
	 * @since 1.0
	 *
	 * @param array $request The Form Data passed into the batch processing
	 */
	public function set_properties( $request ) {

		// Set data from form submission
		if ( isset( $_POST['form'] ) ) {
			parse_str( $_POST['form'], $this->data );
		}

		$this->form = $this->data['forms'];

		$this->form_id = ! empty( $request['forms'] ) && 0 !== $request['forms'] ? absint( $request['forms'] ) : null;

		$this->price_id = isset( $request['give_price_option'] ) && ( 'all' !== $request['give_price_option'] && '' !== $request['give_price_option'] ) ? absint( $request['give_price_option'] ) : null;

		$this->start  = isset( $request['start'] ) ? sanitize_text_field( $request['start'] ) : '';
		$this->end    = isset( $request['end'] ) ? sanitize_text_field( $request['end'] ) : '';
		$this->status = isset( $request['status'] ) ? sanitize_text_field( $request['status'] ) : 'complete';
	}

	/**
	 * Set the CSV columns.
	 *
	 * @access public
	 * @since  1.0
	 * @return array|bool $cols All the columns.
	 */
	public function csv_cols() {

		$columns = isset( $this->data['give_csv_toolbox_export_option'] ) ? $this->data['give_csv_toolbox_export_option'] : array();

		// We need columns.
		if ( empty( $columns ) ) {
			return false;
		}

		$cols = $this->get_cols( $columns );

		return $cols;
	}


	/**
	 * CSV file columns.
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	private function get_cols( $columns ) {

		$cols = array();

		foreach ( $columns as $key => $value ) {

			switch ( $key ) {
				case 'first_name' :
					$cols['first_name'] = __( 'First Name', 'give' );
					break;
				case 'last_name' :
					$cols['last_name'] = __( 'Last Name', 'give' );
					break;
				case 'email' :
					$cols['email'] = __( 'Email Address', 'give' );
					break;
				case 'address' :
					$cols['address_line1']   = __( 'Address 1', 'give' );
					$cols['address_line2']   = __( 'Address 2', 'give' );
					$cols['address_city']    = __( 'City', 'give' );
					$cols['address_state']   = __( 'State', 'give' );
					$cols['address_zip']     = __( 'Zip', 'give' );
					$cols['address_country'] = __( 'Country', 'give' );
					break;
				case 'donation_total' :
					$cols['donation_total'] = __( 'Donation Total', 'give' );
					break;
				case 'payment_gateway' :
					$cols['payment_gateway'] = __( 'Payment Gateway', 'give' );
					break;
				case 'form_id' :
					$cols['form_id'] = __( 'Form ID', 'give' );
					break;
				case 'form_title' :
					$cols['form_title'] = __( 'Form Title', 'give' );
					break;
				case 'form_level_id' :
					$cols['form_level_id'] = __( 'Level ID', 'give' );
					break;
				case 'form_level_title' :
					$cols['form_level_title'] = __( 'Level Title', 'give' );
					break;
				case 'donation_date' :
					$cols['donation_date'] = __( 'Donation Date', 'give' );
					break;
				case 'donation_time' :
					$cols['donation_time'] = __( 'Donation Time', 'give' );
					break;
				case 'userid' :
					$cols['userid'] = __( 'User ID', 'give' );
					break;
				case 'donorid' :
					$cols['donorid'] = __( 'Donor ID', 'give' );
					break;
				case 'donor_ip' :
					$cols['donor_ip'] = __( 'Donor IP Address', 'give' );
					break;

			}
		}

		return $cols;

	}


	/**
	 * Get the Export Data.
	 *
	 * @access public
	 * @since  1.0
	 * @global object $wpdb Used to query the database using the WordPress database API.
	 * @return array $data The data for the CSV file.
	 */
	public function get_data() {

		$data    = array();
		$columns = $this->csv_cols();
		$i = 0;
		
		$args = array(
			'number'     => 30,
			'page'       => $this->step,
			'status'     => $this->status,
			'give_forms' => array( $this->form_id ),
		);

		// Date query.
		if ( ! empty( $this->start ) || ! empty( $this->end ) ) {

			$args['date_query'] = array(
				array(
					'after'     => date( 'Y-n-d 00:00:00', strtotime( $this->start ) ),
					'before'    => date( 'Y-n-d 23:59:59', strtotime( $this->end ) ),
					'inclusive' => true,
				),
			);

		}

		// Check for price option
		if ( null !== $this->price_id ) {
			$args['meta_query'] = array(
				array(
					'key'   => '_give_payment_price_id',
					'value' => (int) $this->price_id,
				),
			);
		}

		// Payment query.
		$payments = give_get_payments( $args );

		if ( $payments ) {

			foreach ( $payments as $payment ) {

				$payment_meta = give_get_payment_meta( $payment->ID );
				$user_info    = give_get_payment_meta_user_info( $payment->ID );
				$total        = give_get_payment_amount( $payment->ID );
				$user_id      = isset( $user_info['id'] ) && $user_info['id'] != - 1 ? $user_info['id'] : $user_info['email'];

				$payment = new Give_Payment($payment->ID);

				if ( is_numeric( $user_id ) ) {
					$user = get_userdata( $user_id );
				} else {
					$user = false;
				}

				$customer = new Give_Customer( give_get_payment_customer_id( $payment->ID ) );
				$address  = '';
				if ( isset( $customer->user_id ) && $customer->user_id > 0 ) {
					$address = give_get_donor_address( $customer->user_id );
				}
				$name_array = explode( ' ', $customer->name );

				// Set columns
				if ( ! empty( $columns['first_name'] ) ) {
					$data[$i]['first_name'] = isset( $name_array[0] ) ? $name_array[0] : '';
				}
				if ( ! empty( $columns['last_name'] ) ) {
					$data[$i]['last_name'] = ( isset( $name_array[1] ) ? $name_array[1] : '' ) . ( isset( $name_array[2] ) ? ' ' . $name_array[2] : '' ) . ( isset( $name_array[3] ) ? ' ' . $name_array[3] : '' );
				}
				if ( ! empty( $columns['email'] ) ) {
					$data[$i]['email'] = $customer->email;
				}
				if ( ! empty( $columns['address_line1'] ) ) {
					$data[$i]['address_line1']   = isset( $address['line1'] ) ? $address['line1'] : '';
					$data[$i]['address_line2']   = isset( $address['line2'] ) ? $address['line2'] : '';
					$data[$i]['address_city']    = isset( $address['city'] ) ? $address['city'] : '';
					$data[$i]['address_state']   = isset( $address['state'] ) ? $address['state'] : '';
					$data[$i]['address_zip']     = isset( $address['zip'] ) ? $address['zip'] : '';
					$data[$i]['address_country'] = isset( $address['country'] ) ? $address['country'] : '';
				}

				if ( ! empty( $columns['donation_total'] ) ) {
					$data[$i]['donation_total'] = give_currency_filter( give_format_amount( give_get_payment_amount( $payment->ID ) ) );
				}

				if ( ! empty( $columns['payment_gateway'] ) ) {
					$data[$i]['payment_gateway'] = $payment->gateway;
				}


				if ( ! empty( $columns['form_id'] ) ) {
					$data[$i]['form_id'] = $payment->form_id;
				}

				if ( ! empty( $columns['form_title'] ) ) {
					$data[$i]['form_title'] = get_the_title( $payment->form_id );
				}

				if ( ! empty( $columns['form_level_id'] ) ) {
					$data[$i]['form_level_id'] = $payment->price_id;
				}

				if ( ! empty( $columns['form_level_title'] ) ) {
					$var_prices = give_has_variable_prices( $payment_meta['form_id'] );
					if ( empty( $var_prices ) ) {
						$data[$i]['form_level_title'] = '';
					} else {
						$prices_atts = '';
						if ( $variable_prices = give_get_variable_prices( $payment_meta['form_id'] ) ) {
							foreach ( $variable_prices as $variable_price ) {
								$prices_atts[ $variable_price['_give_id']['level_id'] ] = give_format_amount( $variable_price['_give_amount'] );
							}
						}
						$data[$i]['form_level_title'] = give_get_price_option_name( $payment->form_id, $payment->price_id );
					}
				}


				// 	case 'form_level_id' :
				// 		$cols['form_level_id'] = __( 'Level ID', 'give' );
				// 		break;
				// 	case 'form_level_title' :
				// 		$cols['form_level_title'] = __( 'Level Title', 'give' );
				// 		break;
				// 	case 'donation_date' :
				// 		$cols['donation_date'] = __( 'Donation Date', 'give' );
				// 		break;
				// 	case 'donation_time' :
				// 		$cols['donation_time'] = __( 'Donation Time', 'give' );
				// 		break;
				// 	case 'userid' :
				// 		$cols['userid'] = __( 'User ID', 'give' );
				// 		break;
				// 	case 'donorid' :
				// 		$cols['donorid'] = __( 'Donor ID', 'give' );
				// 		break;
				// 	case 'donor_ip' :
				// 		$cols['donor_ip'] = __( 'Donor IP Address', 'give' );
				// 		break;

				$i++;
			}
// echo "<pre>";
// var_dump($payment);
// var_dump($data);
// echo "</pre>";
// die();
			$data = apply_filters( 'give_export_get_data', $data );
			$data = apply_filters( "give_export_get_data_{$this->export_type}", $data );

			return $data;

		}

		return array();

	}

	/**
	 * Return the calculated completion percentage.
	 *
	 * @since 1.0
	 * @return int
	 */
	public function get_percentage_complete() {

		$status = $this->status;
		$args   = array(
			'start-date' => date( 'n/d/Y', strtotime( $this->start ) ),
			'end-date'   => date( 'n/d/Y', strtotime( $this->end ) ),
		);

		if ( 'any' == $status ) {
			$total = array_sum( (array) give_count_payments( $args ) );
		} else {
			$total = give_count_payments( $args )->$status;
		}

		$percentage = 100;

		if ( $total > 0 ) {
			$percentage = ( ( 30 * $this->step ) / $total ) * 100;
		}

		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

}
