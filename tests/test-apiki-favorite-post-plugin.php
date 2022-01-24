<?php
/**
 * Class Test_Apiki_Favorite_Post_Plugin
 *
 * @package Apiki_Favorite_Post_Plugin
 */

use \FavoritePostPlugin\FavoritePostPlugin;

/**
 * Sample test case.
 */
class Test_Apiki_Favorite_Post_Plugin extends \WP_UnitTestCase
{
    /**
     * Holds the WP REST Server object
     *
     * @var WP_REST_Server
     */
    private $server;

    /**
     * Holds the plugin object
     *
     * @var FavoritePostPlugin
     */
    private $plugin;

    /**
     * Holds user id.
     *
     * @var int
     */
    private $user_id;

    /**
     * Holds post id.
     *
     * @var int
     */
    private $post_id;

    /**
     * Create a user and a post for our test.
     */
    public function setUp()
    {
        parent::setUp();

        // Initiating the REST API.
        global $wp_rest_server;
        $this->server = $wp_rest_server = new \WP_REST_Server;
        $this->plugin = FavoritePostPlugin::getInstance();
        $this->plugin->activate();
        do_action('rest_api_init');

        $this->user_id = $this->factory->user->create(array(
            'display_name' => 'test_author',
        ));
        wp_set_current_user($this->user_id);

        $this->post_id = $this->factory->post->create([
            'post_title' => 'Hello World',
            'post_content' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
            'post_status' => 'publish',
            'post_author' => $this->user_id
        ]);
    }

    /**
     * Delete the user and post after the test.
     */
    public function tearDown()
    {
        parent::tearDown();
        wp_set_current_user(0);
        wp_delete_user($this->user_id);
        wp_delete_post($this->post_id);
        $this->plugin->deactivate();
    }

    public function test_mark_as_favorite()
    {
        $request = new WP_REST_Request('GET', '/apiki-favorite-post/v1/favorite/' . $this->post_id);
        $response = $this->server->dispatch($request);
        $data = $response->get_data();
        $this->assertTrue($data['success']);

        $request = new WP_REST_Request('GET', '/apiki-favorite-post/v1/' . $this->post_id);
        $response = $this->server->dispatch($request);
        $data = $response->get_data();
        $this->assertTrue($data['favorite']);
    }

    public function test_unmark_as_favorite()
    {
        $request = new WP_REST_Request('GET', '/apiki-favorite-post/v1/favorite/' . $this->post_id);
        $response = $this->server->dispatch($request);
        $data = $response->get_data();
        $this->assertTrue($data['success']);

        $request = new WP_REST_Request('GET', '/apiki-favorite-post/v1/unfavorite/' . $this->post_id);
        $response = $this->server->dispatch($request);
        $data = $response->get_data();
        $this->assertTrue($data['success']);

        $request = new WP_REST_Request('GET', '/apiki-favorite-post/v1/' . $this->post_id);
        $response = $this->server->dispatch($request);
        $data = $response->get_data();
        $this->assertFalse($data['favorite']);
    }

    public function test_not_logged_error()
    {
        wp_set_current_user(0);
        $request = new WP_REST_Request('GET', '/apiki-favorite-post/v1/favorite/' . $this->post_id);
        $response = $this->server->dispatch($request);
        $data = $response->get_data();
        $this->assertSame($response->get_status(), 500);
        $this->assertSame($data['code'], 'not_logged_in');
        wp_set_current_user($this->user_id);
    }

    public function test_not_found_post()
    {
        $request = new WP_REST_Request('GET', '/apiki-favorite-post/v1/favorite/0');
        $response = $this->server->dispatch($request);
        $data = $response->get_data();
        $this->assertSame($response->get_status(), 500);
        $this->assertSame($data['code'], 'post_not_found');
    }


    public function test_invalid_post_id()
    {
        $request = new WP_REST_Request('GET', '/apiki-favorite-post/v1/favorite/-1');
        $response = $this->server->dispatch($request);
        $data = $response->get_data();
        $this->assertSame($response->get_status(), 404);
        $this->assertSame($data['code'], 'rest_no_route');
    }
}
