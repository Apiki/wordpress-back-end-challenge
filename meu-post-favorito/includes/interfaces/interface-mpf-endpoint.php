<?php
/**
 * MPF plugin.
 *
 * @package MPF\Admin\Endpoints
 */

/**
 * Dita os métodos necessários para uma implementação de Endpoint.
 */
interface MPF_Endpoint_Interface  {

	/**
	 * Registra as rotas para os endpoints.
	 *
	 * @return void
	 */
	public function register();
}
