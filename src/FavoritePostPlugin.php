<?php

namespace FavoritePostPlugin;

use FavoritePostPlugin\Controllers\FavoritePostsController;

class FavoritePostPlugin
{
    private $controllers = [
        FavoritePostsController::class,
    ];

    private static $instance = null;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (is_null(static::$instance))
            static::$instance = new FavoritePostPlugin();
        return static::$instance;
    }

    public static function tableName()
    {
        global $wpdb;
        return $wpdb->prefix . 'favorite_user_posts';
    }

    public function activate()
    {
        global $wpdb;
        $table_name = static::tableName();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE `$table_name` (
          `id` INT NOT NULL AUTO_INCREMENT,
          `post_id` BIGINT(20) NOT NULL,
          `user_id` BIGINT(20) NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE INDEX `unique_favorite_user_id_post_id` (`post_id`, `user_id`)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    public function deactivate()
    {
        static::uninstall();
    }

    public static function uninstall()
    {
        global $wpdb;
        $table_name = static::tableName();
        $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
    }

    public function register()
    {
        foreach ($this->controllers as $className) {
            $controller = new $className;
            $controller->registerRoutes();
        }
    }
}
