<?php
/**
 * WordPress base configuration
 * 
 * This file contains database settings and security configurations
 * 
 * @package WordPress
 */

// ====================
// Debugging Settings
// ====================
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('DISABLE_WP_CRON', true);
define('WP_DEBUG_DISPLAY', false);
@ini_set('display_errors', 0);

// ====================
// Database Settings
// ====================
define('DB_NAME', 'Apiki-favorites');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_HOST', 'localhost');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

// ====================
// Site URL Settings
// ====================
if (!defined('WP_CLI')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    define('WP_SITEURL', $protocol . $_SERVER['HTTP_HOST']);
    define('WP_HOME', $protocol . $_SERVER['HTTP_HOST']);
}

// ====================
// Security Keys
// ====================
define('AUTH_KEY',         'jyDnVgBBppWZSlfvDy6zMJcTRT6Ld8wLopqdExBvCGYdoufMPOx3Ia4X4EQeS5wF');
define('SECURE_AUTH_KEY',  'mBQ9h2nGdrcH1nt9fJQIlkpcBWSzNtr2Kh8SPby2tski6IFXSOPgEOrGrQVnvxxO');
define('LOGGED_IN_KEY',    'bPGoQffhESHfLT4qI4IhSMvwa3clcqHceLpVfg0GkI2nQAXilQy0dU1V72EZkXQz');
define('NONCE_KEY',        'c3y6Nmb0pfzm9I3rSPU5iu8YKYczkAYIaaU43YT5b9QT83umsZcTebHhfN7mgIt7');
define('AUTH_SALT',        'uJvplhHUExYQrv857g6b3538O8d2xzfMQVeQo9nx5Lmy9nUu1MFUtIBQAr9PnZAh');
define('SECURE_AUTH_SALT', '1dQeGK89gp8u9PQ5ux9SOK5h4dIwVYbUo61fvRdRXTwc55tt7vFg6NjSE9vPijoR');
define('LOGGED_IN_SALT',   'SPWOokOGsvAHiz9WPigDb4AP9PNohBt2uB1jXWbvTXp9AaFLwzOPKRBOqzGwLAsm');
define('NONCE_SALT',       'XfrTUrpI3cO5RP4bjTgZAtdonolkPEqcuvq1cAu1PwW52DZUvBuJLsIdU3dK4Vef');

// ====================
// Database Table Prefix
// ====================
$table_prefix = 'wp_';

// ====================
// Absolute Path
// ====================
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

// ====================
// Additional Security
// ====================
define('DISALLOW_FILE_EDIT', true);
define('FORCE_SSL_ADMIN', true);

// ====================
// Load WordPress
// ====================
require_once ABSPATH . 'wp-settings.php';