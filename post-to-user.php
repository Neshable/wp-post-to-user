<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Migrate Custom Post to User 
 * Plugin URI:        none
 * Description:       Migrate custom any WP post type to users. In this version many fields are hard-coded to the plugin, so 						in order to use it, please modify for your needs. This is non-commercial plugin.
 * Version:           1.0.0
 * Author:            Nesho Sabakov
 * Author URI:        http://softsab.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       post-to-user
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PLUGIN_NAME_VERSION', '1.0.0' );


/**
 * The core plugin class
 */

require plugin_dir_path( __FILE__ ) . 'includes/class-post-to-user.php';


function run_post_user() {
	$plugin = new Post_User();
}

run_post_user();
