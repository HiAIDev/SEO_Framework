<?php
/**
 * @package The_SEO_Framework\Classes
 */
namespace The_SEO_Framework;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2018 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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
 * Class The_SEO_Framework\Generate_Description
 *
 * Generates Description SEO data based on content.
 *
 * @since 2.8.0
 */
class Generate_Description extends Generate {

	/**
	 * Constructor, loads parent constructor.
	 */
	protected function __construct() {
		parent::__construct();
	}

	/**
	 * Returns the meta description from custom fields. Falls back to autogenerated description.
	 *
	 * @since 3.0.6
	 * @since 3.1.0 The first argument now accepts an array, with "id" and "taxonomy" fields.
	 * @uses $this->get_description_from_custom_field()
	 * @uses $this->get_generated_description()
	 *
	 * @param array|null $args   An array of 'id' and 'taxonomy' values.
	 *                           Accepts int values for backward compatibility.
	 * @param bool       $escape Whether to escape the description.
	 * @return string The real description output.
	 */
	public function get_description( $args = null, $escape = true ) {

		$desc = $this->get_description_from_custom_field( $args, false )
			 ?: $this->get_generated_description( $args, false );

		return $escape ? $this->escape_description( $desc ) : $desc;
	}

	/**
	 * Returns the Open Graph meta description. Falls back to meta description.
	 *
	 * @since 3.0.4
	 * @since 3.1.0 : 1. Now tries to get the homepage social descriptions.
	 *                2. The first argument now accepts an array, with "id" and "taxonomy" fields.
	 * @uses $this->get_open_graph_description_from_custom_field()
	 * @uses $this->get_generated_open_graph_description()
	 *
	 * @param array|null $args   An array of 'id' and 'taxonomy' values.
	 *                           Accepts int values for backward compatibility.
	 * @param bool       $escape Whether to escape the description.
	 * @return string The real Open Graph description output.
	 */
	public function get_open_graph_description( $args = null, $escape = true ) {

		$desc = $this->get_open_graph_description_from_custom_field( $args, false )
			 ?: $this->get_generated_open_graph_description( $args, false );

		return $escape ? $this->escape_description( $desc ) : $desc;
	}

	/**
	 * Returns the Open Graph meta description from custom field.
	 * Falls back to meta description.
	 *
	 * @since 3.1.0
	 * @see $this->get_open_graph_description()
	 *
	 * @param array|null $args   The query arguments. Accepts 'id' and 'taxonomy'.
	 *                           Leave null to autodetermine query.
	 * @param bool       $escape Whether to escape the title.
	 * @return string TwOpen Graphitter description.
	 */
	protected function get_open_graph_description_from_custom_field( $args, $escape ) {

		if ( null === $args ) {
			$desc = $this->get_custom_open_graph_description_from_query();
		} else {
			$this->fix_generation_args( $args );
			$desc = $this->get_custom_open_graph_description_from_args( $args );
		}

		return $escape ? $this->escape_description( $desc ) : $desc;
	}

	/**
	 * Returns the Open Graph meta description from custom field, based on query.
	 * Falls back to Open Graph description.
	 *
	 * @since 3.1.0
	 * @see $this->get_open_graph_description()
	 * @see $this->get_open_graph_description_from_custom_field()
	 *
	 * @return string Open Graph description.
	 */
	protected function get_custom_open_graph_description_from_query() {

		$desc = '';

		if ( $this->is_real_front_page() ) {
			$desc = $this->get_option( 'homepage_og_description' ) ?: '';
		}
		if ( ! $desc ) {
			if ( $this->is_singular() ) {
				$desc = $this->get_custom_field( '_open_graph_description' ) ?: '';
			}
		}

		return $desc;
	}

	/**
	 * Returns the Open Graph meta description from custom field, based on arguments.
	 * Falls back to Open Graph description.
	 *
	 * @since 3.1.0
	 * @see $this->get_open_graph_description()
	 * @see $this->get_open_graph_description_from_custom_field()
	 *
	 * @param array|null $args The query arguments. Accepts 'id' and 'taxonomy'.
	 * @return string Open Graph description.
	 */
	protected function get_custom_open_graph_description_from_args( array $args ) {

		$desc = '';

		if ( $args['taxonomy'] ) {
			$desc = '';
		} else {
			if ( $this->is_front_page_by_id( $args['id'] ) ) {
				$desc = $this->get_option( 'homepage_og_description' ) ?: '';
			}
			if ( ! $desc ) {
				$desc = $this->get_custom_field( '_open_graph_description', $args['id'] )  ?: '';
			}
		}

		return $desc;
	}

	/**
	 * Returns the Twitter meta description.
	 * Falls back to Open Graph description.
	 *
	 * @since 3.0.4
	 * @since 3.1.0 : 1. Now tries to get the homepage social descriptions.
	 *                2. The first argument now accepts an array, with "id" and "taxonomy" fields.
	 * @uses $this->get_twitter_description_from_custom_field()
	 * @uses $this->get_generated_twitter_description()
	 *
	 * @param array|null $args   An array of 'id' and 'taxonomy' values.
	 *                           Accepts int values for backward compatibility.
	 * @param bool       $escape Whether to escape the description.
	 * @return string The real Twitter description output.
	 */
	public function get_twitter_description( $args = null, $escape = true ) {

		$desc = $this->get_twitter_description_from_custom_field( $args, false )
			 ?: $this->get_generated_twitter_description( $args, false );

		return $escape ? $this->escape_description( $desc ) : $desc;
	}

	/**
	 * Returns the Twitter meta description from custom field.
	 * Falls back to Open Graph description.
	 *
	 * @since 3.1.0
	 * @see $this->get_twitter_description()
	 *
	 * @param array|null $args   The query arguments. Accepts 'id' and 'taxonomy'.
	 *                           Leave null to autodetermine query.
	 * @param bool       $escape Whether to escape the title.
	 * @return string Twitter description.
	 */
	protected function get_twitter_description_from_custom_field( $args, $escape ) {

		if ( null === $args ) {
			$desc = $this->get_custom_twitter_description_from_query();
		} else {
			$this->fix_generation_args( $args );
			$desc = $this->get_custom_twitter_description_from_args( $args );
		}

		return $escape ? $this->escape_description( $desc ) : $desc;
	}

	/**
	 * Returns the Twitter meta description from custom field, based on query.
	 * Falls back to Open Graph description.
	 *
	 * @since 3.1.0
	 * @see $this->get_twitter_description()
	 * @see $this->get_twitter_description_from_custom_field()
	 *
	 * @return string Twitter description.
	 */
	protected function get_custom_twitter_description_from_query() {

		$desc = '';

		if ( $this->is_real_front_page() ) {
			$desc = $this->get_option( 'homepage_twitter_description' ) ?: $this->get_option( 'homepage_og_description' ) ?: '';
		}
		if ( ! $desc ) {
			if ( $this->is_singular() ) {
				$desc = $this->get_custom_field( '_twitter_description' )
					  ?: $this->get_custom_field( '_open_graph_description' )
					  ?: '';
			}
		}

		return $desc;
	}

	/**
	 * Returns the Twitter meta description from custom field, based on arguments.
	 * Falls back to Open Graph description.
	 *
	 * @since 3.1.0
	 * @see $this->get_twitter_description()
	 * @see $this->get_twitter_description_from_custom_field()
	 *
	 * @param array|null $args The query arguments. Accepts 'id' and 'taxonomy'.
	 * @return string Twitter description.
	 */
	protected function get_custom_twitter_description_from_args( array $args ) {

		$desc = '';

		if ( $args['taxonomy'] ) {
			$desc = '';
		} else {
			if ( $this->is_front_page_by_id( $args['id'] ) ) {
				$desc = $this->get_option( 'homepage_twitter_description' ) ?: $this->get_option( 'homepage_og_description' ) ?: '';
			}
			if ( ! $desc ) {
				$desc = $this->get_custom_field( '_twitter_description', $args['id'] )
					 ?: $this->get_custom_field( '_open_graph_description', $args['id'] )
					 ?: '';
			}
		}

		return $desc;
	}

	/**
	 * Returns the custom user-inputted description.
	 *
	 * @since 3.0.6
	 * @since 3.1.0 The first argument now accepts an array, with "id" and "taxonomy" fields.
	 *
	 * @param array|null $args   An array of 'id' and 'taxonomy' values.
	 *                           Accepts int values for backward compatibility.
	 * @param bool       $escape Whether to escape the description.
	 * @return string The custom field description.
	 */
	public function get_description_from_custom_field( $args = null, $escape = true ) {

		if ( null === $args ) {
			$desc = $this->get_custom_description_from_query();

			// Generated as backward compat for the filter...
			$args = [
				'id'       => $this->get_the_real_ID(),
				'taxonomy' => $this->get_current_taxonomy(),
			];
		} else {
			$this->fix_generation_args( $args );
			$desc = $this->get_custom_description_from_args( $args );
		}

		/**
		 * Filters the description from custom field, if any.
		 * @since 2.9.0
		 * @since 3.0.6 1. Duplicated from $this->generate_description() (to be deprecated)
		 *              2. Removed all arguments but the 'id' argument.
		 * @param string $desc The description.
		 * @param array  $args The description arguments.
		 */
		$desc = (string) \apply_filters( 'the_seo_framework_custom_field_description', $desc, $args );

		return $escape ? $this->escape_description( $desc ) : $desc;
	}

	/**
	 * Gets a custom description, based on expected or current query, without escaping.
	 *
	 * @since 3.1.0
	 * @internal
	 * @see $this->get_description_from_custom_field()
	 *
	 * @return string The custom description.
	 */
	protected function get_custom_description_from_query() {

		$desc = '';

		if ( $this->is_real_front_page() ) {
			$desc = $this->get_option( 'homepage_description' ) ?: '';
		}
		if ( ! $desc ) {
			if ( $this->is_singular() ) {
				$desc = $this->get_custom_field( '_genesis_description' ) ?: '';
			} elseif ( $this->is_term_meta_capable() ) {
				$data = $this->get_term_meta( $this->get_the_real_ID() );
				$desc = ! empty( $data['description'] ) ? $data['description'] : '';
			}
		}

		return $desc;
	}

	/**
	 * Gets a custom description, based on input arguments query, without escaping.
	 *
	 * @since 3.1.0
	 * @internal
	 * @see $this->get_description_from_custom_field()
	 *
	 * @param array $args Array of 'id' and 'taxonomy' values.
	 * @return string The custom description.
	 */
	protected function get_custom_description_from_args( array $args ) {

		$desc = '';

		if ( $args['taxonomy'] ) {
			// $term = \get_term( $args['id'], $args['taxonomy'] ); // redundant
			$data  = $this->get_term_meta( $args['id'] );
			$desc = ! empty( $data['description'] ) ? $data['description'] : '';
		} else {
			if ( $this->is_front_page_by_id( $args['id'] ) ) {
				$desc = $this->get_option( 'homepage_description' ) ?: '';
			}
			$desc = $desc ?: $this->get_custom_field( '_genesis_description' ) ?: '';
		}

		return $desc;
	}

	/**
	 * Returns the autogenerated meta description.
	 *
	 * @since 3.0.6
	 * @since 3.1.0 1. The first argument now accepts an array, with "id" and "taxonomy" fields.
	 *              2. No longer caches.
	 *              3. Now listens to option.
	 *              4. Added type argument.
	 * @uses $this->generate_description()
	 * @staticvar array $cache
	 *
	 * @param array|null $args   An array of 'id' and 'taxonomy' values.
	 *                           Accepts int values for backward compatibility.
	 * @param bool       $escape Whether to escape the description.
	 * @param string     $type   Type of description. Accepts 'search', 'opengraph', 'twitter'.
	 * @return string The generated description output.
	 */
	public function get_generated_description( $args = null, $escape = true, $type = 'search' ) {

		if ( ! $this->is_auto_description_enabled( $args ) ) return '';

		if ( null === $args ) {
			$excerpt = $this->get_description_excerpt_from_query();
			$_filter_id = $this->get_the_real_ID();
			$additions_superseded = $this->are_description_additions_superseded();
		} else {
			$this->fix_generation_args( $args );
			$_filter_id = $args['id'];
			$excerpt = $this->get_description_excerpt_from_args( $args );
			$additions_superseded = $this->are_description_additions_superseded( $args );
		}

		if ( $additions_superseded ) {
			$additions        = '';
			$additions_length = 0;
		} else {
			$additions = $this->get_description_additions( $args );

			if ( ! $excerpt ) {
				$excerpt          = $additions;
				$additions        = '';
				$additions_length = 0;
			} else {
				$additions_length = $additions ? mb_strlen( html_entity_decode( $additions ) ) : 0;
				if ( $additions_length > 71 ) {
					$additions        = '';
					$additions_length = 0;
				}
			}
		}

		/**
		 * @since 2.9.0
		 * @since 3.1.0 No longer passes 3rd and 4th parameter.
		 * @param string $excerpt The excerpt to use.
		 * @param int    $page_id The current page/term ID
		 */
		$excerpt = (string) \apply_filters( 'the_seo_framework_fetched_description_excerpt', $excerpt, $_filter_id );

		// The JS scripts don't fully comply with this, yet; because we can't lock the "if parent is empty" value.
		// Normalize to prevent confusion.
		// if ( ! in_array( $type, [ 'opengraph', 'twitter', 'search' ], true ) )
			$type = 'search';

		$excerpt = $this->trim_excerpt(
			$excerpt,
			0,
			$this->get_input_guidelines()['description'][ $type ]['chars']['goodUpper'] - $additions_length
		);

		if ( $additions ) {
			$desc = sprintf(
				/* translators: 1: Description additions, 2: Description separator, 3: Excerpt */
				\__( '%1$s %2$s %3$s', 'autodescription' ),
				$additions,
				$this->get_description_separator(),
				$excerpt
			);
		} else {
			$desc = $excerpt;
		}

		/**
		 * Filters the generated description, if any.
		 * @since 2.9.0
		 * @since 3.1.0 No longer passes 3rd and 4th parameter.
		 * @param string     $description The description.
		 * @param array|null $args The description arguments.
		 */
		$desc = (string) \apply_filters( 'the_seo_framework_generated_description', $desc, $args );

		return $escape ? $this->escape_description( $desc ) : $desc;
	}

	/**
	 * Returns the autogenerated Twitter meta description. Falls back to meta description.
	 *
	 * @since 3.0.4
	 * @uses $this->get_generated_open_graph_description()
	 *
	 * @param array|null $args   An array of 'id' and 'taxonomy' values.
	 *                           Accepts int values for backward compatibility.
	 * @param bool       $escape Whether to escape the description.
	 * @return string The generated Twitter description output.
	 */
	public function get_generated_twitter_description( $args = null, $escape = true ) {
		return $this->get_generated_description( $args, $escape, 'twitter' );
	}

	/**
	 * Returns the autogenerated Open Graph meta description. Falls back to meta description.
	 *
	 * @since 3.0.4
	 * @uses $this->generate_description()
	 * @staticvar array $cache
	 *
	 * @param array|null $args   An array of 'id' and 'taxonomy' values.
	 *                           Accepts int values for backward compatibility.
	 * @param bool       $escape Whether to escape the description.
	 * @return string The generated Open Graph description output.
	 */
	public function get_generated_open_graph_description( $args = null, $escape = true ) {
		return $this->get_generated_description( $args, $escape, 'opengraph' );
	}

	/**
	 * Determines whether description additions are used instead of an excerpt,
	 * thus superseding the need.
	 *
	 * @since 3.1.0
	 *
	 * @param array|null $args An array of 'id' and 'taxonomy' values.
	 * @return bool
	 */
	protected function are_description_additions_superseded( $args = null ) {

		if ( $args ) {
			if ( empty( $args['taxonomy'] ) )
				return $this->is_blog_page( $args['id'] ) || $this->is_real_front_page( $args['id'] );
		} else {
			return $this->is_blog_page() || $this->is_real_front_page();
		}

		return false;
	}

	/**
	 * Returns a description excerpt for the current query.
	 *
	 * @since 3.1.0
	 *
	 * @return string
	 */
	protected function get_description_excerpt_from_query() {

		static $excerpt;

		if ( isset( $excerpt ) )
			return $excerpt;

		$excerpt = '';

		if ( $this->is_blog_page() ) {
			$excerpt = $this->get_blog_page_description_excerpt();
		} elseif ( $this->is_real_front_page() ) {
			$excerpt = $this->get_front_page_description_excerpt();
		} elseif ( $this->is_archive() ) {
			$excerpt = $this->get_archival_description_excerpt();
		} elseif ( $this->is_singular() ) {
			$excerpt = $this->get_singular_description_excerpt();
		}

		return $excerpt;
	}

	/**
	 * Returns a description excerpt for the current query.
	 *
	 * @since 3.1.0
	 *
	 * @param array|null $args An array of 'id' and 'taxonomy' values.
	 * @return string
	 */
	protected function get_description_excerpt_from_args( array $args ) {

		$excerpt = '';

		if ( $args['taxonomy'] ) {
			$excerpt = $this->get_archival_description_excerpt( \get_term( $args['id'], $args['taxonomy'] ) );
		} else {
			if ( $this->is_blog_page( $args['id'] ) ) {
				$excerpt = $this->get_blog_page_description_excerpt();
			} elseif ( $this->is_front_page_by_id( $args['id'] ) ) {
				$excerpt = $this->get_front_page_description_excerpt();
			} else {
				$excerpt = $this->get_singular_description_excerpt( $args['id'] );
			}
		}

		return $excerpt;
	}

	/**
	 * Returns a description excerpt for the blog page.
	 *
	 * @since 3.1.0
	 *
	 * @return string
	 */
	protected function get_blog_page_description_excerpt() {
		return $this->get_description_additions( [ 'id' => (int) \get_option( 'page_for_posts' ) ], true );
	}

	/**
	 * Returns a description excerpt for the front page.
	 *
	 * @since 3.1.0
	 *
	 * @return string
	 */
	protected function get_front_page_description_excerpt() {

		$id = $this->get_the_front_page_ID();

		if ( $this->is_static_frontpage( $id ) ) {
			$excerpt = $this->get_singular_description_excerpt( $id );
		}
		$excerpt = $excerpt ?: $this->get_description_additions( [ 'id' => $id ], true );

		return $excerpt;
	}

	/**
	 * Returns a description excerpt for archives.
	 *
	 * @since 3.1.0
	 *
	 * @param null|\WP_Term $term
	 * @return string
	 */
	protected function get_archival_description_excerpt( $term = null ) {

		if ( $term && \is_wp_error( $term ) )
			return '';

		if ( is_null( $term ) ) {
			$_query = true;
			$term   = \get_queried_object();
		} else {
			$_query = false;
		}

		/**
		 * @since 3.1.0
		 *
		 * @param string   $excerpt The short circuit excerpt.
		 * @param \WP_Term $term    The Term object.
		 */
		$excerpt = (string) \apply_filters( 'the_seo_framework_generated_archive_excerpt', '', $term );

		if ( $excerpt ) return $excerpt;

		$excerpt = '';

		if ( ! $_query ) {
			$excerpt = ! empty( $term->description ) ? $this->s_description_raw( $term->description ) : '';
		} else {
			if ( $this->is_category() || $this->is_tag() || $this->is_tax() ) {
				$excerpt = ! empty( $term->description ) ? $this->s_description_raw( $term->description ) : '';
			} elseif ( $this->is_author() ) {
				$excerpt = $this->s_description_raw( \get_the_author_meta( 'description', (int) \get_query_var( 'author' ) ) );
			} elseif ( \is_post_type_archive() ) {
				// TODO
				$excerpt = '';
			} else {
				$excerpt = '';
			}
		}

		return $excerpt;
	}

	/**
	 * Returns a description excerpt for singular post types.
	 *
	 * @since 3.1.0
	 *
	 * @param int $id The singular ID.
	 * @return string
	 */
	protected function get_singular_description_excerpt( $id = null ) {

		if ( is_null( $id ) )
			$id = $this->get_the_real_ID();

		//* If the post is protected, don't generate a description.
		if ( $this->is_protected( $id ) ) return '';

		return $this->get_excerpt_by_id( '', $id, null, false );
	}

	/**
	 * Returns additions for "Title on Blogname".
	 *
	 * @since 3.1.0
	 * @see $this->get_generated_description()
	 *
	 * @param array|null $args   An array of 'id' and 'taxonomy' values.
	 *                           Accepts int values for backward compatibility.
	 * @param bool       $forced Whether to force the additions, bypassing options and filters.
	 * @return string The description additions.
	 */
	protected function get_description_additions( $args, $forced = false ) {

		$term = null;
		if ( is_null( $args ) ) {
			$term = $this->is_archive() ? \get_queried_object() : null;
		} elseif ( ! empty( $args['taxonomy'] ) ) {
			$term = \get_term( $args['id'], $args['taxonomy'] );
		}

		$additions = [
			'title'    => '',
			'on'       => '',
			'blogname' => '',
		];

		if ( $forced || $this->add_description_additions( $args['id'], $term ) ) {
			if ( ! empty( $args['taxonomy'] ) ) {
				$title = $this->generate_title_from_args( $args );
			} else {
				if ( $this->is_blog_page( $args['id'] ) ) {
					$title = $this->get_raw_generated_title( $args );
					/* translators: %s = Blog page title. Front-end output. */
					$title = sprintf( \__( 'Latest posts: %s', 'autodescription' ), $title );
				} elseif ( $this->is_front_page_by_id( $args['id'] ) ) {
					$title = $this->get_home_page_tagline();
				} else {
					if ( is_null( $args ) ) {
						$title = $this->generate_title_from_query();
					} else {
						$title = $this->generate_title_from_args( $args );
					}
				}
			}

			if ( $forced || $this->get_option( 'description_blogname' ) ) {
				$additions = [
					'title'    => $title,
					'on'       => \_x( 'on', 'Placement. e.g. Post Title "on" Blog Name', 'autodescription' ),
					'blogname' => $this->get_blogname(),
				];
			} else {
				$additions = array_merge( $additions, [
					'title' => $title,
				] );
			}
		}

		if ( empty( $additions['title'] ) )
			return '';

		$title    = $additions['title'];
		$on       = $additions['on'];
		$blogname = $additions['blogname'];

		/* translators: 1: Title, 2: on, 3: Blogname */
		return trim( sprintf( \__( '%1$s %2$s %3$s', 'autodescription' ), $title, $on, $blogname ) );
	}

	/**
	 * Trims the excerpt by word and determines sentence stops.
	 *
	 * @since 2.6.0
	 * @since 3.1.0 : 1. Now uses smarter trimming.
	 *                2. Deprecated 2nd parameter.
	 *                3. Now has unicode support for sentence closing.
	 *                4. Now strips last three words when preceded by a sentence closing separator.
	 *                5. Now always leads with (inviting) dots, even if the excerpt is shorter than $max_char_length.
	 * @see https://secure.php.net/manual/en/regexp.reference.unicode.php
	 *
	 * @param string $excerpt         The untrimmed excerpt.
	 * @param int    $depr            The current excerpt length. No longer needed.
	 * @param int    $max_char_length At what point to shave off the excerpt.
	 * @return string The trimmed excerpt.
	 */
	public function trim_excerpt( $excerpt, $depr = 0, $max_char_length = 0 ) {

		//* Find all words with $max_char_length, and trim when the last word boundary or punctuation is found.
		preg_match( sprintf( '/.{0,%d}(\p{Po}|\p{Z}|$){1}/su', $max_char_length ), trim( $excerpt ), $matches );
		$excerpt = isset( $matches[0] ) ? ( $matches[0] ?: '' ) : '';

		//* Remove trailing/leading commas and spaces.
		$excerpt = trim( $excerpt, ' ,' );

		//* Test if there's punctuation with something trailing. The next regex will be a wild-goose chase otherwise.
		preg_match( '/\p{Po}\p{Z}?\w/su', $excerpt, $matches );
		if ( $matches ) {
			//* Find words that are leading after a dot. If there are 3 or fewer, trim them.
			//= super fast when there's punctuation with something trailing:
			preg_match( '/(.+)((\p{Po})\p{Z}?(\w+\p{Z}?){1,3})(.+)?/su', $excerpt, $matches );
			// If $matches[5] is set, then there are more than 3 words...
			if ( isset( $matches[1], $matches[3] ) && empty( $matches[5] ) ) {
				$excerpt = $matches[1] . $matches[3];
			}
		}

		//* Remove leading commas again.
		$excerpt = rtrim( $excerpt, ' ,' );

		if ( ';' === substr( $excerpt, -1 ) ) {
			//* Replace connector punctuation with a dot.
			$excerpt = rtrim( $excerpt, ' \\/,.?!;' );
			if ( $excerpt )
				$excerpt .= '.';
		} elseif ( $excerpt ) {
			//* Finds sentence-closing punctiations.
			preg_match( '/\p{Po}$/u', $excerpt, $matches );
			if ( empty( $matches ) ) // no punctuation found
				$excerpt .= '...';
		}

		return trim( $excerpt );
	}

	/**
	 * Determines whether automated descriptions are enabled.
	 *
	 * @since 3.1.0
	 * @access private
	 * @see $this->get_the_real_ID()
	 * @see $this->get_current_taxonomy()
	 *
	 * @param array|null $args An array of 'id' and 'taxonomy' values.
	 *                         Can be null when query is autodetermined.
	 * @return bool
	 */
	public function is_auto_description_enabled( $args ) {

		if ( is_null( $args ) ) {
			$args = [
				'id'       => $this->get_the_real_ID(),
				'taxonomy' => $this->get_current_taxonomy(),
			];
		}

		/**
		 * @since 2.5.0
		 * @since 3.0.0 Now passes $args as the second parameter.
		 * @since 3.1.0 Now listens to option.
		 * @param bool  $autodescription Enable or disable the automated descriptions.
		 * @param array $args            The description arguments.
		 */
		return (bool) \apply_filters_ref_array(
			'the_seo_framework_enable_auto_description',
			[
				$this->get_option( 'auto_description' ),
				$args,
			]
		);
	}

	/**
	 * Determines whether to add description additions. (╯°□°）╯︵ ┻━┻
	 *
	 * @since 2.6.0
	 * @since 2.7.0 Removed cache.
	 *              Whether an excerpt is available is no longer part of this check.

	 * @param int             $id The current page or post ID.
	 * @param \WP_Term|string $term The current Term.
	 * @return bool Whether to add description additions.
	 */
	public function add_description_additions( $id = '', $term = '' ) {

		/**
		 * @since 2.6.0
		 * @param array $filter : {
		 *    @param bool     $filter Set to true to add prefix.
		 *    @param int      $id     The Term object ID or The Page ID.
		 *    @param \WP_term $term   The Term object.
		 * }
		 */
		$filter = \apply_filters( 'the_seo_framework_add_description_additions', true, $id, $term );
		$option = $this->get_option( 'description_additions' );

		return $option && $filter;
	}

	/**
	 * Returns Description Separator.
	 *
	 * @since 2.3.9
	 * @staticvar string $sep
	 *
	 * @return string The Separator, unescaped.
	 */
	public function get_description_separator() {
		static $sep;
		/**
		 * @since 2.3.9
		 * @param string $sep The description separator.
		 */
		return isset( $sep )
			? $sep
			: $sep = (string) \apply_filters(
				'the_seo_framework_description_separator',
				$this->get_separator( 'description' )
			);
	}
}
