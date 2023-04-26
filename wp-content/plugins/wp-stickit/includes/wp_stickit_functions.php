<?php

/**
 * Load the required js files for this plugin.
 *
 * @since    1.0.0
 */
function wp_stickit_enqueue_script() {
	wp_enqueue_script('jquery');
	wp_enqueue_script('wp_stickit', plugin_dir_url(__FILE__) . '../js/jquery.stickit.min.js');
}
add_action('wp_enqueue_scripts', 'wp_stickit_enqueue_script');

/**
 * Load plugin options based on user specification.
 *
 * @since    1.0.0
 */
function wp_stickit_trigger_settings() {?>
	<script type="text/javascript">
		jQuery('<?php echo get_option('wp_stickit_class_name', '.sidebar'); ?>').stickit({
			top: <?php echo get_option('wp_stickit_top', 0); ?>,
			zIndex: <?php echo get_option('wp_stickit_zindex', 100); ?>,
			screenMinWidth: <?php echo get_option('wp_stickit_screen_min_width', 1280); ?>,
			screenMaxWidth: <?php echo get_option('wp_stickit_screen_max_width', 1920); ?>,
		});
	</script>
<?php }
add_action('wp_footer', 'wp_stickit_trigger_settings');
