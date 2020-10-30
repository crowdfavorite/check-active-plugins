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
	if ( ! is_multisite() ) {
		echo '<p style="color:red;">This plugin must be used on a multisite.</p>';
		return;
	}
	
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
				$plugin_name                        = html_entity_decode( $plugin_data[ 'Name' ] );
				$all_active_plugins[ $plugin_name ] = $plugin_name;
			}
			echo '<li> - ' . ( ! empty( $plugin_data[ 'Name' ] ) ? $plugin_data[ 'Name' ] : $value ) . '</li>';
			
		}
		echo '</ul>';
	}
	
	/*
     * Network Activated Plugins
     */
	echo '<br /><hr /><br />';
	
	echo '<h3>All sites plugins that are active:</h3>';
	echo '<ul>';
	foreach ( $all_active_plugins as $key => $value ) {
		echo '<li> - ' . $value . '</li>';
	}
	echo '</ul>';
	
	
	/*
     * Network Activated Plugins
     */
	echo '<br /><hr /><br />';
	
	echo '<h3>ONLY NETWORK ACTIVATED</h3>';
	
	$network_plugins = get_site_option( 'active_sitewide_plugins' );
	if ( ! empty( $network_plugins ) ) {
		echo '<ul>';
		foreach ( $network_plugins as $key => $value ) {
			$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $key );
			if ( ! empty( $plugin_data ) ) {
				$plugin_name                        = html_entity_decode( $plugin_data[ 'Name' ] );
				$all_active_plugins[ $plugin_name ] = $plugin_name;
			}
			echo '<li> - ' . ( ! empty( $plugin_data[ 'Name' ] ) ? $plugin_data[ 'Name' ] : $value ) . '</li>';
		}
		echo '</ul>';
	}
	
	
	/*
     * Network Activated Plugins
     */
	echo '<br /><hr /><br />';
	
	echo '<h3>NETWORK INACTIVE</h3>';
	
	$all_plugins = get_plugins();
	
	echo '<ul>';
	$out = '';
	foreach ( $all_plugins as $plugin ) {
		$plugin_exists = ( $plugin[ 'Name' ] ? ( $all_active_plugins[ $plugin[ 'Name' ] ] ?? false ) : false );
		
		if ( empty( $plugin_exists ) ) {
			$out .= '<li> - ' . $plugin[ 'Name' ] . '</li>';
		}
		
	}
	echo $out;
	if ( empty( $out ) ) {
		echo '<li><strong>All installed plugins are activated networkly or to individual sites.</strong></li>';
	}
	echo '</ul>';
	
	
}
