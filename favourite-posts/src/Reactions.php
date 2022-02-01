<?php
namespace App;

use \WP_REST_Request;
use \Exception;
use \WP_Error;

const REST_API_NAMESPACE = 'reactions/v1';

/**
 * User's interactions with posts
 */
class Reactions
{
    /**
     * Users reactions
     * @var CustomTable $reactionsTable
     */
    private static $reactionsTable;

    /**
     * Types of reactions.
     * @var CustomTable $typesTable
     */
    private static $typesTable;

    /**
     * Types of reactions.
     * @var App\ReactionApi|bool $api Proxy to App\RestApi object (sets routes and callbacks for rest-api).
     */
    private static $api;

    /**
     * Create a Reaction object
     * Loads data from self::$reactionsTable record matching $user and $post (if any)
     * @param WP_User|int $user User object or ID to search
     * @param WP_Post|int $post Post object or ID to search
     */
    public function __construct( $post = null, $user = null )
    {
        /* too early? */
        if( ! function_exists( 'get_user_by' ) ) {
            throw new \Exception( sprintf( "Tried instantiate '%s' before plugins are loaded", __CLASS__ ) );
        }
        // setup object
        $this->init();
        /* set user */
        if( is_null( $user ) ) {
            $user_id = get_current_user_id();
        }
        elseif( $user instanceof \WP_User ) {
            $user_id = $user->ID;
        }
        elseif( is_numeric( $user ) ) {
            $user_id = $user;
        }
        else {
            $user_id = email_exists( $user );
        }
        $this->user = get_user_by( 'id', $user_id );
        /* set post */
        $this->post = get_post( $post );
        /* grab reaction from database */
        $this->reaction = $this->getUserReaction( [
            'user_id' => $this->user->ID,
            'post_id' => $this->post->ID
        ] );
    }

    /**
     * Load reactions
     * Installs ddatabase and system hooks used by reactions
     */
    public function init() {
        /* prepare tables */
        $this->customTablesSetup();
        /* setup rest-api */
        $this->restApiSetup();
    }

    /**
     * Retrieve a react type from database
     * Search database for a react type matching fields in $args. Throws a Exception if $args contains invalid fields. 
     * @return array Set of reactType values. Empty array if no matches found
     */
    public function getType( array $args ) : array
    {
        $match = self::$typesTable->get_row( $args );
        if( empty( $match ) ) {
            return [];
        }
        return $match;
    }

    /**
     * List reaction types
     * Retrive a list of reaction types from database
     */
    public function getTypes() : array
    {
        return self::$typesTable->get_results([]);
    }

    /**
     * Retrieve post reactions from database
     * Function is suitable to be a wp-rest-api callback
     * @param array $args Array containing user's and post's IDs
     *  @type int $user_id A valid WP_User->ID (defaults to current user ID or 0 if no user is logged in)
     *  @type int $post_id A valid WP_Post->ID (required)
     * @return array A set of post reactions
     *  @type int $userId The queried $args[user_id]
     *  @type int $postId The queried $args[post_id]
     *  @type array $postReactions Amount of reactions per type (see ::getReactionsCount)
     *  @type string $userReaction Current user selected reaction or a empty string if user does not marked this post
     */
    public function getPostReactions( $args ) //: array|WP_Error 
    {
        $parsedArgs = [
            'user_id' => isset( $args['user_id'] ) ? $args['user_id'] : $this->user->ID,
            'post_id' => isset( $args['post_id'] ) ? $args['post_id'] : $this->post->ID
        ];
        $argsErrors = $this->get_invalid_args( $parsedArgs );
        if( is_wp_error( $argsErrors ) ) {
            return $argsErrors;
        }
        return array_merge(
            $parsedArgs,
            [
                'postReactions' => $this->getReactionsCount( [ 'post_id' => $args['post_id'] ] ),
                'userReaction' => $this->getUserReaction( $parsedArgs )
            ]
        );
    }

    /**
     * Retrieve total of each reaction type for this post.
     * Query database for reactions totals. Function is suitable to be a wp-rest-api callback.
     * @param array $args Array containing user's and post's IDs
     *  @type int $user_id A valid WP_User->ID (defaults to current user ID or 0 if no user is logged in)
     *  @type int $post_id A valid WP_Post->ID (required)
     * @return array [ string $reactionType => int $reactionsCount ]
     */
    public static function getReactionsCount( array $args ) : array
    {
        $postId = isset( $args['post_id'] ) ? $args['post_id'] : get_post();
        $tableName = self::$reactionsTable->name;
        $tableResults = self::$reactionsTable->WPDB->get_results(
            self::$reactionsTable->WPDB->prepare(
                "
                SELECT reaction, COUNT(*) as total FROM {$tableName} WHERE
                post_id = ${postId}
                "
            ),
            ARRAY_A
        );
        return array_reduce( 
            $tableResults,
            function( $acum, $item ) {
                if( empty( $item['reaction'] ) ) {
                    return $acum;
                }
                $acum[ $item['reaction'] ] = $item['total'];
                return $acum;
            },
            array()
        );
    }

    /**
     * Retrieve reaction marked by a user to a post
     * Query database for reactions. Function is suitable to be a wp-rest-api callback. 
     *  Function returns a empty string if no post_id is informed and not inside loop
     * @param array $args Array containing user's and post's IDs
     *  @type int $user_id A valid WP_User->ID (defaults to current user ID or 0 if no user is logged in)
     *  @type int $post_id A valid WP_Post->ID (required)
     * @return string|bool Reaction slug. Null if no user is informed and no user is logged. 
     */
    public function getUserReaction( array $args ) : string
    {
        $parsedArgs = [
            'post_id' => isset( $args['post_id'] ) ? $args['post_id'] : $this->post->ID,
            'user_id' => isset( $args['user_id'] ) ? $args['user_id'] : $this->user->ID
        ];
        $argsErrors = $this->get_invalid_args( $parsedArgs );
        if( is_wp_error( $argsErrors ) ) {
            return ''; // is not a error at this level -- upper function will yeld about this -- just do not get invalid data
        }
        $reactionRow = self::$reactionsTable->get_row( $parsedArgs );
        return isset( $reactionRow['reaction'] ) ? $reactionRow['reaction'] : '';
    }

    /**
     * Creates or updates a user reaction to a post.
     * Inserts on database a register of reaction given an user id and a post id.
     * @param array $args Array containing reaction, user's and post's IDs
     *  @type string $reaction Must match to a slug of self::typesTable (see $this->getTypes)
     *  @type int $user_id A valid WP_User->ID (defaults to current user ID or 0 if no user is logged in)
     *  @type int $post_id A valid WP_Post->ID (required)
     * @return bool True or false as the register is successfully inseerted to db
     */
    public function setUserReaction( array $args ) // : bool|WP_Error
    {
        $parsedArgs = [
            'post_id' => isset( $args['post_id'] ) ? $args['post_id'] : $this->post->ID,
            'user_id' => isset( $args['user_id'] ) ? $args['user_id'] : $this->user->ID,
            'reaction' => isset( $args['reaction'] ) ? $args['reaction'] : null
        ];
        $argsErrors = $this->get_invalid_args( $parsedArgs );
        if( is_wp_error( $argsErrors ) ) {
            return $argsErrors;
        }
        /* Check permissions */
        if( $post_id !== get_current_user_id() AND ! current_user_can( 'manage_options' ) ) {
            return new WP_Error(
                sprintf( "Not allowed", implode( array_keys( $invalidFields ) ) )
            );
        }
        /* current reaction to this post */
        $currentReaction = self::$reactionsTable->get_row( $parsedArgs );
        if( empty( $currentReaction ) ) {
            $parsedArgs['ID'] = self::$reactionsTable->insert( $parsedArgs );
            $parsedArgs['status'] = 'inserted';
        }
        else {
            $parsedArgs['status'] 
              = self::$reactionsTable->update( $parsedArgs, [ 'ID' => $currentReaction['ID'] ] ) !== false
              ? 'updated' 
              : 'failed';
        }
        return $parsedArgs;
    }

    /**
     * Creates or updates a user reaction to a post.
     * Inserts on database a register of reaction given an user id and a post id.
     * @param array $args Array containing reaction, user's and post's IDs
     *  @type string $reaction Must match to a slug of self::typesTable (see $this->getTypes)
     *  @type int $user_id A valid WP_User->ID (defaults to current user ID or 0 if no user is logged in)
     *  @type int $post_id A valid WP_Post->ID (required)
     * @return bool True or false as the register is successfully inseerted to db
     */
    public function unsetUserReaction( array $args ) : object
    {
        $parsedArgs = [
            'post_id' => isset( $args['post_id'] ) ? $args['post_id'] : $this->post->ID,
            'user_id' => isset( $args['user_id'] ) ? $args['user_id'] : $this->user->ID,
            'reaction' => isset( $args['reaction'] ) ? $args['reaction'] : null
        ];
        if( empty( $parsedArgs['reaction'] ) ) {
            unset( $parsedArgs['reaction'] );
        }
        $argsErrors = $this->get_invalid_args( $parsedArgs );
        if( is_wp_error( $argsErrors ) ) {
            return $argsErrors;
        }
        /* Check permissions */
        if( $post_id !== get_current_user_id() AND ! current_user_can( 'manage_options' ) ) {
            return new WP_Error(
                sprintf( "Not allowed", implode( array_keys( $invalidFields ) ) )
            );
        }
        // delete record
        $parsedArgs['deleted'] = self::$reactionsTable->delete( $parsedArgs ) ? true : false;
        return (object)$parsedArgs;
    }

    public function apiEndpointProxy( WP_REST_Request $request )
    {
        $method = $request->get_method();
        $args = $request->get_params();
        if( $method == 'POST' ) {
            return $this->setUserReaction( $args );
        }
        elseif( $method == 'DELETE' ) {
            return $this->unsetUserReaction( $args );
        }
        else {
            // default to GET
            return $this->getPostReactions( $args );
        }
    }

    /**
     * Set up the rest-api endpoints
     * Activate REST-API routes x callbacks
     */
    public function restApiSetup() {
        /* add rest-api routes for setting/unsetting/querying reactions */
        self::$api = new RestApi(
            [
                'namespace' => REST_API_NAMESPACE,
                'routes' => [
                    [
                        'route' => '/types',
                        'methods' => 'GET',
                        'callback' => [ $this, 'getTypes' ]
                    ],
                    [
                        'route' => '/post/(?<post_id>\d+)',
                        'methods' => [ 'GET', 'POST', 'DELETE' ],
                        'callback' => [ $this, 'apiEndpointProxy' ]
                    ]
                ]
            ]
        );
    }

    /**
     * Check common args
     * Returns a WP_Error if any not compilant $arg was passed and
     *  null if all args validates.
     * @param array $args
     *  @type int $user_id WP_User->ID to check against WP DB
     *  @type int $post_id WP_Post->ID to check against database
     *  @type string $reaction To search on ReactionType/slug table/column
     */
    public function get_invalid_args( array $args ) : ?WP_Error
    {
        if( array_key_exists( 'post_id', $args ) and ! get_post( $args['post_id'] ) ) {
            return new WP_Error( sprintf( __( 'Invalid post ID (%s)', 'favourite-posts' ), $args['post_id'] ) );
        }
        if( array_key_exists( 'user_id', $args ) and ! get_user_by( 'id', $args['user_id'] ) ) {
            return new WP_Error( sprintf( __( 'Invalid user ID (%s)', 'favourite-posts' ), $args['user_id'] ) );
        }
        if( array_key_exists( 'reaction', $args ) and empty( $args['reaction'] ) OR $this->getType( [ 'slug' => $args['reaction'] ] ) == false ) {
            return new WP_Error( sprintf(
                __( 'Invalid reaction slug (%s)', 'favourite-posts' ),
                empty( $args['reaction'] ) ? '<empty>' : $args['reaction']
            ) );
        }
        return null; // on PHP 8.0 we will have mixed and composite return types!
    }


    public static function customTablesSetup() {
        /* Table to contain reactions */
        self::$reactionsTable = new CustomTable(
            [
                'name' => 'reactions',
                'columns' => [
                    'user_id' => [
                        'type' => 'bigint'
                    ],
                    'post_id' => [
                        'type' => 'bigint'
                    ],
                    'reaction' => [
                        'type' => 'tinytext'
                    ]
                ]
            ]
        );
        /* table to hold differents types of reactions */
        self::$typesTable = new CustomTable(
            [
                'name' => 'reactions_types',
                'columns' => [
                    'slug' => [
                        'type' => 'tinytext',
                    ],
                    'title' => [
                        'type' => 'tinytext',
                    ],
                    'description' => [
                        'type' => 'tinytext',
                    ],
                    'icon' => [
                        'type' => 'tinytext',
                    ]
                ]
            ]
        );
        /* default reaction (favourite post) */
        if( self::$typesTable->is_empty() ) {
            self::$typesTable->insert(
                [
                    'slug' => 'favourite',
                    'title' => __('Favourite Post'),
                    'description' => __('Make this post a favourite'),
                    'icon' => __DIR__ . '/icon/heart.svg'
                ]
            );
        }
    }

}