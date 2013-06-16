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

if(!is_admin()) {
	die();
}

global $wpdb;

$error = '';

if(!$_GET['range-id']) {
	// Gotta specify a range and ID...
	IPAccess::doRedirect('./admin.php?page=ipaccess-admin');
}

// Get the range info:
$s_range_id = intval($_GET['range-id']);

$curr_range = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}ipaccess_ranges` WHERE id={$s_range_id}", ARRAY_A);

// Are we submitting the form?
if($_POST['submit']) {
	if(!wp_verify_nonce($_POST['ipaccess_nonce'], 'ipaccess-admin-edit-range')) {
		// Possible CSRF?
		$error = '<div class="error">An error occured. Please try again.</div>';
	}
	elseif(empty($_POST['ipaccess-range-start'])) {
		$error = '<div class="error">Start IP Address is required!</div>';
	}
	else {
		// Start and end must be valid IP addresses
		if(!filter_var($_POST['ipaccess-range-start'], FILTER_VALIDATE_IP)) {
			$error = '<div class="error">Start IP is not a valid IP address!</div>';
		}
		// Don't forget - range-end is optional
		elseif(!empty($_POST['ipaccess-range-end']) && !filter_var($_POST['ipaccess-range-end'], FILTER_VALIDATE_IP)) {
			$error = '<div class="error">End IP is not a valid IP address!</div>';
		}
		else {
			// If there's no end, or if start==end, then it's a single. Else, range
			$type = empty($_POST['ipaccess-range-end']) || ($_POST['ipaccess-range-start'] == $_POST['ipaccess-range-end']) ? 'single' : 'range';

			$start_ip = IPAccess::dottedToNumeric($_POST['ipaccess-range-start']);
			// If it's single, the end should be the same as beginning:
			$end_ip   = IPAccess::dottedToNumeric($type === 'single' ? $_POST['ipaccess-range-start'] : $_POST['ipaccess-range-end']);

			// Range: start must be before range
			if($type == 'range' && $start_ip > $end_ip) {
				$error = '<div class="error">Error: Start IP must come before End IP!</div>';
			}
			else {
				// TODO: Error checking: is this already existing? Is it within/overlapping another range?

				// Count how many IPs we have:
				$number_of_ips = $type == 'single' ? 1 : bcadd(bcsub($end_ip, $start_ip), 1);

				// Update DB:
				$update = $wpdb->update(
					$wpdb->prefix . 'ipaccess_ranges',
					
					array(
						//'org_id'        => $curr_range['org_id'],
						'type'          => $type,
						'start'         => $start_ip,
						'end'           => $end_ip,
						'number_of_ips' => $number_of_ips,
					),
					
					array(
						'id'            => $curr_range['id']
					)
				);

				if($update !== false) {
					IPAccess::recalculateOrgIPCount($curr_range['org_id']);
					IPAccess::doRedirect('./admin.php?page=ipaccess-admin&action=edit-org&org-id=' . intval($curr_range['org_id']));
				}
				else {
					$error = '<div class="error">An error occured. Please try again.</div>';
				}
			}
		}
	}
}
?>

<div class="ipaccess wrap">
	<div id="icon-ms-admin" class="icon32"></div>
	<h2>IPAccess &raquo; Organizations &raquo; Edit &raquo; Edit IP Address/Range</h2>

	<?php echo $error; ?>
	
	<div id="post-body">	
		<form method="post" action="#">
			<ul>
				<li style="margin-bottom:20px;">
					<label for="ipaccess-range-start"><strong>Start IP Address: </strong></label>
					<input id="ipaccess-range-start" name="ipaccess-range-start" type="text" value="<?php echo IPAccess::numericToDotted($curr_range['start']); ?>" maxlength="255" size="50" />
				</li>
				<li>
					<label for="ipaccess-range-end">End IP Address: <br /><small>Only required for IP ranges</small></label>
					<input id="ipaccess-range-end" name="ipaccess-range-end" type="text" value="<?php echo IPAccess::numericToDotted($curr_range['end']); ?>" maxlength="255" size="50" />
				</li>
			</ul>
			
			<?php wp_nonce_field('ipaccess-admin-edit-range', 'ipaccess_nonce'); ?>
			
			<input class="button-primary" type="submit" name="submit" value="Submit" /> 
			<a class="button-secondary" href="./admin.php?page=ipaccess-admin&action=edit-org&org-id=<?php echo $curr_range['org_id']; ?>">Cancel</a>
		</form>
	</div>
</div>