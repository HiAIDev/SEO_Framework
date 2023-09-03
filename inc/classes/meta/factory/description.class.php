<?php
/**
 * @package The_SEO_Framework\Classes\Front\Meta\Factory
 * @subpackage The_SEO_Framework\Meta\Description
 */

namespace The_SEO_Framework\Meta\Factory;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\Helper\Query,
	\The_SEO_Framework\Meta\Factory;

use function \The_SEO_Framework\{
	memo,
	Utils\normalize_generation_args,
	Utils\clamp_sentence
};

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as published
 * by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Holds getters for meta tag output.
 *
 * @since 4.3.0
 * @access protected
 * @internal
 */
class Description {

	/**
	 * Returns the meta description from custom fields. Falls back to autogenerated description.
	 *
	 * @since 4.3.0
	 *
	 * @param array|null $args   The query arguments. Accepts 'id', 'taxonomy', and 'pta'.
	 *                           Leave null to autodetermine query.
	 * @param bool       $escape Whether to escape the description.
	 * @return string The real description output.
	 */
	public static function get_description( $args = null, $escape = true ) {

		$desc = static::get_custom_description( $args, false )
			 ?: static::get_generated_description( $args, false );

		return $escape ? \tsf()->escape_description( $desc ) : $desc;
	}

	/**
	 * Returns the custom user-inputted description.
	 *
	 * @since 4.3.0
	 *
	 * @param array|null $args   The query arguments. Accepts 'id', 'taxonomy', and 'pta'.
	 *                           Leave null to autodetermine query.
	 * @param bool       $escape Whether to escape the description.
	 * @return string The custom field description.
	 */
	public static function get_custom_description( $args = null, $escape = true ) {

		if ( null === $args ) {
			$desc = static::get_custom_description_from_query();
		} else {
			normalize_generation_args( $args );
			$desc = static::get_custom_description_from_args( $args );
		}

		/**
		 * @since 2.9.0
		 * @since 3.0.6 1. Duplicated from $this->generate_description() (deprecated)
		 *              2. Removed all arguments but the 'id' argument.
		 * @since 4.2.0 1. No longer gets supplied custom query arguments when in the loop.
		 *              2. Now supports the `$args['pta']` index.
		 * @param string     $desc The custom-field description.
		 * @param array|null $args The query arguments. Contains 'id', 'taxonomy', and 'pta'.
		 *                         Is null when the query is auto-determined.
		 */
		$desc = (string) \apply_filters_ref_array(
			'the_seo_framework_custom_field_description',
			[
				$desc,
				$args,
			]
		);

		return $escape ? \tsf()->escape_description( $desc ) : $desc;
	}

	/**
	 * Gets a custom description, based on expected or current query, without escaping.
	 *
	 * @since 4.3.0
	 * @internal
	 * @see static::get_custom_description()
	 *
	 * @return string The custom description.
	 */
	public static function get_custom_description_from_query() {

		if ( Query::is_real_front_page() ) {
			if ( Query::is_static_frontpage() ) {
				$desc = \tsf()->get_option( 'homepage_description' )
					 ?: \tsf()->get_post_meta_item( '_genesis_description' );
			} else {
				$desc = \tsf()->get_option( 'homepage_description' );
			}
		} elseif ( Query::is_singular() ) {
			$desc = \tsf()->get_post_meta_item( '_genesis_description' );
		} elseif ( Query::is_editable_term() ) {
			$desc = \tsf()->get_term_meta_item( 'description' );
		} elseif ( \is_post_type_archive() ) {
			/**
			 * @since 4.0.6
			 * @since 4.2.0 Deprecated.
			 * @deprecated Use options instead.
			 * @param string $desc The post type archive description.
			 */
			$desc = (string) \apply_filters_deprecated(
				'the_seo_framework_pta_description',
				[ \tsf()->get_post_type_archive_meta_item( 'description' ) ?: '' ],
				'4.2.0 of The SEO Framework'
			);
		}

		return $desc ?? '' ?: '';
	}

	/**
	 * Gets a custom description, based on input arguments query, without escaping.
	 *
	 * @since 3.1.0
	 * @since 3.2.2 Now tests for the static frontpage metadata prior getting fallback data.
	 * @since 4.2.0 Now supports the `$args['pta']` index.
	 * @since 4.3.0 Now expects an ID before getting a post meta item.
	 * @internal
	 * @see static::get_custom_description()
	 *
	 * @param array $args Array of 'id' and 'taxonomy' values.
	 * @return string The custom description.
	 */
	public static function get_custom_description_from_args( $args ) {

		if ( $args['taxonomy'] ) {
			$desc = \tsf()->get_term_meta_item( 'description', $args['id'] );
		} elseif ( $args['pta'] ) {
			$desc = \tsf()->get_post_type_archive_meta_item( 'description', $args['pta'] );
		} elseif ( Query::is_real_front_page_by_id( $args['id'] ) ) {
			if ( $args['id'] ) {
				$desc = \tsf()->get_option( 'homepage_description' )
					 ?: \tsf()->get_post_meta_item( '_genesis_description', $args['id'] );
			} else {
				$desc = \tsf()->get_option( 'homepage_description' );
			}
		} elseif ( $args['id'] ) {
			$desc = \tsf()->get_post_meta_item( '_genesis_description', $args['id'] );
		}

		return $desc ?? '' ?: '';
	}

	/**
	 * Returns the autogenerated meta description.
	 *
	 * @since 3.0.6
	 * @since 3.1.0 1. The first argument now accepts an array, with "id" and "taxonomy" fields.
	 *              2. No longer caches.
	 *              3. Now listens to option.
	 *              4. Added type argument.
	 * @since 3.1.2 1. Now omits additions when the description will be deemed too short.
	 *              2. Now no longer converts additions into excerpt when no excerpt is found.
	 * @since 3.2.2 Now converts HTML characters prior trimming.
	 * @since 4.2.0 Now supports the `$args['pta']` index.
	 * @uses $this->generate_description()
	 * @TODO Should we enforce a minimum description length, where this result is ignored? e.g., use the input
	 *       guidelines' 'lower' value as a minimum, so that TSF won't ever generate "bad" descriptions?
	 *       This isn't truly helpful, since then search engines can truly fetch whatever with zero guidance.
	 *
	 * @param array|null $args   The query arguments. Accepts 'id', 'taxonomy', and 'pta'.
	 *                           Leave null to autodetermine query.
	 * @param bool       $escape Whether to escape the description.
	 * @param string     $type   Type of description. Accepts 'search', 'opengraph', 'twitter'.
	 * @return string The generated description output.
	 */
	public static function get_generated_description( $args = null, $escape = true, $type = 'search' ) {

		if ( ! static::may_generate( $args ) ) return '';

		switch ( $type ) {
			case 'opengraph':
			case 'twitter':
			case 'search':
				break;
			default:
				$type = 'search';
		}

		if ( null === $args ) {
			$excerpt = static::get_excerpt_from_query();
		} else {
			normalize_generation_args( $args );
			$excerpt = static::get_excerpt_from_args( $args );
		}

		/**
		 * @since 2.9.0
		 * @since 3.1.0 No longer passes 3rd and 4th parameter.
		 * @since 4.0.0 1. Deprecated second parameter.
		 *              2. Added third parameter: $args.
		 * @since 4.2.0 Now supports the `$args['pta']` index.
		 * @param string     $excerpt The excerpt to use.
		 * @param int        $page_id Deprecated.
		 * @param array|null $args The query arguments. Contains 'id', 'taxonomy', and 'pta'.
		 *                         Is null when the query is auto-determined.
		 */
		$excerpt = (string) \apply_filters_ref_array(
			'the_seo_framework_fetched_description_excerpt',
			[
				$excerpt,
				0,
				$args,
			]
		);

		// This page has a generated description that's far too short: https://theseoframework.com/em-changelog/1-0-0-amplified-seo/.
		// A direct directory-'site:' query will accept the description outputted--anything else will ignore it...
		// We should not work around that, because it won't direct in the slightest what to display.
		$excerpt = clamp_sentence(
			$excerpt,
			0,
			\tsf()->get_input_guidelines()['description'][ $type ]['chars']['goodUpper']
		);

		/**
		 * @since 2.9.0
		 * @since 3.1.0 No longer passes 3rd and 4th parameter.
		 * @since 4.2.0 Now supports the `$args['pta']` index.
		 * @param string     $desc The generated description.
		 * @param array|null $args The query arguments. Contains 'id', 'taxonomy', and 'pta'.
		 *                         Is null when the query is auto-determined.
		 */
		$desc = (string) \apply_filters_ref_array(
			'the_seo_framework_generated_description',
			[
				$excerpt,
				$args,
			]
		);

		return $escape ? \tsf()->escape_description( $desc ) : $desc;
	}

	/**
	 * Returns a description excerpt for the current query.
	 *
	 * @since 3.1.0
	 * @since 4.2.0 Flipped order of query tests.
	 *
	 * @return string
	 */
	public static function get_excerpt_from_query() {

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = memo() ) return $memo;

		if ( Query::is_real_front_page() ) {
			$excerpt = static::get_singular_excerpt();
		} elseif ( Query::is_home_as_page() ) {
			$excerpt = static::get_blog_page_excerpt();
		} elseif ( Query::is_singular() ) {
			$excerpt = static::get_singular_excerpt();
		} elseif ( Query::is_archive() ) {
			$excerpt = static::get_archival_excerpt();
		}

		return memo( $excerpt ?? '' ?: '' );
	}

	/**
	 * Returns a description excerpt for the current query.
	 *
	 * @since 3.1.0
	 * @since 3.2.2 Fixed front-page as blog logic.
	 * @since 4.2.0 Now supports the `$args['pta']` index.
	 *
	 * @param array $args The query arguments. Accepts 'id', 'taxonomy', and 'pta'.
	 * @return string
	 */
	public static function get_excerpt_from_args( $args ) {

		if ( $args['taxonomy'] ) {
			$excerpt = static::get_archival_excerpt( \get_term( $args['id'], $args['taxonomy'] ) );
		} elseif ( $args['pta'] ) {
			$excerpt = static::get_archival_excerpt( \get_post_type_object( $args['pta'] ) );
		} else {
			if ( Query::is_home_as_page( $args['id'] ) ) {
				$excerpt = static::get_blog_page_excerpt();
			} else {
				$excerpt = static::get_singular_excerpt( $args['id'] );
			}
		}

		return $excerpt ?? '' ?: '';
	}

	/**
	 * Returns a description excerpt for the blog page.
	 *
	 * @since 3.1.0
	 *
	 * @return string
	 */
	public static function get_blog_page_excerpt() {
		return sprintf(
			/* translators: %s = Blog page title. Front-end output. */
			\__( 'Latest posts: %s', 'autodescription' ),
			\tsf()->get_filtered_raw_generated_title( [ 'id' => (int) \get_option( 'page_for_posts' ) ] )
		);
	}

	/**
	 * Returns a description excerpt for archives.
	 *
	 * @since 3.1.0
	 * @since 4.0.0 Now processes HTML tags via s_excerpt_raw() for the author descriptions.
	 * @since 4.2.0 Now uses post type archive descriptions to prefill meta descriptions.
	 *
	 * @param null|\WP_Term|\WP_Post_Type $object The term or post type object.
	 * @return string
	 */
	public static function get_archival_excerpt( $object = null ) {

		if ( $object && \is_wp_error( $object ) )
			return '';

		if ( \is_null( $object ) ) {
			$in_the_loop = true;
			$object      = \get_queried_object();
		} else {
			$in_the_loop = false;
		}

		/**
		 * @since 3.1.0
		 * @see `\tsf()->s_excerpt_raw()` to strip HTML tags neatly.
		 * @param string                 $excerpt The short circuit excerpt.
		 * @param \WP_Term|\WP_Post_Type $object  The Term object or post type object.
		 */
		$excerpt = (string) \apply_filters_ref_array(
			'the_seo_framework_generated_archive_excerpt',
			[
				'',
				$object,
			]
		);

		if ( $excerpt ) return $excerpt;

		if ( $in_the_loop ) {
			if ( Query::is_category() || Query::is_tag() || Query::is_tax() ) {
				// WordPress DOES NOT allow HTML in term descriptions, not even if you're a super-administrator.
				// See https://wpvulndb.com/vulnerabilities/9445. We won't parse HTMl tags unless WordPress adds native support.
				$excerpt = \tsf()->s_description_raw( $object->description ?? '' );
			} elseif ( Query::is_author() ) {
				$excerpt = \tsf()->s_excerpt_raw( \get_the_author_meta( 'description', (int) \get_query_var( 'author' ) ) );
			} elseif ( \is_post_type_archive() ) {
				/**
				 * @since 4.0.6
				 * @since 4.2.0 Now provides the post type object description, if assigned.
				 * @param string $excerpt The archive description excerpt.
				 * @param \WP_Term|\WP_Post_Type $object The post type object.
				 */
				$excerpt = (string) \apply_filters_ref_array(
					'the_seo_framework_pta_description_excerpt',
					[
						\tsf()->s_description_raw( $object->description ?? '' ),
						$object,
					]
				);
			} else {
				/**
				 * @since 4.0.6
				 * @since 4.1.0 Added the $object object parameter.
				 * @param string $excerpt The fallback archive description excerpt.
				 * @param \WP_Term $object    The Term object.
				 */
				$excerpt = (string) \apply_filters_ref_array(
					'the_seo_framework_fallback_archive_description_excerpt',
					[
						'',
						$object,
					]
				);
			}
		} else {
			$excerpt = \tsf()->s_description_raw( $object->description ?? '' );
		}

		return $excerpt;
	}

	/**
	 * Returns a description excerpt for singular post types.
	 *
	 * @since 3.1.0
	 * NOTE: Don't add memo; large memory heaps can occur.
	 *       It only runs twice on the post edit screen (post.php).
	 *       Front-end caller get_excerpt_from_query() uses memo.
	 *
	 * @param ?int $id The singular ID. Leave null to get main query.
	 * @return string
	 */
	public static function get_singular_excerpt( $id = null ) {

		$post = \get_post( $id ?? Query::get_the_real_id() );

		// If the post is protected, don't generate a description.
		if ( \tsf()->is_protected( $post ) ) return '';

		if ( ! empty( $post->post_excerpt ) && \post_type_supports( $post->post_type, 'excerpt' ) ) {
			$excerpt = $post->post_excerpt;
		} elseif ( ! empty( $post->post_content ) && ! \tsf()->uses_non_html_page_builder( $post->ID ) ) {
			// We should actually get the parsed content here... but that can be heavy on the server.
			// We could cache that parsed content, but that'd be asinine for a plugin. WordPress should've done that.
			$excerpt = \tsf()->get_post_content( $post );

			if ( $excerpt )
				$excerpt = \tsf()->strip_paragraph_urls( \tsf()->strip_newline_urls( $excerpt ) );
		}

		return empty( $excerpt ) ? '' : \tsf()->s_excerpt_raw( $excerpt );
	}

	/**
	 * Determines whether automated descriptions are enabled.
	 *
	 * @since 4.3.0
	 * @see Query::get_the_real_id()
	 * @see Query::get_current_taxonomy()
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'taxonomy', and 'pta'.
	 *                         Leave null to autodetermine query.
	 * @return bool
	 */
	public static function may_generate( $args = null ) {

		isset( $args ) and normalize_generation_args( $args );

		/**
		 * @since 2.5.0
		 * @since 3.0.0 Now passes $args as the second parameter.
		 * @since 3.1.0 Now listens to option.
		 * @since 4.2.0 Now supports the `$args['pta']` index.
		 * @param bool       $autodescription Enable or disable the automated descriptions.
		 * @param array|null $args            The query arguments. Contains 'id', 'taxonomy', and 'pta'.
		 *                                    Is null when the query is auto-determined.
		 */
		return (bool) \apply_filters_ref_array(
			'the_seo_framework_enable_auto_description',
			[
				\tsf()->get_option( 'auto_description' ),
				$args,
			]
		);
	}
}
