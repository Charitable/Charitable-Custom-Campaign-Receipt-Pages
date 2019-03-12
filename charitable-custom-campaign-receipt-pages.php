<?php
/**
 * Plugin Name:       Charitable - Custom Campaign Receipt Pages
 * Plugin URI:        https://github.com/Charitable/Charitable-Custom-Campaign-Receipt-Pages
 * Description:       A dumping ground for testing custom overrides.
 * Version:           1.0.0
 * Author:            WP Charitable
 * Author URI:        https://wpcharitable.com/
 * Requires at least: 4.5
 * Tested up to:      5.0.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add a "Donation Receipt page" setting to the Campaign settings.
 *
 * @return void
 */
add_action( 'init', function() {

	/**
	 * Create a new field as an instance of `Charitable_Campaign_Field`.
	 *
	 * See all available arguments at:
	 *
	 * @https://github.com/Charitable/Charitable/blob/ef9a468fbdd6fa83307abe6ac0c38896f625cf45/includes/fields/class-charitable-campaign-field.php
	*/
	$campaign_field = new Charitable_Campaign_Field( 'donation_receipt_page', array(
		'label'      => 'Donation Receipt Page',
		'data_type'  => 'meta',
		'admin_form' => array(
			'type'     => 'select',
			'required' => false,
			'options'  => array(
				'default' => 'Use the default donation receipt page',
				'pages'   => array(
					'options' => charitable_get_pages_options(),
					'label'   => 'Pages',
				),
			),
			'default'  => 'default',
			'section'  => 'campaign-donation-options',
		),
	) );

	/**
	 * Now, we register our new field.
	 */
	charitable()->campaign_fields()->register_field( $campaign_field );

} );

/**
 * Filter the page to redirect the donor to after making their donation.
 *
 * @param  string $default The endpoint's URL.
 * @param  array  $args    Mixed set of arguments.
 * @return string
 */
add_filter( 'charitable_permalink_donation_receipt_page', function( $default, $args ) {

	/**
	 * Get the donation object.
	 */
	$donation_id = isset( $args['donation_id'] ) ? $args['donation_id'] : get_the_ID();
	$donation    = charitable_get_donation( $donation_id );

	/**
	 * Get the campaign that received the donation.
	 */
	$campaign_id = current( $donation->get_campaign_donations() )->campaign_id;
	$campaign    = charitable_get_campaign( $campaign_id );

	/**
	 * Get the campaign's donation receipt page.
	 */
	$receipt_page = $campaign->get( 'donation_receipt_page' );

	/**
	 * If we haven't set a donation receipt page or we chose
	 * the default option, return the default.
	 */
	if ( ! $receipt_page || 'default' == $receipt_page ) {
		return $default;
	}

	/**
	 * Get the permalink for the receipt page and then append
	 * the donation_id and donation_receipt arguments to it.
	 */
	$receipt_page_url = get_permalink( $receipt_page );
	$receipt_page_url = add_query_arg(
		array(
			'donation_id'      => $donation_id,
			'donation_receipt' => 1,
		),
		$receipt_page_url
	);

	/**
	 * Return the escaped URL.
	 */
	return esc_url_raw( $receipt_page_url );

}, 10, 2 );
