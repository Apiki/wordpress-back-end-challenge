<?php
/**
 * Plugin Name: Apiki Favorites
 * Description: Um plugin para permitir que usuários logados favoritem posts.
 * Version: 1.0.0
 * Author: MateusBreno
 * License: GPL2
 */

declare(strict_types=1);

if (!defined("ABSPATH")) {
    exit;
}

define("APIKI_FAVORITES_PATH", plugin_dir_path(__FILE__));

require_once APIKI_FAVORITES_PATH . "includes/class-apiki-favorites-db.php";
require_once APIKI_FAVORITES_PATH . "includes/class-apiki-favorites-rest-controller.php";

class Apiki_Favorites
{
    public function __construct()
    {
        register_activation_hook(__FILE__, [$this, "activate"]);
        register_deactivation_hook(__FILE__, [$this, "deactivate"]);

        add_action("plugins_loaded", [$this, "init_plugin"]);
    }

    public function activate(): void
    {
        Apiki_Favorites_DB::create_favorites_table();
    }

    public function deactivate(): void
    {
        Apiki_Favorites_DB::drop_favorites_table();
    }

    public function init_plugin(): void
    {
        new Apiki_Favorites_REST_Controller();
    }
}

new Apiki_Favorites();
