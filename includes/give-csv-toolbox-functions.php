<?php
/**
 * CSV Toolbox Functions
 */


/**
 * AJAX
 *
 * @see http://wordpress.stackexchange.com/questions/58834/echo-all-meta-keys-of-a-custom-post-type
 *
 * @return string
 */
function give_csv_toolbox_get_custom_fields() {

	global $wpdb;
	$post_type = 'give_payment';

	$form_id = isset( $_POST['form_id'] ) ? intval( $_POST['form_id'] ) : '';

	if ( empty( $form_id ) ) {
		return false;
	}

	$args          = array(
		'give_forms'     => array( $form_id ),
		'posts_per_page' => - 1,
		'fields'         => 'ids'
	);
	$donation_list = implode( '\',\'', give_get_payments( $args ) );

	$query = "
        SELECT DISTINCT($wpdb->postmeta.meta_key) 
        FROM $wpdb->posts 
        LEFT JOIN $wpdb->postmeta 
        ON $wpdb->posts.ID = $wpdb->postmeta.post_id
        WHERE $wpdb->posts.post_type = '%s'
        AND $wpdb->posts.ID IN (%s)
        AND $wpdb->postmeta.meta_key != '' 
        AND $wpdb->postmeta.meta_key NOT RegExp '(^[_0-9].+$)'
    ";

	$meta_keys = $wpdb->get_col( $wpdb->prepare( $query, $post_type, $donation_list ) );

	$query = "
        SELECT DISTINCT($wpdb->postmeta.meta_key) 
        FROM $wpdb->posts 
        LEFT JOIN $wpdb->postmeta 
        ON $wpdb->posts.ID = $wpdb->postmeta.post_id 
        WHERE $wpdb->posts.post_type = '%s' 
        AND $wpdb->posts.ID IN (%s)
        AND $wpdb->postmeta.meta_key != '' 
        AND $wpdb->postmeta.meta_key NOT RegExp '^[^_]'
    ";

	$hidden_meta_keys = $wpdb->get_col( $wpdb->prepare( $query, $post_type, $donation_list ) );

	wp_send_json( array( 'standard_fields' => $meta_keys, 'hidden_fields' => $hidden_meta_keys ) );

	give_die();

}

add_action( 'wp_ajax_give_csv_toolbox_get_custom_fields', 'give_csv_toolbox_get_custom_fields' );


/**
 * Register the payments batch exporter
 *
 * @since  1.0
 */
function give_register_csv_toolbox_batch_export() {
	add_action( 'give_batch_export_class_include', 'give_csv_toolbox_include_export_class', 10, 1 );
}

add_action( 'give_register_batch_exporter', 'give_register_csv_toolbox_batch_export', 10 );


/**
 * Includes the CSV Toolbox Custom Exporter Class.
 *
 * @param $class Give_CSV_Toolbox_Donations_Export
 */
function give_csv_toolbox_include_export_class( $class ) {
	if ( 'Give_CSV_Toolbox_Donations_Export' === $class ) {
		require_once GIVE_CSV_TOOLBOX_DIR . 'includes/give-csv-toolbox-exporter.php';
	}
}