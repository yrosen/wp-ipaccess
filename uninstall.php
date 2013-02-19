<?php
/**
 * IPAccess Wordpress Plugin
 * Manages a list of IP addresses so that content can be shown only to allowed visitors.
 *
 * @author  Yudi Rosen <yudi42@gmail.com>
 * @license MIT - http://yrosen.mit-license.org/
 * @package IPAccess
 * @version 1.0.0
 */
 
 
// Wordpress docs say "emphasis is put on using the 'uninstall.php' 
// way of uninstalling the plugin rather than register_uninstall_hook."
// So...here we are.

if(WP_UNINSTALL_PLUGIN) {
	global $wpdb;

	$wpdb->query("DROP TABLE IF EXISTS `{$wpdb->prefix}ipaccess_orgs`;");
	$wpdb->query("DROP TABLE IF EXISTS `{$wpdb->prefix}ipaccess_ranges`;");	
}

?>