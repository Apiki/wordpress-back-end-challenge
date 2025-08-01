<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'Apiki-favorites' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

if ( !defined('WP_CLI') ) {
    define( 'WP_SITEURL', $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] );
    define( 'WP_HOME',    $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] );
}



/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'jyDnVgBBppWZSlfvDy6zMJcTRT6Ld8wLopqdExBvCGYdoufMPOx3Ia4X4EQeS5wF' );
define( 'SECURE_AUTH_KEY',  'mBQ9h2nGdrcH1nt9fJQIlkpcBWSzNtr2Kh8SPby2tski6IFXSOPgEOrGrQVnvxxO' );
define( 'LOGGED_IN_KEY',    'bPGoQffhESHfLT4qI4IhSMvwa3clcqHceLpVfg0GkI2nQAXilQy0dU1V72EZkXQz' );
define( 'NONCE_KEY',        'c3y6Nmb0pfzm9I3rSPU5iu8YKYczkAYIaaU43YT5b9QT83umsZcTebHhfN7mgIt7' );
define( 'AUTH_SALT',        'uJvplhHUExYQrv857g6b3538O8d2xzfMQVeQo9nx5Lmy9nUu1MFUtIBQAr9PnZAh' );
define( 'SECURE_AUTH_SALT', '1dQeGK89gp8u9PQ5ux9SOK5h4dIwVYbUo61fvRdRXTwc55tt7vFg6NjSE9vPijoR' );
define( 'LOGGED_IN_SALT',   'SPWOokOGsvAHiz9WPigDb4AP9PNohBt2uB1jXWbvTXp9AaFLwzOPKRBOqzGwLAsm' );
define( 'NONCE_SALT',       'XfrTUrpI3cO5RP4bjTgZAtdonolkPEqcuvq1cAu1PwW52DZUvBuJLsIdU3dK4Vef' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
