<?php

declare(strict_types=1);

namespace Apiki\Favorites\REST;

use Apiki\Favorites\Domain\Favorite;
use Apiki\Favorites\Domain\FavoriteException;
use Apiki\Favorites\Plugin;
use Apiki\Favorites\Repository\FavoritesRepository;
use WP_Error;
use WP_Post;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined('ABSPATH') || exit;

/**
 * REST controller for favorites.
 *
 * Extends WP_REST_Controller to inherit schema/sanitization/response
 * helpers — instead of calling register_rest_route() with raw arrays
 * everywhere.
 *
 * Two important conventions enforced here:
 *
 *   1. HTTP methods are referenced via WP_REST_Server constants
 *      (READABLE, CREATABLE, DELETABLE) — never as raw strings like 'GET'
 *      or 'POST'. The constants are bitmask-friendly, documented, and
 *      survive refactors in the WP core.
 *
 *   2. Authorization uses `current_user_can()` with a capability —
 *      not bare `is_user_logged_in()`. A login check alone says
 *      "is there a user?". A capability check says "is the user allowed
 *      to do THIS thing?". Even when the capability used here ('read')
 *      is granted to every logged-in role, going through the capability
 *      system means an admin can later restrict it via a role plugin
 *      without our code changing.
 */
final class FavoritesController extends WP_REST_Controller
{
    public function __construct(
        private readonly FavoritesRepository $repository
    ) {
        $this->namespace = Plugin::REST_NAMESPACE;
        $this->rest_base = 'favorites';
    }

    public function register_routes(): void
    {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [$this, 'get_items'],
                    'permission_callback' => [$this, 'permissions_check'],
                    'args'                => $this->get_collection_params(),
                ],
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [$this, 'create_item'],
                    'permission_callback' => [$this, 'permissions_check'],
                    'args'                => [
                        'post_id' => [
                            'description'       => __('Post ID to favorite.', 'apiki-favorites'),
                            'type'              => 'integer',
                            'required'          => true,
                            'minimum'           => 1,
                            'sanitize_callback' => 'absint',
                        ],
                    ],
                ],
                'schema' => [$this, 'get_public_item_schema'],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<post_id>\d+)',
            [
                'args' => [
                    'post_id' => [
                        'description'       => __('Post ID to unfavorite.', 'apiki-favorites'),
                        'type'              => 'integer',
                        'required'          => true,
                        'sanitize_callback' => 'absint',
                    ],
                ],
                [
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => [$this, 'delete_item'],
                    'permission_callback' => [$this, 'permissions_check'],
                ],
            ]
        );
    }

    /**
     * Authorization gate for every endpoint.
     *
     * Two checks, in order:
     *
     *   1. is_user_logged_in() — gives a clear 401 vs 403 distinction.
     *      Without a user, we return 401 Unauthorized (you need to log in).
     *
     *   2. current_user_can('read') — capability check. 'read' is the
     *      capability granted to the lowest WP role (Subscriber), so
     *      every logged-in user passes by default. The point is NOT to
     *      restrict access — the point is to route the authorization
     *      through the capability system so it can be customized later
     *      without touching this code.
     */
    public function permissions_check(WP_REST_Request $request): bool|WP_Error
    {
        if ( ! is_user_logged_in()) {
            return new WP_Error(
                'rest_forbidden_context',
                __('You must be logged in to manage favorites.', 'apiki-favorites'),
                ['status' => 401]
            );
        }

        if ( ! current_user_can('read')) {
            return new WP_Error(
                'rest_forbidden',
                __('You are not allowed to manage favorites.', 'apiki-favorites'),
                ['status' => 403]
            );
        }

        return true;
    }

    /**
     * GET /favorites — list current user's favorites, paginated.
     *
     * @param WP_REST_Request<array<string, mixed>> $request
     */
    public function get_items($request): WP_REST_Response
    {
        $user_id  = get_current_user_id();
        $page     = (int) $request->get_param('page');
        $per_page = (int) $request->get_param('per_page');

        $favorites = $this->repository->list_for_user($user_id, $page, $per_page);
        $total     = $this->repository->count_for_user($user_id);

        $items = [];
        foreach ($favorites as $favorite) {
            $item    = $this->prepare_item_for_response($favorite, $request);
            $items[] = $this->prepare_response_for_collection($item);
        }

        $response = rest_ensure_response($items);
        $response->header('X-WP-Total', (string) $total);
        $response->header(
            'X-WP-TotalPages',
            (string) ( $per_page > 0 ? (int) ceil($total / $per_page) : 0 )
        );

        return $response;
    }

    /**
     * POST /favorites — favorite a post for the current user.
     *
     * @param WP_REST_Request<array<string, mixed>> $request
     */
    public function create_item($request): WP_REST_Response|WP_Error
    {
        $user_id = get_current_user_id();
        $post_id = (int) $request->get_param('post_id');

        if ( ! $this->post_is_favoritable($post_id)) {
            return new WP_Error(
                'rest_post_invalid',
                __('Post does not exist or is not published.', 'apiki-favorites'),
                ['status' => 404]
            );
        }

        try {
            $favorite = $this->repository->add($user_id, $post_id);
        } catch (FavoriteException $e) {
            return new WP_Error(
                'rest_already_favorited',
                $e->getMessage(),
                ['status' => $e->getCode() > 0 ? $e->getCode() : 409]
            );
        }

        $response = $this->prepare_item_for_response($favorite, $request);
        $response->set_status(201);

        return $response;
    }

    /**
     * DELETE /favorites/{post_id} — unfavorite a post.
     *
     * @param WP_REST_Request<array<string, mixed>> $request
     */
    public function delete_item($request): WP_REST_Response|WP_Error
    {
        $user_id = get_current_user_id();
        $post_id = (int) $request->get_param('post_id');

        try {
            $this->repository->remove($user_id, $post_id);
        } catch (FavoriteException $e) {
            return new WP_Error(
                'rest_favorite_not_found',
                $e->getMessage(),
                ['status' => $e->getCode() > 0 ? $e->getCode() : 404]
            );
        }

        return new WP_REST_Response(null, 204);
    }

    /**
     * Converts a Favorite VO into an HTTP response body.
     *
     * @param Favorite                              $item
     * @param WP_REST_Request<array<string, mixed>> $request
     */
    public function prepare_item_for_response($item, $request): WP_REST_Response
    {
        return rest_ensure_response($item->to_array());
    }

    /**
     * @return array<string, mixed>
     */
    public function get_item_schema(): array
    {
        if ($this->schema !== null) {
            return $this->add_additional_fields_schema($this->schema);
        }

        $this->schema = [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'favorite',
            'type'       => 'object',
            'properties' => [
                'id' => [
                    'description' => __('Unique favorite identifier.', 'apiki-favorites'),
                    'type'        => 'integer',
                    'context'     => ['view'],
                    'readonly'    => true,
                ],
                'user_id' => [
                    'description' => __('User who favorited the post.', 'apiki-favorites'),
                    'type'        => 'integer',
                    'context'     => ['view'],
                    'readonly'    => true,
                ],
                'post_id' => [
                    'description' => __('Favorited post ID.', 'apiki-favorites'),
                    'type'        => 'integer',
                    'context'     => ['view'],
                    'required'    => true,
                ],
                'created_at' => [
                    'description' => __('When the favorite was created (UTC).', 'apiki-favorites'),
                    'type'        => 'string',
                    'format'      => 'date-time',
                    'context'     => ['view'],
                    'readonly'    => true,
                ],
            ],
        ];

        return $this->add_additional_fields_schema($this->schema);
    }

    /**
     * @return array<string, mixed>
     */
    public function get_collection_params(): array
    {
        return [
            'page' => [
                'description'       => __('Current page of results.', 'apiki-favorites'),
                'type'              => 'integer',
                'default'           => 1,
                'minimum'           => 1,
                'sanitize_callback' => 'absint',
            ],
            'per_page' => [
                'description'       => __('Items per page.', 'apiki-favorites'),
                'type'              => 'integer',
                'default'           => 10,
                'minimum'           => 1,
                'maximum'           => 100,
                'sanitize_callback' => 'absint',
            ],
        ];
    }

    /**
     * Checks that a post exists and is published.
     *
     * Private helper kept inside the controller because it bridges the
     * REST layer (where post existence becomes a 404) and the WP
     * post API. The repository should not know about WP_Post.
     */
    private function post_is_favoritable(int $post_id): bool
    {
        $post = get_post($post_id);
        return $post instanceof WP_Post && $post->post_status === 'publish';
    }
}
