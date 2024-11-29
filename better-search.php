<?php
/**
 * Better Search is a plugin that will replace the default WordPress search page
 * with highly relevant search results improving your visitors search experience.
 *
 * @package   Better_Search
 * @author    Ajay D'Souza <me@ajaydsouza.com>
 * @license   GPL-2.0+
 * @link      https://webberzone.com
 * @copyright 2009-2024 Ajay D'Souza
 *
 * @wordpress-plugin
 * Plugin Name: Better Search
 * Plugin URI:  https://webberzone.com/plugins/better-search/
 * Description: Replace the default WordPress search with a contextual search. Search results are sorted by relevancy ensuring a better visitor search experience.
 * Version:     4.0.1-beta1
 * Update URI: https://api.freemius.com
 * Author:      WebberZone
 * Author URI:  https://webberzone.com/
 * Text Domain: better-search
 * License:     GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 * GitHub Plugin URI: https://github.com/WebberZone/better-search/
 */

namespace WebberZone\Better_Search;

if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! defined( 'BETTER_SEARCH_VERSION' ) ) {
	/**
	 * Holds the version of Better Search.
	 *
	 * @since 2.9.3
	 */
	define( 'BETTER_SEARCH_VERSION', '4.0.0' );
}

if ( ! defined( 'BETTER_SEARCH_PLUGIN_DIR' ) ) {
	/**
	 * Holds the filesystem directory path (with trailing slash) for Better Search
	 *
	 * @since 2.2.0
	 */
	define( 'BETTER_SEARCH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'BETTER_SEARCH_PLUGIN_URL' ) ) {
	/**
	 * Holds the filesystem directory path (with trailing slash) for Better Search
	 *
	 * @since 2.2.0
	 */
	define( 'BETTER_SEARCH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'BETTER_SEARCH_PLUGIN_FILE' ) ) {
	/**
	 * Holds the filesystem directory path (with trailing slash) for Better Search
	 *
	 * @since 2.2.0
	 */
	define( 'BETTER_SEARCH_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'BETTER_SEARCH_DB_VERSION' ) ) {
	/**
	 * Holds the version of Better Search.
	 *
	 * @since 3.3.0
	 */
	define( 'BETTER_SEARCH_DB_VERSION', '2.0' );
}

// Load Freemius.
require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/load-freemius.php';

// Load the autoloader.
require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/autoloader.php';

if ( ! function_exists( __NAMESPACE__ . '\load_bsearch' ) ) {
	/**
	 * The main function responsible for returning the one true WebberZone Snippetz instance to functions everywhere.
	 *
	 * @since 3.3.0
	 */
	function load_bsearch() {
		Main::get_instance();
	}
	add_action( 'plugins_loaded', __NAMESPACE__ . '\load_bsearch' );
}

/*
 *----------------------------------------------------------------------------
 * Include files
 *----------------------------------------------------------------------------
 */
require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/options-api.php';
require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/class-better-search-core-query.php';
require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/class-better-search-query.php';
require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/functions.php';
require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/general-template.php';
require_once BETTER_SEARCH_PLUGIN_DIR . 'includes/heatmap.php';


/**
 * Declare $bsearch_settings global so that it can be accessed in every function
 *
 * @since 1.3
 */
global $bsearch_settings;
$bsearch_settings = bsearch_get_settings();
