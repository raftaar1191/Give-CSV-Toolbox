/**
 * Give CSV Toolbox JS.

 * @package:     Give
 * @subpackage:  Assets/JS
 * @copyright:   Copyright (c) 2016, WordImpress
 * @license:     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
jQuery.noConflict();
(function ($) {

	/**
	 * On DOM ready. Kicks it off.
	 */
	$(function () {

		toggle_csv_toolbox_fields();

	});

	/**
	 * Toggles fields. Triggers AJAX.
	 */
	function toggle_csv_toolbox_fields() {

		$( 'select[name="forms"]' ).chosen().change(function () {

			$( '#give-csv-toolbox-standard-fields, #give-csv-toolbox-custom-fields-wrap, #give-csv-toolbox-submit-wrap' ).hide();

			var give_form_id,
				variable_prices_html_container = $( '.give-donation-level' );

			// Check for form ID.
			if ( ! ( give_form_id = $( this ).val() )) {
				return false;
			}

			// Clear out fields.
			$( '.give-csv-toolbox-field-list' ).empty();

			// Ajax.
			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					form_id: give_form_id,
					// level_id: '',
					action: 'give_csv_toolbox_get_custom_fields'
				},
				success: function (response) {

					if (response) {

						console.log( response );
						output_csv_toolbox_fields( response );
						$( '#give-csv-toolbox-standard-fields, #give-csv-toolbox-custom-fields-wrap, #give-csv-toolbox-submit-wrap' ).slideDown();

					} else {

						alert( 'An AJAX error occurred.' );

					}
				}
			});
		});

	}

	/**
	 * Outputs the custom field checkboxes.
	 *
	 * @param response
	 */
	function output_csv_toolbox_fields(response) {

		var standard_fields = (typeof response.standard_fields !== 'undefined') ? response.standard_fields : 'No Custom Fields Found.';

		// Loop through STANDARD fields & output
		$( standard_fields ).each(function (index, value) {

			$( '#give-csv-toolbox-standard-field-list' ).append( '<li><label for="give-csv-toolkit-standard-field-' + value + '""><input type="checkbox" name="give_csv_toolkit_export_field[' + value + ']" id="give-csv-toolkit-standard-field-' + value + '">' + value + '</label> </li>' );

		});

		var hidden_fields = (typeof response.hidden_fields !== 'undefined') ? response.hidden_fields : 'No Hidden Custom Fields Found.';

		// Loop through HIDDEN fields & output
		$( hidden_fields ).each(function (index, value) {

			$( '#give-csv-toolbox-hidden-field-list' ).append( '<li><label for="give-csv-toolkit-hidden-field-' + value + '""><input type="checkbox" name="give_csv_toolkit_export_field[' + value + ']" id="give-csv-toolkit-hidden-field-' + value + '">' + value + '</label> </li>' );

		});

	}

})(jQuery);
