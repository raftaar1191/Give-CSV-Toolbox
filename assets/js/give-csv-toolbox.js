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

		$('select[name="forms"]').chosen().change(function () {

			var toggle_fields = $('#give-csv-toolbox-standard-fields, #give-csv-toolbox-custom-fields-wrap, #give-csv-toolbox-submit-wrap, #give-csv-toolbox-export-options');

			// Hide when change is made for a form.
			toggle_fields.hide();

			var give_form_id,
				variable_prices_html_container = $('.give-donation-level');

			// Check for form ID.
			if (!( give_form_id = $(this).val() )) {
				return false;
			}

			// Clear out fields.
			$('.give-csv-toolbox-field-list').empty();

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

						console.log(response);
						output_csv_toolbox_fields(response);
						checkbox_select_subfields();
						toggle_fields.slideDown();

					} else {
						alert('An AJAX error occurred.');
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

		/**
		 * FFM Fields
		 */
		var ffm_fields = (typeof response.ffm_fields !== 'undefined') ? response.ffm_fields : '';
		var ffm_field_list = $('#give-csv-toolbox-ffm-field-list');

		if (ffm_fields) {

			// Loop through FFM fields & output
			$(ffm_fields).each(function (index, value) {

				// Repeater sections.
				var repeater_sections = (typeof value.repeaters !== 'undefined') ? value.repeaters : '';

				if (repeater_sections) {

					var parent_title = '';
					// Repeater section field.
					$(repeater_sections).each(function (index, value) {

						if (parent_title !== value.parent_title) {
							ffm_field_list.append('<li class="repeater-section-title" data-parent-meta="' + value.parent_meta + '"><label for="give-csv-toolkit-ffm-field-' + value.parent_meta + '"><input type="checkbox" name="give_csv_toolbox_export_parent[' + value.parent_meta + ']" id="give-csv-toolkit-ffm-field-' + value.parent_meta + '">' + value.parent_title + '</label></li>');
						}
						parent_title = value.parent_title;


						ffm_field_list.append('<li class="repeater-section repeater-section-' + value.parent_meta + '"><label for="give-csv-toolkit-ffm-field-' + value.subkey + '"><input type="checkbox" name="give_csv_toolbox_export_option[' + value.subkey + ']" id="give-csv-toolkit-ffm-field-' + value.subkey + '">' + value.label + '</label></li>');

					});

				}

				// Repeater sections.
				var single_repeaters = (typeof value.single !== 'undefined') ? value.single : '';
				if (single_repeaters) {

					// Repeater section field.
					$(single_repeaters).each(function (index, value) {

						ffm_field_list.append('<li><label for="give-csv-toolkit-ffm-field-' + value.subkey + '"><input type="checkbox" name="give_csv_toolbox_export_option[' + value.metakey + ']" id="give-csv-toolkit-ffm-field-' + value.subkey + '">' + value.label + '</label> </li>');

					});

				}
			});

		} else {
			ffm_field_list.append('<li class="give-csv-toolbox-no-fields"><span class="dashicons dashicons-info"></span>No fields found.</li>');
		}

		/**
		 * Standard Fields
		 */
		var standard_fields = (typeof response.standard_fields !== 'undefined') ? response.standard_fields : '';
		var standard_field_list = $('#give-csv-toolbox-standard-field-list');
		if (standard_fields.length > 0) {

			// Loop through STANDARD fields & output
			$(standard_fields).each(function (index, value) {

				standard_field_list.append('<li><label for="give-csv-toolkit-standard-field-' + value + '"><input type="checkbox" name="give_csv_toolbox_export_option[' + value + ']" id="give-csv-toolkit-standard-field-' + value + '">' + value + '</label> </li>');

			});

		} else {
			standard_field_list.append('<li class="give-csv-toolbox-no-fields"><span class="dashicons dashicons-info"></span>No fields found.</li>');
		}

		/**
		 * Hidden Fields
		 */
		var hidden_fields = (typeof response.hidden_fields !== 'undefined') ? response.hidden_fields : '';
		var hidden_field_list = $('#give-csv-toolbox-hidden-field-list');
		if (hidden_fields.length > 0) {

			// Loop through HIDDEN fields & output
			$(hidden_fields).each(function (index, value) {
				hidden_field_list.append('<li><label for="give-csv-toolkit-hidden-field-' + value + '"><input type="checkbox" name="give_csv_toolbox_export_option[' + value + ']" id="give-csv-toolkit-hidden-field-' + value + '">' + value + '</label> </li>');
			});

		} else {
			hidden_field_list.append('<li class="give-csv-toolbox-no-fields"><span class="dashicons dashicons-info"></span>No fields found.</li>');
		}
	}

	/**
	 * Toggle subfield checkboxes.
	 *
	 * This is necessary to ensure proper exporting of all custom field repeater data.
	 */
	function checkbox_select_subfields() {

		$('.repeater-section-title input').on('click', function () {

			var field_selector = $(this).parents('.repeater-section-title').data('parent-meta');
			var checkboxes = $('.repeater-section-' + field_selector).find('input');

			checkboxes.prop('checked', !checkboxes.prop('checked'));

		});

	}

})(jQuery);
