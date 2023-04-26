<?php
/*
Plugin Name: TablePress Extension: DataTables Column Filter
Plugin URI: https://tablepress.org/extensions/datatables-column-filter/
Description: Custom Extension for TablePress to add the DataTables Column Filter plugin
Version: 1.1
Author: Tobias Bäthge
Author URI: https://tobias.baethge.com/
*/

/*
 * Shortcode:
 * [table id=123 datatables_columnfilter=true /]
 *
 * "Custom CSS" to make the text fields fit:
 * .tablepress tfoot th .filter_column {
 *     width: 100%;
 *     -moz-box-sizing: border-box;
 *     -webkit-box-sizing: border-box;
 *     box-sizing: border-box;
 * }
 *
 * See http://jquery-datatables-column-filter.googlecode.com/svn/trunk/default.html
 */

/*
 * Register necessary Plugin Filters.
 */
add_filter( 'tablepress_shortcode_table_default_shortcode_atts', 'tablepress_add_shortcode_parameters_columnfilter' );
add_filter( 'tablepress_table_js_options', 'tablepress_add_columnfilter_js_options', 10, 3 );
add_filter( 'tablepress_datatables_command', 'tablepress_add_columnfilter_js_command', 10, 5 );

/**
 * Add "datatables_columnfilter" as a valid parameter to the [table /] Shortcode.
 *
 * @since 1.0
 *
 * @param array $default_atts Default attributes for the TablePress [table /] Shortcode.
 * @return array Extended attributes for the Shortcode.
 */
function tablepress_add_shortcode_parameters_columnfilter( $default_atts ) {
	$default_atts['datatables_columnfilter'] = '';
	return $default_atts;
}

/**
 * Pass "datatables_columnfilter" from Shortcode parameters to JavaScript arguments.
 *
 * @since 1.0
 *
 * @param array  $js_options    Current JS options.
 * @param string $table_id      Table ID.
 * @param array $render_options Render Options.
 * @return array Modified JS options.
 */
function tablepress_add_columnfilter_js_options( $js_options, $table_id, $render_options ) {
	$js_options['datatables_columnfilter'] = $render_options['datatables_columnfilter'];

	// Register the JS.
	if ( '' !== $js_options['datatables_columnfilter'] ) {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		$js_columnfilter_url = plugins_url( "columnfilter{$suffix}.js", __FILE__ );
		wp_enqueue_script( 'tablepress-columnfilter', $js_columnfilter_url, array( 'tablepress-datatables' ), '1.5.6', true );
	}

	return $js_options;
}

/**
 * Evaluate "datatables_columnfilter" parameter and add corresponding JavaScript code, if needed.
 *
 * @since 1.0
 *
 * @param string $command    DataTables command.
 * @param string $html_id    HTML ID of the table.
 * @param array  $parameters DataTables parameters.
 * @param string $table_id   Table ID.
 * @param array  $js_options DataTables JS options.
 * @return string Modified DataTables command.
 */
function tablepress_add_columnfilter_js_command( $command, $html_id, $parameters, $table_id, $js_options ) {
	if ( empty( $js_options['datatables_columnfilter'] ) ) {
		return $command;
	}

	// Get Columnfilter parameters from Shortcode attribute, except if it's just set to "true".
	$columnfilter_parameters = '';
	if ( true !== $js_options['datatables_columnfilter'] ) {
		$columnfilter_parameters = $js_options['datatables_columnfilter'];
	}

	$command = "$('#{$html_id}').dataTable({$parameters}).columnFilter({$columnfilter_parameters});";
	return $command;
}
