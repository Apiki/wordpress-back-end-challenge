<?php
/**
 * Main class for the WordPress Back-end Challenge plugin.
 *
 * @package WP_Backend_Challenge
 * @author  Luis Paiva <contato@luispaiva.com.br>
 *
 * @version 1.0.0
 */

namespace App;

/**
 * Class Init
 */
class Init {

	/**
	 * Init class.
	 *
	 * @return void
	 */
	public static function init(): void {
		new Setup\Config();
		new Hooks\Hooks();
		new Routes\Routes();
	}
}
