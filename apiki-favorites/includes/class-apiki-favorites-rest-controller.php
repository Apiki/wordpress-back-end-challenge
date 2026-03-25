<?php

declare(strict_types=1);

if (!defined("ABSPATH")) {
    exit;
}

class Apiki_Favorites_REST_Controller
{
    private string $namespace = "apiki/v1";
    private string $base = "favorites";

    public function __construct()
    {
        add_action("rest_api_init", [$this, "register_routes"]);
    }

    public function register_routes(): void
    {
        register_rest_route(
            $this->namespace,
            "/" . $this->base . "/(?P<post_id>\d+)",
            [
                [
                    "methods" => WP_REST_Server::CREATABLE,
                    "callback" => [$this, "add_favorite"],
                    "permission_callback" => [$this, "permission_check"],
                    "args" => [
                        "post_id" => [
                            "validate_callback" => function ($param, $request, $key) {
                                return is_numeric($param);
                            },
                        ],
                    ],
                ],
                [
                    "methods" => WP_REST_Server::DELETABLE,
                    "callback" => [$this, "remove_favorite"],
                    "permission_callback" => [$this, "permission_check"],
                    "args" => [
                        "post_id" => [
                            "validate_callback" => function ($param, $request, $key) {
                                return is_numeric($param);
                            },
                        ],
                    ],
                ],
            ]
        );
    }

    public function permission_check(WP_REST_Request $request): bool
    {
        return is_user_logged_in();
    }

    public function add_favorite(WP_REST_Request $request): WP_REST_Response
    {
        global $wpdb;
        $user_id = get_current_user_id();
        $post_id = (int) $request["post_id"];

        if (!get_post($post_id)) {
            return new WP_REST_Response(
                ["message" => "Post not found."],
                404
            );
        }

        $table_name = $wpdb->prefix . "apiki_favorites";

        $data = [
            "user_id" => $user_id,
            "post_id" => $post_id,
        ];

        $format = ["%d", "%d"];

        $inserted = $wpdb->insert($table_name, $data, $format);

        if ($inserted === false) {
            return new WP_REST_Response(
                ["message" => "Error adding favorite or already favorited."],
                400
            );
        }

        return new WP_REST_Response(
            ["message" => "Post favorited successfully."],
            200
        );
    }

    public function remove_favorite(WP_REST_Request $request): WP_REST_Response
    {
        global $wpdb;
        $user_id = get_current_user_id();
        $post_id = (int) $request["post_id"];

        $table_name = $wpdb->prefix . "apiki_favorites";

        $deleted = $wpdb->delete(
            $table_name,
            [
                "user_id" => $user_id,
                "post_id" => $post_id,
            ],
            ["%d", "%d"]
        );

        if ($deleted === false) {
            return new WP_REST_Response(
                ["message" => "Error removing favorite."],
                400
            );
        }

        if ($deleted === 0) {
            return new WP_REST_Response(
                ["message" => "Favorite not found to remove."],
                404
            );
        }

        return new WP_REST_Response(
            ["message" => "Post unfavorited successfully."],
            200
        );
    }
}
