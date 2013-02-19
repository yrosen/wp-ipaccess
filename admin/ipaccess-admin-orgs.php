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

// First, get our orgs from the database:
global $wpdb;

$content = '';

$orgs    = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}ipaccess_orgs`", ARRAY_A);


if(!$orgs) {
	$content = '<tr><td colspan="6" style="text-align:center;font-style:italic">No organizations found. <a href="./admin.php?page=ipaccess-admin&action=add-org">Add one?</a></td></tr>';
}
else {
	foreach($orgs as $org) {
		// Sanitize any HTML:
		$org = array_map(function($a) { return htmlentities($a, ENT_QUOTES); }, $org);

		// Make the # of IPs all pretty:
		$org['number_of_ips'] = number_format_i18n($org['number_of_ips']);

		$content .= "
			<tr>
				<td>{$org['id']}</td>
				<td>{$org['name']}</td>
				<td>{$org['contact_name']}</td>
				<td>{$org['contact_email']}</td>
				<td>{$org['number_of_ips']}</td> 
				<td>
					<a class=\"icon edit\" href=\"./admin.php?page=ipaccess-admin&amp;action=edit-org&amp;org-id={$org['id']}\" title=\"Edit\">Edit</a> 
					<a class=\"icon delete\" href=\"./admin.php?page=ipaccess-admin&amp;action=delete-org&amp;org-id={$org['id']}\" title=\"Delete\" onclick=\"return confirm('Are you sure you want to delete this organization? This can not be un-done.');\">Delete</a>
				</td>
			</tr>
		";
	}
}
?>

<div class="ipaccess wrap">
	<div id="icon-ms-admin" class="icon32"></div>
	<h2>IPAccess &raquo; Organizations<a class="add-new-h2" href="./admin.php?page=ipaccess-admin&action=add-org">Add New</a></h2>

	<table class="widefat">
		<thead>
			<tr>
				<th>ID</th>
				<th>Org. Name</th>
				<th>Contact Name</th>
				<th>Contact E-Mail</th>
				<th>Number of IPs</th>
				<th>Options</th>
			</tr>
		</thead>
		
		<tfoot>
			<tr>
				<th>ID</th>
				<th>Org. Name</th>
				<th>Contact Name</th>
				<th>Contact E-Mail</th>
				<th>Number of IPs</th>
				<th>Options</th>
			</tr>
		</tfoot>

		<tbody>
			<?php echo $content; ?>
		</tbody>
	</table>

	<p>
		<em><strong>Your IP:</strong> <?php echo IPAccess::getIP(); ?></em>
	</p>

</div>