<?php
/**
 * Plugin Name: GlotPress Stats
 * Description: Shortcode for displaying a polyglot-oriented digest of a locale
 * Version: 0.4
 * Author: Nilo Velez
 * Author URI: https://www.nilovelez.com
 * Text Domain: glotpress-stats
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html

 * @package    WordPress
 * @subpackage glotstats
 */

namespace glotstats;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'init',
	function () {
		/**
		 * Outputs the stats table of the given locale and project
		 * [glotstats locale="es" directory="plugins" view="tasks"]
		 * locale: locale code as used in the >>URLs<<< os translate.wordpress.org
		 * directory: choose themes or plugins
		 * view:
		 *  - top: shows the unstranslatede projects from the top 200 of the selected directory
		 *  - stats: shows ready to copy Slack code with the info of the next 3 projects to do
		 */

		// Load plugin text domain
		load_plugin_textdomain( 'glotpress-stats', FALSE,	dirname(plugin_basename(__FILE__)) . '/languages' );

		// Register Styles and Scripts
		wp_register_style( 'datatable', plugin_dir_url(__FILE__) . 'vendor/DataTables/datatables.min.css', '', '1.10.21' );
		wp_register_script( 'jquery-full', plugin_dir_url(__FILE__) . 'assets/js/jquery-3.5.1.min.js', '', '3.5.1', false );
		wp_register_script( 'datatables', plugin_dir_url( __FILE__ ) . 'vendor/DataTables/datatables.min.js', '', '1.10.21', false );
		wp_register_script( 'glotpress-stats', plugin_dir_url(__FILE__) . 'assets/js/glotpress-stats.js', '', '0.3', false );
		
		function shortcode_callback( $atts ) {			
			// Enqueue Styles and Scripts
			wp_enqueue_style('datatable');
			wp_enqueue_script('jquery-full');
			wp_enqueue_script('datatables');
			wp_enqueue_script('glotpress-stats');

			$a = shortcode_atts(
				array(
					'locale'    => 'es',
					'directory' => 'plugins',
					'view'      => 'top',
				),
				$atts
			);
			if ( ! in_array( $a['directory'], array( 'themes', 'plugins' ), true ) ) {
				return false;
			}
			if ( ! in_array( $a['view'], array( 'top', 'tasks' ), true ) ) {
				return false;
			}
			ob_start();
			require_once plugin_dir_path( __FILE__ ) . './parser.php';
			parse( $a['locale'], $a['directory'], $a['view'] );
			return ob_get_clean();
		}
		add_shortcode( 'glotstats', 'glotstats\shortcode_callback' );
	}
);


