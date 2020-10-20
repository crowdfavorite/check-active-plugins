<?php
/**
 * Plugin Name: CF Check Active Plugins
 * Plugin URI: https://www.crowdfavorite.com
 * Description: See a list of active plugins.
 * Version: 1.0
 * Author: CrowdFavorite
 * Author URI: https://www.crowdfavorite.com
 */


function cf_cap_add_settings_page()
{
	add_options_page(
		'Check Active Plugins',
		'Check Active Plugins',
		'manage_options',
		'cf-check-active-plugins',
		'cf_cap_render_plugin_settings_page'
	);
}

add_action( 'admin_menu', 'cf_cap_add_settings_page' );


function cf_cap_render_plugin_settings_page()
{
	echo "<h2>See active plugins</h2>";
	
	
	/*
	 * Iterate Through All Sites
	 */
	global $wpdb;
	
	$blogs = $wpdb->get_results( "
        SELECT blog_id
        FROM {$wpdb->blogs}
        WHERE site_id = '{$wpdb->siteid}'
        AND spam = '0'
        AND deleted = '0'
        AND archived = '0'
    " );
	
	$all_active_plugins = [];
	
	
	echo '<h3>ALL SITES</h3>';
	
	foreach ( $blogs as $blog ) {
		
		switch_to_blog( $blog->blog_id );
		$active = $wpdb->get_results( "
			SELECT *
			FROM {$wpdb->prefix}options
			WHERE option_name = 'active_plugins'
		" );
		
		foreach ( $active as $item ) {
			$plugins = unserialize( $item->option_value );
		}
		
		printf( '<hr /><h4><strong>SITE</strong>: <a href="%s" title="Go to the Dashboard for %s">%s</a></h4>',
			get_admin_url( $blog->blog_id ), get_blog_option( $blog->blog_id, 'blogname' ),
			get_blog_option( $blog->blog_id, 'blogname' ) );
		echo '<ul>';
		foreach ( $plugins as $key => $value ) {
			$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $value );
			
			if ( ! empty( $plugin_data[ 'Name' ] ) ) {
				$all_active_plugins[ $plugin_data[ 'Name' ] ] = $plugin_data[ 'Name' ];
			}
			
			$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $value );
			echo '<li> - ' . ( ! empty( $plugin_data[ 'Name' ] ) ? $plugin_data[ 'Name' ] : $value ) . '</li>';
			
		}
		echo '</ul>';
	}
	
	
	/*
     * Network Activated Plugins
     */
	echo '<br /><hr /><br />';
	
	echo '<h3>NETWORK ACTIVATED</h3>';
	echo '<p style="color:#000; font-weight:bold;">Cannot verify all plugins on local environments using git.</p>';
	echo '<ul>';
	foreach ( $all_active_plugins as $key => $value ) {
		$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $value );
		echo '<li> - ' . ( ! empty( $plugin_data[ 'Name' ] ) ? $plugin_data[ 'Name' ] : $value ) . '</li>';
	}
	echo '</ul>';
	
	/*
     * Network Activated Plugins
     */
	echo '<br /><hr /><br />';
	
	echo '<h3>NETWORK INACTIVE</h3>';
	echo '<p style="color:#000; font-weight:bold;">Cannot verify all plugins on local environments using git.</p>';
	
	$all_plugins = get_plugins();
	
	echo '<ul>';
	foreach ( $all_plugins as $plugin ) {
		$plugin_exists = ( $plugin[ 'Name' ] ? ( $all_active_plugins[ $plugin[ 'Name' ] ] ?? false ) : false );
		
		if ( empty( $plugin_exists ) ) {
			echo '<li> - ' . $plugin[ 'Name' ] . '</li>';
		}
		
	}
	echo '</ul>';
	
}
