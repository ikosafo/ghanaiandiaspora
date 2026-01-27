<?php
define( 'WP_CACHE', true );


























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
define( 'DB_NAME', 'ghanfhwj_gha' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

define('WP_HOME', 'http://ghdiaspora.local');
define('WP_SITEURL', 'http://ghdiaspora.local');

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
define( 'AUTH_KEY',         'cwigfpltqwhekqmmykx18ugpahn8sumnofzzq4jllfrafkrodvdweb2cpuhufoym' );
define( 'SECURE_AUTH_KEY',  '3tribulr9fhfy0rbm8teqcg0lrgcdrrmrrdwjcyfx1myvb170i33pqq1cjyjqqsf' );
define( 'LOGGED_IN_KEY',    'wvxvhlezaxyufcwjhhdve7whayxrbr2d6axghd9olqqs0ynq2eqmoesmraekwdi8' );
define( 'NONCE_KEY',        'syrrttpzjwexycoikmclkc0hooocht23gefvbvigjk8afk5rtcype9o89iranzgo' );
define( 'AUTH_SALT',        'shr5moitusnrwwntl7qo97okmxq7dw7qhhixpltx6n4zr7ehsalm766jm8dn6rgx' );
define( 'SECURE_AUTH_SALT', '24odnpuzxglus5y7ycmbaq9h7s95wacjwngyjkamnmvof6hturbcan8kgjtg7ofl' );
define( 'LOGGED_IN_SALT',   'x4ig657gq3dq6xdysxi1yj9hmloszrcrsicptdq2sm5wowehabatbt0jeirqlulm' );
define( 'NONCE_SALT',       'j9yt7xliybluc4mqdwtdybu7ivskucz4qwqeucedx8ss9lma1gvjulwwd26pca4q' );

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
$table_prefix = 'wphc_';

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



define( 'DUPLICATOR_AUTH_KEY', '5ZXRaW$T~?%!JO y@3 |hWm.5s`al{90~6hq1*d|y<qTct)u trAl<l{Xb&Cr`M(' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
