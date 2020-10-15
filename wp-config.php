<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'cryptoo' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '5y6]J#]YWTZ0Yu~ (?b{ziq4-;X<]St{WT8-7vd<obzkFL (<Q+h@q79ve,xTR(i' );
define( 'SECURE_AUTH_KEY',  '^V-&Z0/oQj$hQ_#JHHx@ES;MlIw%d]&1n-,BlmFX-9q0R2rf#fT2L~m.]A<KNf%x' );
define( 'LOGGED_IN_KEY',    ':,Q]Hux!{!+{@;-,#)SB[&Os4A[6|voM!8f44<jbBO+?<.k}8ooB5HUnW&8!2pa4' );
define( 'NONCE_KEY',        '<i!v/,J,[~I*N/z,VT,9q`jSRWlFeZb!#kR&323`+Jl77b0rvhkL .hRk!MX#5tZ' );
define( 'AUTH_SALT',        'MZ<!s$2x1p/yao,i`P$wH-QZD$2vhodxu{V^vQmTU=.rS2!0WC[oDPG}&+.qwwA|' );
define( 'SECURE_AUTH_SALT', 'i`&5|<#J)S*L&X0dWosltPhMjb.EPQoC{L@+qSPo+_4Y( hKzjYSE<p1rG~[@~c9' );
define( 'LOGGED_IN_SALT',   ']}vo&=Xd<(QSr%OG^6*I&w]>.UL>8R?v`R7b| HGi3F2uZ-p}xve68gU)rsJpCux' );
define( 'NONCE_SALT',       '1W+e3&;D4X{P,LWtnjdDeCNBnvq*[EWM-P/6MA(tZ$b6TP<ULJ(,>Xhfbli|J@lT' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
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
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
