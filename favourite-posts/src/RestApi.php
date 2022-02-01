<?php
namespace App;

/**
 * Create a new API endpoint
 */
class RestApi
{
    /**
     * Register endpoints
     * Hook on Rest Api init to register this endpoint
     */
    public function __construct( array $args = [] )
    {
        $this->namespace = isset( $args['namespace'] ) ? $args['namespace'] : 'wp/v2';
        $routes = isset( $args['routes'] ) ? $args['routes'] : array();
        array_walk( $routes, [ $this, 'addRoute' ] );
        add_action( 'rest_api_init', [ $this, 'init' ], 10 );
    }

    /**
     * Create a endpoint
     * Adds a endpoint $endpoint to the wordpress REST-API tied to $callback
     * @param array $args Endpoint settings
     *  @type string $endpoint Regex pattern to endpoint
     *  @type callable $callback Function, method or closure tied to this endpoint
     *  @type string $method Method to register endpoint, defaults to GET
     *  @type bool $override If this registration should override 
     *    previous routes as in WP's register_rest_route. Defaults to true;
     */
    public function addRoute( array $args  ) : void
    {
        if( ! is_string( $args['route'] ) ) {
            throw new \Exception( sprintf( "Route '%s' is invalid", $args['route'] ) );
        }
        if( ! is_callable( $args['callback'] ) ) {
            throw new \Exception( sprintf( "Invalid callback type (%s) for '%s'", gettype( $args['callback'] ), $args['callback'] ) );
        }
        $this->routes[] = [
            'route' => $args['route'],
            'callback' => $args['callback'],
            'namespace' => isset( $args['namespace'] ) ? $args['namespace'] : $this->namespace,
            'override' => isset( $args['override'] ) ? $args['override'] : true,
            'methods' => isset( $args['methods'] ) ? $args['methods'] : 'GET'
        ];
    }

    /**
     * Load endpoints
     * Configure each REST API route stored in ->routes property
     */
    public function init() : void
    {
        foreach( $this->routes as $route_data ) {
             register_rest_route(
                $route_data['namespace'],
                $route_data['route'],
                [
                    'callback' => $route_data['callback'],
                    'methods' => $route_data['methods']
                ],
                $route_data['override']
            );
        }
    }

}