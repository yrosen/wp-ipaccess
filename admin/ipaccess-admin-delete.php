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

if(!is_admin()) {
	die();
}

global $wpdb;

// This is the default direct-to once we're done with whatever we're doing here:
$redirect_to = './admin.php?page=ipaccess-admin';

// Delete an organization and all of its' IP ranges:
if($_GET['action'] == 'delete-org' && $_GET['org-id']) {
	$s_org_id = intval($_GET['org-id']);

	// Delete the organization
	$wpdb->query("DELETE FROM `{$wpdb->prefix}ipaccess_orgs` WHERE id={$s_org_id} LIMIT 1");

	// Delete all its IP ranges
	// TODO: I think we should use foreign keys -YR
	$wpdb->query("DELETE FROM `{$wpdb->prefix}ipaccess_ranges` WHERE org_id={$s_org_id}");
}

// Delete an IP range:
elseif($_GET['action'] == 'delete-range' && $_GET['range-id']) {
	$s_range_id = $wpdb->escape($_GET['range-id']);

	// First, figure out what org it belongs to:
	$org_id = $wpdb->get_var("SELECT org_id FROM `{$wpdb->prefix}ipaccess_ranges` WHERE id={$s_range_id} LIMIT 1");

	if($org_id) {
		// Delete the specified IP range
		$wpdb->query("DELETE FROM `{$wpdb->prefix}ipaccess_ranges` WHERE id={$s_range_id} LIMIT 1");
	
		IPAccess::recalculateOrgIPCount($org_id);

		$redirect_to = './admin.php?page=ipaccess-admin&action=edit-org&org-id=' . $org_id;
	}
}

// Get out of here:
IPAccess::doRedirect($redirect_to);
?>