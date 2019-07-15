<?php
/**
 * @package The_SEO_Framework\Compat\Plugin\WooCommerce
 */

namespace The_SEO_Framework;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and $_this = \the_seo_framework_class() and $this instanceof $_this or die;

// @TODO Move everything WC related over to here.

\add_action( 'woocommerce_init', __NAMESPACE__ . '\\_init_wc_compat' );
/**
 * Initializes WooCommerce compatibility.
 *
 * @since 3.1.0
 * @uses \is_product()
 */
function _init_wc_compat() {
	\add_action(
		'the_seo_framework_do_before_output',
		function() {
			/**
			 * Removes TSF breadcrumbs.
			 */
			if ( function_exists( '\\is_product' ) && \is_product() ) {
				\add_filter( 'the_seo_framework_json_breadcrumb_output', '__return_false' );
			}
		}
	);
}

\add_filter( 'the_seo_framework_image_generation_params', __NAMESPACE__ . '\\_adjust_image_generation_params', 10, 2 );
/**
 * Adjusts image generation parameters.
 *
 * @since 3.3.0
 *
 * @param array      $params : [
 *    string  size:     The image size to use.
 *    boolean multi:    Whether to allow multiple images to be returned.
 *    array   cbs:      The callbacks to parse. Ideally be generators, so we can halt remotely.
 *    array   fallback: The callbacks to parse. Ideally be generators, so we can halt remotely.
 * ];
 * @param array|null $args The query arguments. Contians 'id' and 'taxonomy'.
 *                         Is null when query is autodetermined.
 * @return array $params
 */
function _adjust_image_generation_params( $params, $args ) {

	$is_product = false;

	if ( null === $args ) {
		$is_product = \the_seo_framework()->is_wc_product();
	} else {
		if ( ! $args['taxonomy'] ) {
			$is_product = \the_seo_framework()->is_wc_product( $args['id'] );
		}
	}

	if ( $is_product ) {
		$params['cbs']['wc_gallery'] = __NAMESPACE__ . '\\_get_product_gallery_image_details';
	}

	return $params;
}

/**
 * Generates image URLs and IDs from the WooCommerce product gallary entries.
 *
 * @since 3.3.0
 * @generator
 *
 * @param array|null $args The query arguments. Accepts 'id' and 'taxonomy'.
 *                         Leave null to autodetermine query.
 * @param string     $size The size of the image to get.
 * @yield array : {
 *    string url: The image URL location,
 *    int    id:  The image ID, if any,
 * }
 */
function _get_product_gallery_image_details( $args = null, $size = 'full' ) {

	$post_id = 0;

	if ( null === $args ) {
		if ( \the_seo_framework()->is_wc_product() ) {
			$post_id = \the_seo_framework()->get_the_real_ID();
		}
	} else {
		if ( ! $args['taxonomy'] ) {
			if ( \the_seo_framework()->is_wc_product( $args['id'] ) ) {
				$post_id = $args['id'];
			}
		}
	}

	$attachment_ids = [];

	if ( $post_id && \metadata_exists( 'post', $post_id, '_product_image_gallery' ) ) {
		$product_image_gallery = \get_post_meta( $post_id, '_product_image_gallery', true );

		$attachment_ids = array_map( 'absint', array_filter( explode( ',', $product_image_gallery ) ) );
	}

	if ( ! $attachment_ids ) {
		yield [
			'url' => '',
			'id'  => 0,
		];
	} else {
		foreach ( $attachment_ids as $id ) {
			yield [
				'url' => \wp_get_attachment_image_url( $id, $size ),
				'id'  => $id,
			];
		}
	}
}
