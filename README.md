IPACCESS
--------------------------------
IPAccess is a plugin that lets you control access to your WordPress site via IP
address. It's perfect for setting up digital subscriptions, allowing access
only to your friends or company, or setting up "paywalls" for your content.

IPAccess was developed on, and only tested against, WordPress 3.5.1. All other
versions may or may not work - use at your own risk.


BACKGROUND
--------------------------------
A site I was working on wanted to sell digital subscriptions to various 
libraries, universities, and other organizations. This plugin handled that - it
can manage the various organizations that required access, as well as the IP 
addresses (or IP ranges) for each organization.

The version I wrote for the site has a lot more features (expiration dates, 
redis backend, better security, and a lot more) but I re-wrote this for simplicity 
and to ensure maximum support with various webhosts.


LICENSE
--------------------------------
This plugin is distributed under the MIT license. Please see http://yrosen.mit-license.org/ 
or the included LICENSE file for the full text of the license.


HOW TO USE
--------------------------------
Very easy. In your template cod:e

    if(IPAccess::isAllowed()) {
         // This is one of your members, so locked content here
    }
    else {
    	// Public content/sign up box/whatever goes here
    }

Another possible way of using it is to write your own Shortcode (http://codex.wordpress.org/Shortcode_API).

This could be something like:

    add_shortcode('ipaccess',
    	function($atts) {
    		if(IPAccess:isAllowed()) {
    			// Allowed, show content or whatever
    		}
    		else {
    			// Not allowed...perhaps cut content here and show sign-up box
    		}
    	}
    );

And in your post you'd simply put `[ipaccess]` to signify locked content.

The administrative panels should be rather easy to understand.


INSTALLATION
--------------------------------
Simply upload the 'ipaccess' directory to your WordPress plugins folder ('wp-content/plugins/'), 
and click "Activate" under "IPAccess" in your WordPress admin panel Plugins page.


CREDITS
--------------------------------
This plugin is written by Yudi Rosen (yudi42@gmail.com, @yudism, github.com/yrosen).

Uses icons from the Silk icon set by famfamfam - http://www.famfamfam.com/lab/icons/silk/


BUG REPORTS, FEATURE REQUESTS, ETC
--------------------------------
https://github.com/yrosen/wp-ipaccess
