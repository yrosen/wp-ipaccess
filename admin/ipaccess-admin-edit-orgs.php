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

$error = $ipranges = '';

if($_GET['org-id']) {
	// Are we submitting the form?
	if($_POST['submit']) {
		if(!wp_verify_nonce($_POST['ipaccess_nonce'], 'ipaccess-admin-edit-org')) {
			// Possible CSRF?
			$error = '<div class="error">An error occured. Please try again.</div>';
		}
		elseif(empty($_POST['ipaccess-org-name'])) {
			$error = '<div class="error">Organization Name is required!</div>';
		}
		else {
			$wpdb->update(
				// Table
				$wpdb->prefix . 'ipaccess_orgs',

				// Data
				array(
					'name'          => $_POST['ipaccess-org-name'],
					'contact_name'  => $_POST['ipaccess-contact-name'],
					'contact_email' => $_POST['ipaccess-contact-email'],
				),

				// Where:
				array(
					'id'			   => $_POST['org-id'],
				)
			);

			// On success:
			$error = '<div class="success">Organization details have been saved!</div>';
		}
	}

	// Get the org from the database...
	$s_id = $wpdb->escape($_GET['org-id']);
	$org  = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}ipaccess_orgs` WHERE `id`={$s_id}", ARRAY_A);

	if(empty($org)) {
		// Organization not found:
		IPAccess::doRedirect('./admin.php?page=ipaccess-admin');
	}
	else {
		$org['name']          = htmlentities($org['name'],          ENT_QUOTES);
		$org['contact-name']  = htmlentities($org['contact_name'],  ENT_QUOTES);
		$org['contact-email'] = htmlentities($org['contact_email'], ENT_QUOTES);
		
		$ranges = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}ipaccess_ranges` WHERE `org_id`={$s_id}", ARRAY_A);
		
		if(count($ranges)) {
			foreach($ranges as $range) {
				$ipranges .= '
					<tr>
						<td>' . ucwords($range['type']) . '</td>
						<td>' . IPAccess::numericToDotted($range['start'])   . '</td>
						<td>' . IPAccess::numericToDotted($range['end'])     . '</td>
						<td>' . number_format_i18n($range['number_of_ips']) . '</td> 
						<td>
							<a class="icon edit" href="./admin.php?page=ipaccess-admin&amp;action=edit-range&amp;range-id=' . $range['id'] . '" title="Edit">Edit</a>
							<a class="icon delete" href="./admin.php?page=ipaccess-admin&amp;action=delete-range&amp;range-id=' . $range['id'] . '" title="Delete" onclick="return confirm(\'Are you sure you want to delete this range? This can not be un-done.\');">Delete</a>
						</td>
					</tr>
				';
			}
		}
		else {
			$ipranges = '<tr><td colspan="5" style="text-align:center;font-style:italic">No IP ranges found for this organization. <a href="./admin.php?page=ipaccess-admin&amp;action=add-range&amp;org-id=' . intval($_GET['org-id']) . '">Add one?</a></td></tr>';
		}
	}
}
else {
	// Gotta specify an organization ID...
	IPAccess::doRedirect('./admin.php?page=ipaccess-admin');
}
?>

<div class="ipaccess wrap">
	<div id="icon-ms-admin" class="icon32"></div>
	<h2>IPAccess &raquo; Organizations &raquo; Edit <a class="add-new-h2" href="./admin.php?page=ipaccess-admin">&laquo; Back</a></h2>

	<?php echo $error; ?>

	<div id="post-body">	
		<form method="post" action="#">
			<ul>
				<li>
					<label for="ipaccess-org-name"><strong>Organization Name: </strong></label>
					<input id="ipaccess-org-name" name="ipaccess-org-name" type="text" value="<?php echo $org['name']; ?>" maxlength="255" size="50" />
				</li>
				<li>
					<label for="ipaccess-contact-name">Contact Name: </label>
					<input id="ipaccess-contact-name" name="ipaccess-contact-name" type="text" value="<?php echo $org['contact-name']; ?>" maxlength="255" size="50" />
				</li>
				
				<li>
					<label for="ipaccess-contact-email">Contact Email: </label>
					<input id="ipaccess-contact-email" name="ipaccess-contact-email" type="text" value="<?php echo $org['contact-email']; ?>" maxlength="255" size="50" />
				</li>
			</ul>
			
			<input class="button-primary" type="submit" name="submit" value="Submit" />
			
			<table class="widefat" style="margin-top:10px;">
				<thead>
					<tr>
						<th>Type</th>
						<th>Start</th>
						<th>End</th>
						<th>Number of IPs</th>
						<th>Options</th>
					</tr>
				</thead>
		
				<tfoot>
					<tr>
						<th>Type</th>
						<th>Start</th>
						<th>End</th>
						<th>Number of IPs</th>
						<th>Options</th>
					</tr>
				</tfoot>

				<tbody>
					<?php echo $ipranges; ?>
				</tbody>
			</table>

			<br />
			<a class="button-secondary" href="./admin.php?page=ipaccess-admin&amp;action=add-range&amp;org-id=<?php echo intval($_GET['org-id']); ?>">Add IP Range</a><br /><br />
			
			<input type="hidden" name="org-id" value="<?php echo intval($_GET['org-id']); ?>" />
			<?php wp_nonce_field('ipaccess-admin-edit-org', 'ipaccess_nonce'); ?>
		</form>
	</div>
</div>