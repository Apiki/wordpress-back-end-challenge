<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for API Meu post favorito.
 *
 * @package    MPF
 * @subpackage MPF/includes
 * @link       #
 * @since      0.1.0
 * @author     Bruno Lima
 *
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @package    MPF
 * @subpackage MPF/includes
 * @since      0.1.0
 * @author     Bruno Lima
 */
class mpf_i18n {

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    0.1.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'api-mpf',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}

}
