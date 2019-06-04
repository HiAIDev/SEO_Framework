<?php
/**
 * @package The_SEO_Framework\Classes\Builders
 * @subpackage The_SEO_Framework\Builders
 */

namespace The_SEO_Framework\Builders;

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
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

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * Generates the SEO Bar.
 *
 * @since 3.3.0
 * Mind the late static binding. We use "self" if the variable is shared between instances.
 * We use "static" if the variable isn't shared between instances.
 * @link <https://www.php.net/manual/en/language.oop5.late-static-bindings.php>
 *
 * @access private
 * @internal
 * @abstract: Implements test_{$*}, see $tests for *.
 * @see \The_SEO_Framework\Interpreters\SeoBar
 *      Use \The_SEO_Framework\Interpreters\SeoBar::generate_bar() instead.
 */
abstract class SeoBar {
	use \The_SEO_Framework\Traits\Enclose_Core_Final;

	/**
	 * @since 3.3.0
	 * @access private
	 *    Only made public as _run_all_tests() is not available.
	 * Shared between instances.
	 * @var array All known tests.
	 */
	public static $tests = [ 'title', 'description', 'indexing', 'following', 'archiving', 'redirect' ];

	/**
	 * @since 3.3.0
	 * Shared between instances.
	 * @var null|\The_SEO_Framework\Load
	 */
	protected static $tsf = null;

	/**
	 * @since 3.3.0
	 * Shared between instances.
	 * @var array $cache A non-volatile caching status. Holds post type settings,
	 *                   among other things, to be used in generation.
	 */
	private static $cache = [];

	/**
	 * @since 3.3.0
	 * Not shared between instances.
	 * @var array $query The current query for the SEO Bar.
	 */
	protected static $query;

	/**
	 * @since 3.3.0
	 * Not shared between instances
	 * @var \The_SEO_Framework\Builders\SeoBar_* $instance The instance.
	 */
	protected static $instance;

	/**
	 * Constructor.
	 *
	 * Sets late static binding.
	 *
	 * @since 3.3.0
	 */
	final protected function __construct() {
		static::$instance = &$this;
		self::$tsf        = self::$tsf ?: \the_seo_framework();
	}

	/**
	 * Returns this instance.
	 *
	 * @since 3.3.0
	 *
	 * @return static
	 */
	final public static function &get_instance() {
		static::$instance instanceof static or new static;
		return static::$instance;
	}

	/**
	 * Sets non-volatile cache by key value.
	 * This cache will stick around for multiple SEO Bar generations.
	 *
	 * @since 3.3.0
	 *
	 * @param string $key   The cache key.
	 * @param mixed  $value The cache value.
	 * @return mixed The cache value.
	 */
	final protected static function set_cache( $key, $value ) {
		return self::$cache[ $key ] = $value;
	}

	/**
	 * Retrieves non-volatile cache value by key.
	 * This cache will stick around for multiple SEO Bar generations.
	 *
	 * @since 3.3.0
	 *
	 * @param string $key The cache key.
	 * @return mixed|null The cache value. Null on failure
	 */
	final protected static function get_cache( $key ) {
		return isset( self::$cache[ $key ] ) ? self::$cache[ $key ] : null;
	}

	/**
	 * Runs all SEO bar tests.
	 *
	 * @since 3.3.0
	 * @access private
	 *         This method will be removed, see todo:
	 * @ignore
	 * @generator
	 * @TODO only available from PHP 7+
	 *
	 * @param array $args : {
	 *   int    $id        : Required. The current post or term ID.
	 *   string $taxonomy  : Optional. If not set, this will interpret it as a post.
	 *   string $post_type : Optional. If not set, this will be automatically filled.
	 *                                 This parameter is ignored for taxonomies.
	 * }
	 * @yield array : {
	 *    string $test => array The testing results.
	 * }
	 */
	// public static function _run_all_tests( array $args ) {
	// 	yield from static::run_test( static::$tests, $args );
	// }

	/**
	 * Runs one or more SEO bar tests.
	 *
	 * @since 3.3.0
	 * @access private
	 * @generator
	 *
	 * @param array|string $tests The test(s) to perform.
	 * @param array        $query  : {
	 *   int    $id        : Required. The current post or term ID.
	 *   string $taxonomy  : Optional. If not set, this will interpret it as a post.
	 *   string $post_type : Optional. If not set, this will be automatically filled.
	 *                                 This parameter is ignored for taxonomies.
	 * }
	 * @yield array : {
	 *    string $test => array $item The SEO Bar compatible results.
	 * }
	 */
	final public function _run_test( $tests, array $query ) {

		$tests = array_intersect( self::$tests, (array) $tests );

		static::$query = $query;

		if ( in_array( 'redirect', $tests, true ) && $this->has_blocking_redirect() )
			$tests = [ 'redirect' ];

		foreach ( $tests as $test )
			yield $test => $this->{"test_$test"}();
	}

	/**
	 * Tests for blocking redirection.
	 *
	 * @since 3.3.0
	 * @abstract
	 *
	 * @return bool True if there's a blocking redirect, false otherwise.
	 */
	abstract protected function has_blocking_redirect();

	/**
	 * Runs title tests.
	 *
	 * @since 3.3.0
	 * @abstract
	 *
	 * @return array $item : {
	 *    string $symbol : Required. The displayed symbol that identifies your bar.
	 *    string $title  : Required. The title of the assessment.
	 *    string $status : Required. Accepts 'good', 'okay', 'bad', 'unknown'.
	 *    string $reason : Required. The final assessment: The reason for the $status.
	 *    string $assess : Required. The assessments on why the reason is set. Keep it short and concise!
	 *                               Does not accept HTML for performant ARIA support.
	 * }
	 */
	abstract protected function test_title();

	/**
	 * Runs title tests.
	 *
	 * @since 3.3.0
	 * @see test_title() for return value.
	 * @abstract
	 *
	 * @return array $item
	 */
	abstract protected function test_description();

	/**
	 * Runs description tests.
	 *
	 * @since 3.3.0
	 * @see test_title() for return value.
	 * @abstract
	 *
	 * @return array $item
	 */
	abstract protected function test_indexing();

	/**
	 * Runs following tests.
	 *
	 * @since 3.3.0
	 * @see test_title() for return value.
	 * @abstract
	 *
	 * @return array $item
	 */
	abstract protected function test_following();

	/**
	 * Runs archiving tests.
	 *
	 * @since 3.3.0
	 * @see test_title() for return value.
	 * @abstract
	 *
	 * @return array $item
	 */
	abstract protected function test_archiving();

	/**
	 * Runs redirect tests.
	 *
	 * @since 3.3.0
	 * @see test_title() for return value.
	 * @abstract
	 *
	 * @return array $item
	 */
	abstract protected function test_redirect();
}
