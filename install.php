<?php
/**
 * IPAccess Wordpress Plugin
 * Manages a list of IP addresses so that content can be shown only to allowed visitors.
 *
 * TODO: FOREIGN KEYS!
 *
 * @author  Yudi Rosen <yudi42@gmail.com>
 * @license MIT - http://yrosen.mit-license.org/
 * @package IPAccess
 * @version 1.1.0
 */

register_activation_hook(
	IPACCESS_BASEDIR . 'ipaccess.php', 

	function () {
		global $wpdb;

		// Create the Orgs table:
		$wpdb->query("
                CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}ipaccess_orgs` (
                        `id` int(8) NOT NULL AUTO_INCREMENT,
                        `added_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        `name` varchar(255) NOT NULL,
                        `contact_name` varchar(255) DEFAULT NULL,
                        `contact_email` varchar(255) DEFAULT NULL,
                        `number_of_ips` BIGINT NULL DEFAULT NULL,
                        `expires_on` int(16) NULL DEFAULT NULL,
                        PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
                ");


		// Create the Ranges table:
		$wpdb->query("
		CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}ipaccess_ranges` (
			`id` int(8) NOT NULL AUTO_INCREMENT,
			`org_id` int(8) DEFAULT NULL,
			`type` varchar(6) NOT NULL,
			`start` int(10) unsigned NOT NULL,
			`end` int(10) unsigned DEFAULT NULL,
			/**
			 * Small rant here: The max size of MySQL's unsigned int is 4294967295.
			 * This is great, until you realize that the max number of IPv4 addresses
			 * is 2^32 (4294967296) - ONE more than unsigned int can hold.
			 * So we'll have to move up to bigint which is massive, in order to fix one
			 * rather unlikely edge case.
			 *
			 * Oh, and don't get me started on PHP's (32bit) int. There's a reason why we're using 
			 * MySQL for ip->numeric conversion, and bcmath for calculating range size.
			 */
			`number_of_ips` BIGINT DEFAULT NULL,   
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
	}
);
?>
