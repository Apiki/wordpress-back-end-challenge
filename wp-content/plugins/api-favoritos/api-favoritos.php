<?php
/*
Plugin Name: API-Favoritos
Plugin URI: https://localhost
Description: favoritar posts para usuários logados usando a WP REST API.
Deve instalar o plugin git clone https://github.com/WP-API/Basic-Auth.git para autenticação básica do usuário
Version: 1.0
Author: Alexandre S. Anjos
Author URI: https://github.com/AlexandreSA/wordpress-back-end-challenge/tree/alexandre-anjos
License: GPLv2 or later
Text Domain: API-Favoritos
*/

/*
*  sudo git clone https://github.com/WP-API/Basic-Auth.git
*
* Inserir um post via curl usando o auth basic do WP_REST_API
* curl -X POST -I --user  admin:admin http://localhost/wordpress-back-end-challenge/wp-json/pluginname/v2/posts/add/5
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

require_once dirname(__FILE__).'/includes/apiController.php';


