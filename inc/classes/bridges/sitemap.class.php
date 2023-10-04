<?php
/**
 * @package The_SEO_Framework\Classes\Bridges\Sitemap
 * @subpackage The_SEO_Framework\Sitemap
 */

namespace The_SEO_Framework\Bridges;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * The SEO Framework plugin
 * Copyright (C) 2019 - 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

\tsf()->_deprecated_function( 'The_SEO_Framework\Bridges\Sitemap', '4.3.0', 'The_SEO_Framework\Sitemap\Registry' );
/**
 * Prepares sitemap output.
 *
 * @since 4.0.0
 * @since 4.3.0 1. Moved to \The_SEO_Framework\Sitemap\Registry
 *              2. Deprecated.
 * @deprecated
 * @ignore
 * @access protected
 * @final Can't be extended.
 */
class_alias( 'The_SEO_Framework\Sitemap\Registry', 'The_SEO_Framework\Bridges\Sitemap', true );
