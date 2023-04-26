<?php

/**
 * Create options page.
 *
 * @since    1.0.0
 */
function wp_stickit_menu_options() {
	if (!current_user_can('manage_options')) {
		wp_die(__('You do not have sufficient permissions to access this page.'));
	}?>

	<div class="wrap">
		<h2><?php _e('WP Stickit Settings', 'menu-test')?></h2>

		<form method="post" action="options.php">
			<?php settings_fields('wp-stickit-option-group');?>
			<?php do_settings_sections('wp_stickit');?>

			<table class="form-table">
				<tr valign="top">
					<th scope="row">Class Name</th>
					<td><input type="text" name="wp_stickit_class_name" value="<?php echo esc_attr(get_option('wp_stickit_class_name', '.sidebar')); ?>" class="regular-text" />
					<p class="description">Sets the class name of the widget that will be sticky.</p></td>
				</tr>

				<tr valign="top">
					<th scope="row">Top</th>
					<td><input type="number" step="1" min="0" name="wp_stickit_top" value="<?php echo esc_attr(get_option('wp_stickit_top', 0)); ?>" class="medium-text" />
					<p class="description">Sets sticky top, eg. it will be stuck at position top 50 if you set 50.</p></td>
				</tr>

				<tr valign="top">
					<th scope="row">Z-Index</th>
					<td><input type="number" step="1" min="0" name="wp_stickit_zindex" value="<?php echo esc_attr(get_option('wp_stickit_zindex', 100)); ?>" class="medium-text" />
					<p class="description">Sets z-index. Default is try to get element z-index property from css style. If undefined, default is 100.</p></td>
				</tr>

				<tr valign="top">
					<th scope="row">Screen Min Width</th>
					<td><input type="number" step="1" min="1" name="wp_stickit_screen_min_width" value="<?php echo esc_attr(get_option('wp_stickit_screen_min_width', 1280)); ?>" class="medium-text" />
					<p class="description">Sets min width for RWD. This is equal to min-width in media query.</p></td>
				</tr>

				<tr valign="top">
					<th scope="row">Screen Max Width</th>
					<td><input type="number" step="1" min="1" name="wp_stickit_screen_max_width" value="<?php echo esc_attr(get_option('wp_stickit_screen_max_width', 1920)); ?>" class="medium-text" />
					<p class="description">Sets max width for RWD. This is equal to max-width in media query.</p></td>
				</tr>
			</table>

			<?php submit_button();?>

		</form>
	</div>
<?php }

/**
 * Register options field.
 *
 * @since    1.0.0
 */
function register_wp_stickit_settings() {
	register_setting('wp-stickit-option-group', 'wp_stickit_class_name');
	register_setting('wp-stickit-option-group', 'wp_stickit_top');
	register_setting('wp-stickit-option-group', 'wp_stickit_zindex');
	register_setting('wp-stickit-option-group', 'wp_stickit_screen_min_width');
	register_setting('wp-stickit-option-group', 'wp_stickit_screen_max_width');
}

/**
 * Register option page.
 *
 * @since    1.0.0
 */
function wp_stickit_menu() {
	add_options_page('WP Stickit Settings', 'WP Stickit', 'manage_options', 'wp_stickit', 'wp_stickit_menu_options');
	add_action('admin_init', 'register_wp_stickit_settings');
}
add_action('admin_menu', 'wp_stickit_menu');