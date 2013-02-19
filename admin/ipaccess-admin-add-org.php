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

$error = '';

// Sanitize stuff:
$org = array(
	'name'          => htmlentities($_POST['ipaccess-org-name'],      ENT_QUOTES),
	'contact-name'  => htmlentities($_POST['ipaccess-contact-name'],  ENT_QUOTES),
	'contact-email' => htmlentities($_POST['ipaccess-contact-email'], ENT_QUOTES),
);

// Submit the form?
if($_POST['submit']) {
	if(empty($org['name'])) {
		$error = '<div class="error">Organization Name is required!</div>';
	}
	elseif(!wp_verify_nonce($_POST['ipaccess_nonce'], 'ipaccess-admin-add-org')) {
		// Possible CSRF?
		$error = '<div class="error">An error occured. Please try again.</div>';
	}
	else {
		// Check to see if NAME already exists:
		$wpdb->get_results($wpdb->prepare("SELECT `name` FROM `{$wpdb->prefix}ipaccess_orgs` WHERE name=%s", $org['name']));

		if($wpdb->num_rows > 0) {
			$error = '<div class="error">Organization Name already exists!</div>';
		}
		else {
			// Add the org!
			$wpdb->insert(
				// Table
				$wpdb->prefix . 'ipaccess_orgs',

				// Data
				array(
					'name'          => $org['name'],
					'contact_name'  => $org['contact-name'],
					'contact_email' => $org['contact-email'],
				)
			);

			if($wpdb->insert_id) {
				// Re-direct to the Edit page for this brand-new organization:
				IPAccess::doRedirect('./admin.php?page=ipaccess-admin&action=edit-org&org-id=' . $wpdb->insert_id);
			}
			else {
				// Something went terribly wrong...
				$error = '<div class="error">An error occured. Please try again.</div>';
			}
		}
	}
}
?>

<div class="ipaccess wrap">
	<div id="icon-ms-admin" class="icon32"></div>
	<h2>IPAccess &raquo; Organizations &raquo; Add</h2>

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
				
				<li>
					<em>You'll be able to add IP ranges once you save this organization.</em>
				</li>
			</ul>

			<?php wp_nonce_field('ipaccess-admin-add-org', 'ipaccess_nonce'); ?>

			<br />

			<input class="button-primary" type="submit" name="submit" value="Submit" />
		</form>
	</div>
</div>