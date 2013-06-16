<?php
/**
 * IPAccess Wordpress Plugin
 * Manages a list of IP addresses so that content can be shown only to allowed visitors.
 *
 * @author  Yudi Rosen <yudi42@gmail.com>
 * @license MIT - http://yrosen.mit-license.org/
 * @package IPAccess
 * @version 1.1.0
 */

// First thing we'll do: Create the top-level menu:
add_action(
	'admin_menu', 

	function () {
		add_menu_page(
			'IPAccess',                       // Page title
			'IPAccess',                       // Menu item title
			'manage_options',                 // Required Capability
			'ipaccess-admin',                 // Menu item slug

			function () {
				// Show the correct admin panel page.
				switch($_GET['action']) {
					case 'add-org':
						// Add Organization:
						require_once(IPACCESS_BASEDIR . 'admin/ipaccess-admin-add-org.php');
						break;

					case 'edit-org':
						// Edit Organization:
						require_once(IPACCESS_BASEDIR . 'admin/ipaccess-admin-edit-orgs.php');
						break;

					case 'add-range':
						// Add Range:
						require_once(IPACCESS_BASEDIR . 'admin/ipaccess-admin-add-range.php');
						break;

					case 'edit-range':
						// Edit Range:
						require_once(IPACCESS_BASEDIR . 'admin/ipaccess-admin-edit-ranges.php');
						break;

					case 'delete-org':
					case 'delete-range':
						// Delete things:
						require_once(IPACCESS_BASEDIR . 'admin/ipaccess-admin-delete.php');
						break;

					default:
						// Organizations Overview
						require_once(IPACCESS_BASEDIR . 'admin/ipaccess-admin-orgs.php');
						break;
				}
			}
		);
	}
);

// Also, we'll want to use our CSS file:
add_action(
	'admin_print_styles',
	
	function () {
		wp_enqueue_style(
			'wpaccess-css',	 // Handle
			plugins_url('/assets/ipaccess.css', __FILE__),	// CSS file
			false,	         // No dependancies
			'1.0.0'	         // Version (keep this for caching reasons)
		);
	}
);

// Also, jQuery UI:
add_action(
	'admin_init', 
	
	function () {
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-datepicker', plugins_url('/ipaccess/assets/ui/minified/jquery.ui.datepicker.min.js'), array('jquery', 'jquery-ui-core') );
		wp_enqueue_style('jquery-ui-theme', plugins_url('/ipaccess/assets/ui/themes/smoothness/jquery-ui.css'));
	}
);
?>