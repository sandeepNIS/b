<?php

/**
 * @package           Wp_Stickit
 * @version           1.3.1
 * @link              https://profiles.wordpress.org/nazsabuz/
 *
 * Plugin Name:       WP Stickit
 * Plugin URI:        https://profiles.wordpress.org/nazsabuz/
 * Description:       WP Stickit makes header, sidebar, or anything sticky.
 * Version:           1.3.1
 * Author:            Nazmul Sabuz
 * Author URI:        https://profiles.wordpress.org/nazsabuz/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Including the core plugin functions.
 *
 * @since    1.0.0
 */
require_once dirname(__file__) . '/includes/wp_stickit_functions.php';

/**
 * Including the functions for admin menu page and options.
 *
 * @since    1.0.0
 */
if (is_admin()) {
	require_once dirname(__file__) . '/admin/wp_stickit_admin.php';
}
