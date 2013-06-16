<?php
/**
 * IPAccess Wordpress Plugin
 * Manages a list of IPv4 addresses so that content can be shown only to allowed visitors.
 *
 * @author  Yudi Rosen <yudi42@gmail.com>
 * @license MIT - http://yrosen.mit-license.org/
 * @package IPAccess
 * @version 1.0.0
 */

/*
Plugin Name: IPAccess
Description: Manages a list of IPv4 addresses so that content can be shown only to allowed visitors.
Author: Yudi Rosen
Version: 1.0.0
Author URI: http://yudirosen.com
*/

// Figure out where we are, since WP keeps messing this up:
define('IPACCESS_BASEDIR', plugin_dir_path(__FILE__));

if(is_admin()) {
	// If we're in the admin panel, load up the admin stuff:
	require_once(IPACCESS_BASEDIR . 'ipaccess-admin.php');

	// Need this for installation
	require_once(IPACCESS_BASEDIR . 'install.php');
}

// Commonly-used & some non-admin functions:
class IPAccess {
	private static $db;
	private static $cookie = 'wpipaccess'; // Cookie name
	
	/**
	 * The alternative to this #*!*$( is to have a million 
	 * 'global $wpdb' statements...
	 */
	public static function init() {
		global $wpdb;

		self::$db = &$wpdb;
	}
	/**
	 * Checks to see if visitor is allowed or not
	 *
	 * Use this in your template to check if a user is in the list of
	 * allowed IP addresses. Returns TRUE or FALSE.
	 *
	 * @access public
	 * @return bool TRUE or FALSE based on if user is allowed or not
	 */
	public static function isAllowed() {
		// First, check cookie. If set & valid, return it.
		if(self::isCookieValid()) {
			return true;
		}

		// Cookie isn't valid, so let's check database:
		$ip = self::dottedToNumeric(self::getIP());

		$ranges = self::$db->get_results('
			SELECT r.*, o.expires_on FROM ' . self::$db->prefix . 'ipaccess_ranges AS r ' . 
			'JOIN ' . self::$db->prefix . 'ipaccess_orgs AS o ON (o.id = r.org_id) ' . 
			"WHERE {$ip} BETWEEN `start` AND `end`"
		);

		if($ranges) {
			// Anything expired?
			foreach($ranges as $range) {
				if($range->expires_on && $range->expires_on < time()) {
					return false;
				}
			}

			// Nothing expired, so...
			self::setCookie(self::getIP());
			return true;
		}

		// No cake for you
		return false;
	}

	/**
	 * Redirects to the desired URL by using headers if available and JS just in case
	 *
	 * @access public
	 * @param  string $url The URL you want to redirect to.
	 */
	public static function doRedirect($url) {
		if(!headers_sent()) {
			wp_redirect($url);
		}

		echo "
			<script type=\"text/javascript\">
				window.location.href='{$url}';
			</script>

			Redirecting....<br />
			If your browser does not automatically redirect, please click <a href=\"{$url}\">here</a>.
		";

		die();
	}

	/**
	 * Returns the user's IP address
	 *
	 * @access public
	 * @return User's IP address
	 */
	public static function getIP() {
		/*
		// Shared client?
		if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
			return $_SERVER['HTTP_CLIENT_IP'];
		}

		// Proxy?
		if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		*/

		// Plain old IP address:
		return $_SERVER['REMOTE_ADDR'];
	}
	
	/**
	 * Converts dotted IP address to numeric using MySQL
	 *
	 * PHP and MySQL can give different results depending on arch, 
	 * so we're always using the DB for converting.
	 *
	 * @access public
	 * @param  mixed $ip IP address to convert
	 * @return numeric value of IP address
	 */
	public static function dottedToNumeric($ip) {
		$s_ip = self::$db->escape($ip);

		return self::$db->get_var("SELECT INET_ATON('{$s_ip}')");
	}
	
	/**
	 * Converts numeric IP to dotted IP address using MySQL
	 *
	 * PHP and MySQL can give different results depending on arch, 
	 * so we're always using the DB for converting.
	 *
	 * @access public
	 * @param  mixed $ip Numeric value of IP address
	 * @return IP address
	 */
	public static function numericToDotted($numericIP) {
		$s_ip = self::$db->escape($numericIP);

		return self::$db->get_var("SELECT INET_NTOA('{$s_ip}')");
	}
	
	/**
	 * Re-calculates the total number of IPs in all of a specified
	 * organization's ranges.
	 *
	 * @access public
	 * @param int $orgID Organization ID
	 * @return Total count
	 */
	public static function recalculateOrgIPCount($orgID) {
		$orgID = intval($orgID);

		// Get total count:
		$count = self::$db->get_var('SELECT SUM(number_of_ips) FROM ' . self::$db->prefix . "ipaccess_ranges WHERE org_id={$orgID}");
		
		// Ensure is numeric:
		$count = $count ? $count : 0;

		self::$db->query('UPDATE ' . self::$db->prefix . "ipaccess_orgs SET number_of_ips={$count} WHERE id={$orgID}");
		
		return $count;
	}

	/**
	 * Set a cookie to expire whenever that flags this IP as allowed
	 *
	 * @access private
	 * @param  mixed $ip  The IP address of our visitor
	 * @param  int   $ttl Time To Live for the cookie (0 (default) or timestamp)
	 */
	private static function setCookie($ip, int $ttl = NULL) {
		// TODO: Security?
		// TTL: 0 = until the end of the session, anything else is the unix timestamp
		$ttl = $ttl ? current_time('timestamp') + intval($ttl) : 0;
		
		setcookie(self::$cookie, self::getCookieValue($ip), $ttl);

	}

	/**
	 * Checks if there's a cookie set that allows the current IP address access
	 *
	 * @access private
	 * @return bool TRUE or FALSE
	 */
	private static function isCookieValid() {
		return $_COOKIE[self::$cookie] ==/*=*/ self::getCookieValue(self::getIP());
	}
	
	/**
	 * Generates the value for the IP's cookie
	 *
	 * @access private
	 * @param mixed $ip The IP address for the cookie
	 * @return string Hash to use as cookie data
	 */
	private static function getCookieValue($ip) {
		if(!defined('AUTH_KEY')) {
			// What kind of old WP are you running???
			return sha1($ip);
		}
		
		//return wp_hash_password($ip . AUTH_KEY);
		return sha1($ip . AUTH_KEY);
	}
}

IPAccess::init();
?>