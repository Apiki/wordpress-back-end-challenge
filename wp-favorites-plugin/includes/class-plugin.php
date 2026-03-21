<?php

class WPFAV_Plugin {

    public function run() {
        $this->load_dependencies();

        add_action('plugins_loaded', ['WPFAV_Database', 'maybe_update']);

        add_action('rest_api_init', ['WPFAV_API', 'register_routes']);
    }

    private function load_dependencies() {
        require_once WPFAV_PLUGIN_PATH . 'includes/class-api.php';
        require_once WPFAV_PLUGIN_PATH . 'includes/class-favorites.php';
    }
}