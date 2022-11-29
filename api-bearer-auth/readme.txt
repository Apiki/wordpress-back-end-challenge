=== API Bearer Auth ===
Contributors: michielve
Tags: api, rest-api, authentication, jwt, jwt-tokens
Requires at least: 4.6
Tested up to: 6.0
Requires PHP: 5.4.0
Stable tag: trunk
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Access and refresh tokens based authentication plugin for the REST API.

== Important update ==

__Update immediately if you're using a version below 20200807. Before this version all access tokens were updated when calling the refresh callback.__

If you are affected by this the fastest solution is to execute this query:

`update wp_user_tokens set access_token_valid = NOW();`

This will invalidate all access tokens. This means that all users need to refresh their access token and will get a new access token and a unique one this time.

A big thank to @harchvertelol for reporting this and suggesting the fix as well!

== Description ==

The API Bearer Auth plugin enables authentication for the REST API by using JWT access an refresh tokens. After the user logs in, the access and refresh tokens are returned and can be used for the next requests. Issued tokens can be revoked from within the users admin screen. See below for the endpoints.

<strong>Note that after activating this plugin, all REST API endpoints will need to be authenticated, unless the endpoint is whitelisted in the `api_bearer_auth_unauthenticated_urls` filter (see FAQ for how to use this filter).

= JWT =

Access tokens can be formatted as JWT tokens. For this to work, you first have to create a secret and add it to the wp-config.php file. If you don't do this, access tokens will work also, but are just random strings. To create a random secret key, you can do for example:

`base64_encode(openssl_random_pseudo_bytes(64));`

And then add the result to wp-config:

`define('API_BEARER_JWT_SECRET', 'mysecretkey');`

If you have problems, you can verify your JWT tokens at: <https://jwt.io/>

= Revoke tokens =

This plugin adds a column to the users table in de admin where you can see when a token expires. You can also revoke tokens by selection the "Revoke API tokens" from the bulk actions select box.

= API endpoints =

Note that all endpoints <strong>expect JSON in the POST body</strong>.

<strong>Login</strong>

Endpoint:

`POST /api-bearer-auth/v1/login`

Request body:

__Note: `client_name` is optional. But if you use it, make sure to use it as well for the refresh call!__

`{"username": "my_username", "password": "my_password", "client_name": "my_app"}`

Response:

`{
  "wp_user": {
    "data": {
      "ID": 1,
      "user_login": "your_user_login",
      // other default Wordpress user fields
    }
  },
  "access_token": "your_access_token",
  "expires_in": 86400, // number of seconds
  "refresh_token": "your_refresh_token"
}`

Make sure to save the access and refresh token!

<strong>Refresh access token</strong>

Endpoint:

`POST /api-bearer-auth/v1/tokens/refresh`

Request body:

__Note: `client_name` is optional. But if you did use it for the login call, make sure to use it here as well!__

`{"token": "your_refresh_token", "client_name": "my_app"}`

Response success:

`{
  "access_token": "your_new_access_token",
  "expires_in": 86400
}`

Response when sending a wrong refresh token is a 401:

`{
  "code": "api_api_bearer_auth_error_invalid_token",
  "message": "Invalid token.",
  "data": {
    "status": 401
  }
}`

<strong>Do a request</strong>

After you have the access token, you can make requests to authenticated endpoints  with an Authorization header like this:

`Authorization: Bearer <your_access_token>`

Note that Apache sometimes strips out the Authorization header. If this is the case, make sure to add this to the .htaccess file:

`RewriteCond %{HTTP:Authorization} ^(.*)
# Don't know why, but some need the line below instead of the RewriteRule line
# SetEnvIf Authorization .+ HTTP_AUTHORIZATION=$0
RewriteRule ^(.*) - [E=HTTP_AUTHORIZATION:%1]`

If you are not logged in or you send an invalid access token, you get a 401 response:

`{
  "code": "api_bearer_auth_not_logged_in",
  "message": "You are not logged in.",
  "data": {
    "status": 401
  }
}`

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/api-bearer-auth` directory, or install the plugin through the WordPress plugins screen directly.
2. If you want your access tokens to be formatted as JWT tokens, define a random string as a `API_BEARER_JWT_SECRET` define in your wp-config.php file.
3. Activate the plugin through the 'Plugins' screen in WordPress.
4. From now on, every REST API endpoint needs to be authenticated.

== Frequently Asked Questions ==

= Change time the access tokens are valid =

By default an access token is valid for 1 day. You can change this, by defining the `API_BEARER_ACCESS_TOKEN_VALID_IN_SECONDS` constant in your wp-config.php file.

`define('API_BEARER_ACCESS_TOKEN_VALID_IN_SECONDS', 3600); // 1 minute`

= Whitelist unauthenticated URLs =

By default all REST API endpoints are only available for authenticated users. If you want to add some more endpoints to this whitelist, you can use the `api_bearer_auth_unauthenticated_urls` filter. Note that you need to specify the endpoint relative to the `site_url()` and that you can specify regular expressions.

For example:

`add_filter('api_bearer_auth_unauthenticated_urls', 'api_bearer_auth_unauthenticated_urls_filter', 10, 2);
function api_bearer_auth_unauthenticated_urls_filter($custom_urls, $request_method) {
  switch ($request_method) {
    case 'POST':
      $custom_urls[] = '/wp-json/myplugin/v1/something/?';
      break;
    case 'GET':
      $custom_urls[] = '/wp-json/myplugin/v1/something/other/?';
      break;
  }
  return $custom_urls;
}`

== Changelog ==

= 20200916 =
* Added permission_callback to prevent error in log.

= 20200911 =
* Added client_name key to login and refresh endpoint.
* Database changes: client_name and some indexes.

= 20200902 =
* Fix for servers that change the headers to lower or uppercase.

= 20200818 =
* Removed `user_pass` from returned user after login call.

= 20200807 =
* Big bug fixed (thanks to @harchvertelol!), please update immediately! Calling the refresh request will update ALL access tokens! This is now fixed.

= 20200717 =
* Preflight requests (OPTIONS) should not require autentication:

= 20190908 =
* Removed Swagger

= 20190907 =
* Sanitize user input for swagger file

= 20181229 =
* Revoke tokens from the users admin screen
* Better documentation

= 20181228 =
* Migrations
* Refresh token is not a JWT token

= 20181226 =
* Also returns expires_in for access token
* The use with a verified access JWT token is returned directly now without
querying the database first.
* Changed the define for valid time access token

= 20181225 =
* Added JWT tokens

= 20181223 =
* Tested with Wordpress 5.0.2
* Added Swagger to make testing of the plugin easier

= 20171208 =
* Define constants to change valid period of access and refresh tokens

= 20171130 =
* First release

