<?php
/**
 * Give Settings Page/Tab
 *
 * @package     Give
 * @subpackage  Classes/Give_Settings_Data
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Give_Settings_CSV' ) ) :

	/**
	 * Give_Settings_CSV.
	 *
	 * @sine 1.0
	 */
	class Give_Settings_CSV {

		/**
		 * Setting page id.
		 *
		 * @since 1.0
		 * @var   string
		 */
		protected $id = '';

		/**
		 * Setting page label.
		 *
		 * @since 1.0
		 * @var   string
		 */
		protected $label = '';

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'csv-toolbox';
			$this->label = esc_html__( 'CSV Toolbox', 'give' );

			add_filter( 'give-tools_tabs_array', array( $this, 'add_settings_page' ), 99 );
			add_action( "give-tools_settings_{$this->id}_page", array( $this, 'output' ) );

			add_action( 'give_admin_field_csv_toolbox', array( $this, 'view_toolbox' ) );

			// Do not use main form for this tab.
			if ( give_get_current_setting_tab() === $this->id ) {
				add_action( 'give-tools_open_form', '__return_empty_string' );
				add_action( 'give-tools_close_form', '__return_empty_string' );
			}
		}

		/**
		 * Add this page to settings.
		 *
		 * @since  1.0
		 *
		 * @param  array $pages Lst of pages.
		 *
		 * @return array
		 */
		public function add_settings_page( $pages ) {
			$pages[ $this->id ] = $this->label;

			return $pages;
		}


		/**
		 * View the UI.
		 */
		function view_toolbox() {
			?>

			<div class="give-csv-toolbox">
				<div class="give-csv-toolbox-title-wrap">
					<h3><span><?php esc_html_e( 'Export Donations and Custom Fields to CSV', 'give' ); ?></span></h3>
					<p><?php _e( 'Download an export of donors for specific donation forms with the option to include custom fields.', 'give' ); ?></p>
				</div>
				<td>
					<form method="post" id="give-csv-toolbox">

						<?php
						$args = array(
							'name'   => 'forms',
							'id'     => 'give_customer_export_form',
							'chosen' => true,
						);
						echo Give()->html->forms_dropdown( $args ); ?>

						<div id="give-csv-toolbox-standard-fields" class="give-clearfix">
							<h4><?php esc_html_e( 'Standard Columns:', 'give' ); ?></h4>
							<ul id="give-export-option-ul">
								<li>
									<label for="give-export-fullname"><input type="checkbox" checked
																			 name="give_export_option[full_name]"
																			 id="give-export-fullname"><?php esc_html_e( 'Name', 'give' ); ?>
									</label>
								</li>
								<li>
									<label for="give-export-email"><input type="checkbox" checked
																		  name="give_export_option[email]"
																		  id="give-export-email"><?php esc_html_e( 'Email', 'give' ); ?>
									</label>
								</li>
								<li>
									<label for="give-export-address"><input type="checkbox" checked
																			name="give_export_option[address]"
																			id="give-export-address"><?php esc_html_e( 'Address', 'give' ); ?>
									</label>
								</li>
								<li>
									<label for="give-export-userid"><input type="checkbox" checked
																		   name="give_export_option[userid]"
																		   id="give-export-userid"><?php esc_html_e( 'User ID', 'give' ); ?>
									</label>
								</li>
								<li>
									<label for="give-export-first-donation-date"><input type="checkbox" checked
																						name="give_export_option[date_first_donated]"
																						id="give-export-first-donation-date"><?php esc_html_e( 'First Donation Date', 'give' ); ?>
									</label>
								</li>
								<li>
									<label for="give-export-donation-number"><input type="checkbox" checked
																					name="give_export_option[donations]"
																					id="give-export-donation-number"><?php esc_html_e( 'Number of Donations', 'give' ); ?>
									</label>
								</li>
								<li>
									<label for="give-export-donation-sum"><input type="checkbox" checked
																				 name="give_export_option[donation_sum]"
																				 id="give-export-donation-sum"><?php esc_html_e( 'Total Donated', 'give' ); ?>
									</label>
								</li>
							</ul>
						</div>

						<div id="give-csv-toolbox-custom-fields-wrap">
							<!-- content here loaded via AJAX -->
							<h4><?php esc_html_e( 'Custom Field Columns:', 'give' ); ?></h4>
							<p><?php esc_html_e( 'The following fields may have been created by Form Field Manager, custom code, or another plugin.', 'give' ); ?></p>


							<div id="csv-toolbox-non-hidden-fields-wrap">
								<ul id="give-csv-toolbox-standard-field-list" class="give-csv-toolbox-field-list">
								</ul>

							</div>

							<h4><?php esc_html_e( 'Hidden Custom Field Columns:', 'give' ); ?></h4>
                            <p><?php esc_html_e( 'The following hidden custom fields contain data created by Give Core, a Give Add-on, another plugin, etc. Hidden fields are generally used for programming logic, but you may contain data you would like to export.', 'give' ); ?></p>
							<div id="csv-toolbox-hidden-fields-wrap">
								<ul id="give-csv-toolbox-hidden-field-list" class="give-csv-toolbox-field-list">
								</ul>
							</div>

						</div>

						<?php wp_nonce_field( 'give_ajax_export', 'give_ajax_export' ); ?>
						<input type="hidden" name="give-export-class" value="Give_Batch_Customers_Export"/>
						<input type="hidden" name="give_export_option[query_id]"
							   value="<?php echo uniqid( 'give_' ); ?>"/>
						<input type="hidden" name="give_action" value="email_csv_toolbox_export"/>

						<div id="give-csv-toolbox-submit-wrap">
							<input type="submit" value="<?php esc_attr_e( 'Generate CSV', 'give' ); ?>"
								   class="button button-primary"/>
						</div>

					</form>
				</td>
			</div>

		<?php }


		/**
		 * Get settings array.
		 *
		 * @since  1.0
		 * @return array
		 */
		public function get_settings() {
			// Hide save button.
			$GLOBALS['give_hide_save_button'] = true;

			// Get settings.
			$settings = apply_filters( 'give_settings_csv_toolbox', array(
				array(
					'id'         => 'give_tools_tools',
					'type'       => 'title',
					'table_html' => false,
				),
				array(
					'id'   => 'csv_toolbox',
					'name' => esc_html__( 'CSV Toolbox', 'give' ),
					'type' => 'csv_toolbox',
				),
				array(
					'id'         => 'give_tools_tools',
					'type'       => 'sectionend',
					'table_html' => false,
				),
			) );

			/**
			 * Filter the settings.
			 *
			 * @since  1.0
			 *
			 * @param  array $settings
			 */
			$settings = apply_filters( 'give_get_settings_' . $this->id, $settings );

			// Output.
			return $settings;
		}

		/**
		 * Output the settings.
		 *
		 * @since  1.0
		 * @return void
		 */
		public function output() {
			$settings = $this->get_settings();

			Give_Admin_Settings::output_fields( $settings, 'give_settings' );
		}
	}

endif;

return new Give_Settings_CSV();
