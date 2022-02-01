<?php
namespace App;

/**
 * Plugin Name
 *
 * @package           FavouritePosts
 * @author            Lucilio Correia
 * @copyright         2022 Hackeamos.org
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Favourite Posts
 * Description:       Allow users to favourite posts (Apiki Backup WP Challenge)
 * Version:           0.0.1
 * Requires at least: 5.9
 * Requires PHP:      7.4
 * Author:            Lucilio Correia
 * Author URI:        https://lucilio.net
 * Text Domain:       plugin-slug
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

require __DIR__ . '/vendor/autoload.php';

function plugin_file_path() {
    return plugin_basename( __FILE__ );
}

add_action( 'plugins_loaded', function(){
    $user = get_user_by( 'login', 'admin' );
    $post = get_posts( [ 'post_status' => 'published' ] )[0];
    $reaction = new Reactions( $post, $user );
} );





/**
 * Whetheter post was favourited or not
 * Return true if WP_Post $post was favourited by WP_User->ID $user with 
 *  $post was favourited by $user with $fa
function is_favourite( $post ) : bool
{

}

$api = new RestApi(
    [
        'namespace' => 'apikich/v1',
        'routes' => [
            [
                'route' => '/favourite_posts/(?<id>\d+)',
                'callback' => 'get_favorite_posts'
            ],
            [
                'route' => '/favourite_posts/(?<id>\d+)/(?<fav>\w+)',
                'callback' => 'set_favorite_posts'
            ]
        ]
    ]
);