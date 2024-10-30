<?php
/*
Plugin Name: ColorWP Twitter Widget
Plugin URI: http://colorwp.com/plugin/twitter-widget/
Description: Adds a Twitter widget to be used in your theme sidebar. Customizable number of latest tweets and an optional follow button.
Version: 1.1
Author: ColorWP.com
Author URI: http://colorwp.com
License: GNU GPLv2
*/

/*  Copyright 2012 ColorWP.com Twitter Widget Plugin (email : contact@colorwp.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class cwp_twitter_widget_plugin{
	
	// The textdomain to load for localization
	static public $textdomain = 'cwp-twitter-widget';
	
	static public function init(){
	}
	
	static public function register_widget(){
		// Include the Widget's class
		require_once "widget.php";
		
		register_widget("colorwp_twitter_widget");
	}
	
	static public function localize(){
		load_plugin_textdomain( self::$textdomain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}

// Initialize the plugin after the core WP functionality has loaded
add_action('init', array('cwp_twitter_widget_plugin', 'init'));

// Register the widget itself
add_action('widgets_init', array('cwp_twitter_widget_plugin', 'register_widget'));

// Support l18n for the plugin by loading a textdomain
add_action('plugins_loaded', array('cwp_twitter_widget_plugin', 'localize'));

?>