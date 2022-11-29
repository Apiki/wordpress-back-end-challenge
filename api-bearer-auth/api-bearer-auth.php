<?php
/*
Plugin Name: API Bearer Auth
Description: Authentication for REST API
Text Domain: api_bearer_auth
Version: 20200916
Author: Michiel van Eerd
License: GPL2
*/

// Always update this!
define('API_BEARER_AUTH_PLUGIN_VERSION', '20200916');

/**
 * How long access token will be valid.
 */
if (!defined('API_BEARER_ACCESS_TOKEN_VALID_IN_SECONDS')) {
  define('API_BEARER_ACCESS_TOKEN_VALID_IN_SECONDS', 86400); // 86400 = valid for one day
}

if (!class_exists('API_Bearer_Auth')) {

  require_once(__DIR__ . '/db.php');

  class API_Bearer_Auth {
    
    /**
     * Database object
     */
    private $db;

    /**
     * Our URLS that don't require authentication.
     * If you use this plugin, all calls to the REST API are blocked for unauthenticated users.
     * Use the api_bearer_auth_unauthenticated_urls filter to add other URLS that don't require authentication.
     */
    private static $UNAUTHENTICATED_ENDPOINTS = [
      'POST' => [
        "/wp-json/api-bearer-auth/v1/login/?",
        "/wp-json/api-bearer-auth/v1/tokens/refresh/?"
      ]
    ];
  
    function __construct() {
      $this->db = new API_Bearer_Auth_Db();
      add_filter('determine_current_user', [$this, 'determine_current_user_filter']);
      add_filter('rest_authentication_errors', [$this, 'rest_authentication_errors_filter']);
      add_action('rest_api_init', [$this, 'rest_api_init_action']);
      add_action('deleted_user', [$this, 'deleted_user_action']);
      if (is_admin()) {
        // We don't use the activation_hook, because this is not called for mu plugins.
        add_action('plugins_loaded', [$this, 'admin_plugins_loaded_action']);
      }
      add_filter('manage_users_columns', [$this, 'manage_users_columns_filter']);
      add_filter('manage_users_custom_column', [$this, 'manage_users_custom_column_filter'], 10, 3);
      add_filter('bulk_actions-users', [$this, 'bulk_actions_edit_users_filter']);
      add_filter('handle_bulk_actions-users', [$this, 'handle_bulk_actions_users'], 10, 3);
    }

    public function bulk_actions_edit_users_filter($bulk_actions) {
      $bulk_actions['revoke_tokens'] = 'Revoke API tokens';
      return $bulk_actions;
    }

    public function handle_bulk_actions_users($redirect_to, $doaction, $user_ids) {
      if ($doaction !== 'revoke_tokens') {
        return $redirect_to;
      }
      foreach ($user_ids as $user_id) {
        $this->db->delete_by_user_id($user_id);
      }
      $redirect_to = add_query_arg('bulk_revoked_tokens', count($user_ids), $redirect_to);
      return $redirect_to;
    }

    public function manage_users_columns_filter($column_headers) {
      $column_headers['access_token_valid'] = 'Access token expires';
      return $column_headers;
    }

    public function manage_users_custom_column_filter($output, $column_name, $user_id) {
      switch ($column_name) {
        case 'access_token_valid':
          $s = $this->db->get_user_token_info($user_id, 'access_token_valid');
          return !empty($s) ? $s : '-';
        default:
          return $output;
      }
    }

    public function admin_plugins_loaded_action() {
      global $wpdb;
      // This is nicer, but also less performant, so make use of a define of the version
      //require_once(ABSPATH . 'wp-admin/includes/plugin.php');
      //$data = get_plugin_data(__FILE__, false, false);
      //$data = get_file_data(__FILE__, ['Version' => 'Version']);
      if (get_option('api_bearer_auth_activated') != API_BEARER_AUTH_PLUGIN_VERSION) {
        self::on_activation();
        update_option('api_bearer_auth_activated', API_BEARER_AUTH_PLUGIN_VERSION, false);
      }
    }
 
    /**
     * Action fired when user is deleted. Deletes tokens for this user.
     * @param integer $id User id.
     */ 
    public function deleted_user_action($id) {
      $this->db->delete_by_user_id($id);
    }
  
    /**
     * Code runs on activation of this plugin. We create / upgrade the tokens table.
     */
    public static function on_activation() {
      // dbDelta has too many weird things...
      require_once(__DIR__ . '/migrations.php');
      api_bearer_auth_migrate();
    }
  
    /**
     * Code runs on uninstall. Drop table.
     */
    public static function on_uninstall() {
      global $wpdb;
      delete_option('api_bearer_auth_activated');
      $wpdb->query('DROP TABLE ' . $wpdb->base_prefix . 'user_tokens');
    }
  
    /**
     * By default, this filter determines the current user from the cookie.
     * We use it to determine the current user from the access token in the Authorization header.
     * If no Authorization header exists, just return the default result.
     */
    public function determine_current_user_filter($user_id) {
  
      // If we use wp-cli we have no headers to look for
      if (php_sapi_name() === 'cli') {
        return $user_id;
      }
 
      /**
       * Make sure to add the lines below to .htaccess
       * otherwise Apache may strip out the auth header.
       * RewriteCond %{HTTP:Authorization} ^(.*)
       * RewriteRule ^(.*) - [E=HTTP_AUTHORIZATION:%1]
       */
      // On some servers the headers are changed to upper or lowercase.
      $headers = array_change_key_case(function_exists('apache_request_headers')
        ? apache_request_headers() : $_SERVER, CASE_LOWER);
      $possibleAuthHeaderKeys = ['authorization', 'http_authorization', 'redirect_http_authorization'];
      $authHeader = null;
      foreach ($possibleAuthHeaderKeys as $key) {
        if (!empty($headers[$key])) {
          $authHeader = $headers[$key];
          break;
        }
      }
      
      if (!empty($authHeader)) {
        // 7 = strlen('Bearer ');
        $access_token = substr($authHeader, 7);
        $my_user_id = $this->db->get_user_id_from_access_token($access_token);
        if (!is_wp_error($my_user_id)) {
          return $my_user_id;
        }
        return false;
      }
      return $user_id;
    }
  
    /**
     * Check if we are allowed to do this API request.
     */
    public function rest_authentication_errors_filter($error) {

      // Preflight requests (OPTIONS) should not require autentication:
      // https://stackoverflow.com/a/61852875/1294832
      if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        return $error;
      }
  
      // If $error is not empty, another auth method has set this
      // so we don't need to do anything.
      if (!empty($error)) {
        return $error;
      }
  
      if (!is_user_logged_in()) {

        // Strip out query string
        $currentUrl = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . strtok($_SERVER['REQUEST_URI'], '?');
        $siteUrl = get_site_url();

        $custom_urls = [];
        /**
         * Filter: api_bearer_auth_unauthenticated_urls
         * Add URLs that should be avialble to unauthenticated users.
         * Specify only the part after the site url, e.g. /wp-json/wp/v2/users
         * Each URL will be prepended by the value of get_site_url()
         * And each resulting URL will be put in between ^ and $ regular expression signs.
         */
        $custom_urls = apply_filters('api_bearer_auth_unauthenticated_urls', $custom_urls, $_SERVER['REQUEST_METHOD']);
        $my_urls = [];
        if (!empty(self::$UNAUTHENTICATED_ENDPOINTS[$_SERVER['REQUEST_METHOD']])) {
          $my_urls = self::$UNAUTHENTICATED_ENDPOINTS[$_SERVER['REQUEST_METHOD']];
        }
        $urls = array_merge($custom_urls, $my_urls);
        foreach ($urls as $url) {
          if (preg_match('@^' . $siteUrl . $url . '$@', $currentUrl)) {
            // We did not authenticate this user (because we have no user).
            // But this request is allowed publicly for us.
            // So return default (null), maybe other filter callbacks will do something with this.
            return $error;
          }
        }

        // No whitelisted URL found.
        return new WP_Error('api_bearer_auth_not_logged_in',
          __('You are not logged in.', 'api_bearer_auth'), ['status' => 401]);

      } elseif (!is_user_member_of_blog()) {

        return new WP_Error('api_api_bearer_auth_wrong_blog',
          __('You are no member of this blog.', 'api_bearer_auth'), ['status' => 401]);

      }
      
      // We have and authenticated user that is member of this blog and we have no problem with this request.
      // But maybe other filters have, so return $error (that will be null)
      return $error;

    }
  
    /**
     * Registering the routes endpoints
     */
    public function rest_api_init_action() {
  
      register_rest_route('api-bearer-auth/v1', '/login', [
        'methods' => 'POST',
        'callback' => [$this, 'callback_login'],
        'permission_callback' => '__return_true',
        'args' => [
          'username' => [
            'required' => true,
          ],
          'password' => [
            'required' => true,
          ],
        ]
      ]);
  
      register_rest_route('api-bearer-auth/v1', '/tokens/refresh', [
        'methods' => 'POST',
        'callback' => [$this, 'callback_refresh_token'],
        'permission_callback' => '__return_true',
        'args' => [
          'token' => [
            'required' => true
          ]
        ]
      ]);
  
    }
  
    public function callback_refresh_token(WP_REST_Request $request) {

      $body = $request->get_json_params();
      $client_name = !empty($body['client_name']) ? $body['client_name'] : '';

      $user_id = $this->db->get_user_id_from_refresh_token($body['token'], $client_name);
      if (empty($user_id)) {
        return new WP_Error('api_api_bearer_auth_error_invalid_token',
          __('Invalid token.', 'api_api_bearer'), ['status' => 401]);
      }
      if (!is_user_member_of_blog($user_id)) {
        return new WP_Error('api_api_bearer_auth_wrong_blog',
          __('You are no member of this blog.', 'api_bearer_auth'), ['status' => 401]);
      }
      $userInfo = $this->db->refresh_access_token($body['token'], $user_id, $client_name);
      if (empty($userInfo)) {
        return new WP_Error('api_api_bearer_auth_error_invalid_token',
        __('Invalid token.', 'api_api_bearer'), ['status' => 401]);
      }
      return rest_ensure_response([
        'access_token' => $userInfo['access_token'],
        'expires_in' => $userInfo['expires_in']
      ]);
    }
  
    public function callback_login(WP_REST_Request $request) {
      
      $body = $request->get_json_params();

      $user = wp_authenticate($body['username'], $body['password']);
      if (is_wp_error($user)) {
        return rest_ensure_response($user);
      }
      if (!is_user_member_of_blog($user->ID)) {
        return new WP_Error('api_api_bearer_auth_wrong_blog',
          __('You are no member of this blog.', 'api_bearer_auth'), ['status' => 401]);
      }
      // Update or insert access en refresh tokens
      if (($result = $this->db->login($user->ID, !empty($body['client_name']) ? $body['client_name'] : '')) !== false) {

        $safeUser = $user;
        unset($safeUser->data->user_pass);

        return rest_ensure_response([
          'wp_user' => $safeUser,
          'access_token' => $result['access_token'],
          'expires_in' => $result['expires_in'],
          'refresh_token' => $result['refresh_token'],
        ]);
      }
      // WP user ophalen wel, maar Access token aanmaken niet gelukt
      return rest_ensure_response(new WP_Error('api_api_bearer_auth_create_token',
        __('Error creating tokens.', 'api_api_bearer')));
    }
  
  }
 
  //register_activation_hook(__FILE__, ['API_Bearer_Auth', 'on_activation']);
  register_uninstall_hook(__FILE__, ['API_Bearer_Auth', 'on_uninstall']);

  new API_Bearer_Auth();
}

