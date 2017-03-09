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

	$donation_list = implode( '\',\'', give_get_payments( array( 'give_forms' => array( $form_id ), 'posts_per_page' => -1, 'fields' => 'ids' ) ) );

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

	// echo $form_id;
	// echo $wpdb->prepare( $query, array( $post_type,  ) );
	// die();
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
