<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'webstejcciba' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost:3307' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

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
define( 'AUTH_KEY',         'SS]=,)XJXy;CjrIZ:D73]f[WlbOk&CP=:,+P#^ZA#Wi6@.9Pfyoz^]d/|@KjPJ0Q' );
define( 'SECURE_AUTH_KEY',  '%ZKl{!()Bk2d>WV,Hwg3o+69?aGqP,w:Ay=3w AgHW hgldL*RC}qb3)G*<,Ugg(' );
define( 'LOGGED_IN_KEY',    'Izu^`cc$3UXh)oh>&%$Nudo?bQ61ky,<<O&>mxSbYa~}[S GyE=]APM#gzrq&.d$' );
define( 'NONCE_KEY',        'IxNRS9z1-6lAr8EsoYJ`MB{P1yYk;4WBd)SCcsm@Iy`x8xR{}]=P6Q`d26wVo/3g' );
define( 'AUTH_SALT',        ')!Y+Oqu&2vbHQJA[_fY*Eyoq.fB2oTe@~~U@D-5T Q*.H*HMp=g>t5Lk?lo`VYJw' );
define( 'SECURE_AUTH_SALT', '`Un&J-X 1<Sd7C#1b{d.^(,fI-EGd^^WhS3^(b_V!}T}r6S/pB&Q(9]4?]TT3B2:' );
define( 'LOGGED_IN_SALT',   'gX ~M*Ojq~PAqAB8gVy-vsEU7)Nabu8J&IgQXE*[!(/q06L{(50i}0qxv(&+bYp3' );
define( 'NONCE_SALT',       'he]hAo]O.XSO}|mqhVqNYebKw)a@p-I@jTH,wm,`m|)}Rv~d@^uOltYWRBJQJP5B' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_jccib_';
define( 'WP_HOME', 'http://localhost:8080/websitejccib/' );
define( 'WP_SITEURL', 'http://localhost:8080/websitejccib/' );
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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
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
