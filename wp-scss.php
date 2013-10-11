<?php 
/**
 * Plugin Name: WP-SCSS
 * Plugin URI: http://connectthink.com
 * Description: Compiles sass files.
 * Version: 1.3
 * Author: Connect Think
 * Author URI: http://connectthink.com
 * License: MIT
 */

/**
 * Plugin Workflow
 *    1. Create plugin global variables
 *    2. Require dependancies
 *        a. scssphp - does scss compiling using php (vendor)
 *        b. wp-scss class - manages compiling
 *    3. Registering Settings Page and Options
 *    4. Assign plugin settings
 *    5. Instantiate wp_scss object and run compiler
 *    6. Handle Errors
 */


/*
 * 1. PLUGIN GLOBAL VARIABLES
 */

// Plugin Paths
if (!defined('WPSCSS_THEME_DIR'))
    define('WPSCSS_THEME_DIR', ABSPATH . 'wp-content/themes/' . get_template());

if (!defined('WPSCSS_PLUGIN_NAME'))
    define('WPSCSS_PLUGIN_NAME', trim(dirname(plugin_basename(__FILE__)), '/'));

if (!defined('WPSCSS_PLUGIN_DIR'))
    define('WPSCSS_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . WPSCSS_PLUGIN_NAME);

if (!defined('WPSCSS_PLUGIN_URL'))
    define('WPSCSS_PLUGIN_URL', WP_PLUGIN_URL . '/' . WPSCSS_PLUGIN_NAME);

// Plugin Version
if (!defined('WPSCSS_VERSION_KEY'))
    define('WPSCSS_VERSION_KEY', 'wpscss_version');

if (!defined('WPSCSS_VERSION_NUM'))
    define('WPSCSS_VERSION_NUM', '1.3.0');

add_option(WPSCSS_VERSION_KEY, WPSCSS_VERSION_NUM);


/*
 * 2. REQUIRE DEPENDANCIES
 *    
 *    scssphp - scss compiler 
 *    class-wp-scss
 *    options.php - settings for plugin page
 */

include_once WPSCSS_PLUGIN_DIR . '/scssphp/scss.inc.php'; // Sass Compiler (vendor)
include_once WPSCSS_PLUGIN_DIR . '/class/class-wp-scss.php'; // Compiling Manager
include_once WPSCSS_PLUGIN_DIR . '/options.php'; // Options page class


/**
 * 3. REGISTER SETTINGS
 * 
 *  Instantiate Options Page
 *  Create link on plugin page to settings page  
 */

if( is_admin() ) {
    $wpscss_settings = new Wp_Scss_Settings();
}

add_filter('plugin_action_links', 'wpscss_plugin_action_links', 10, 2);
function wpscss_plugin_action_links($links, $file) {
  static $this_plugin;

  if( !$this_plugin ) {
    $this_plugin = plugin_basename(__FILE__);
  }

  if ($file == $this_plugin) {
        // The "page" query string value must be equal to the slug
        // of the Settings admin page we defined earlier, which in
        // this case equals "myplugin-settings".
        $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=wpscss_options">Settings</a>';
        array_unshift($links, $settings_link);
    }

    return $links;
}


/** 
 * 4. PLUGIN SETTINGS
 * 
 * Pull settings from options table
 * Scrub empty fields or directories that don't exists
 * Assign settings via settings array to pass to object
 */

$wpscss_options = get_option( 'wpscss_options' );
$scss_dir_setting = $wpscss_options['scss_dir'];
$css_dir_setting = $wpscss_options['css_dir'];

// Checks if directories are empty 
if( $scss_dir_setting == false || $css_dir_setting == false ) {
  function wpscss_settings_error() {
      echo '<div class="error">
        <p><strong>Wp-Scss</strong> requires both directories be specified. <a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=wpscss_options">Please update your settings.</a></p>
      </div>';
  }
  add_action('admin_notices', 'wpscss_settings_error');

// Checks if directory exists
} elseif ( !file_exists(WPSCSS_THEME_DIR . $scss_dir_setting) || !file_exists(WPSCSS_THEME_DIR . $css_dir_setting) ) {
  function wpscss_settings_error() {
      echo '<div class="error">
        <p><strong>Wp-Scss:</strong> One or more specified directories does not exist. <a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=wpscss_options">Please update your settings.</a></p>
      </div>';
  }
  add_action('admin_notices', 'wpscss_settings_error');
}

// Plugin Settings
$wpscss_settings = array(
  'scss_dir'  =>  WPSCSS_THEME_DIR . $scss_dir_setting,
  'css_dir'   =>  WPSCSS_THEME_DIR . $css_dir_setting,
  'compiling' =>  $wpscss_options['compiling_options'], 
  'errors'    =>  $wpscss_options['errors']
);


/**
 * 5. INSTANTIATE & EXECUTE COMPILER
 */

$wpscss_compiler = new Wp_Scss(
  $wpscss_settings['scss_dir'],
  $wpscss_settings['css_dir'],
  $wpscss_settings['compiling']
);


if ( $wpscss_compiler->needs_compiling() ) {
  $wpscss_compiler->compile();
}





/**
 * 6. HANDLE COMPILING ERRORS
 */
//var_dump(count($wpscss_compiler->compile_errors));
if ( !is_admin() && $wpscss_settings['errors'] === 'show' && count($wpscss_compiler->compile_errors) > 0) {
  echo '<div class="sass_errors"><pre>';
  echo '<h6 style="margin: 15px 0;">Sass Compiling Error</h6>';
  
  foreach( $wpscss_compiler->compile_errors as $error) {
    echo '<p class="sass_error">';
    echo '<strong>'. $error['file'] .'</strong> <br/><em>"'. $error['message'] .'"</em>'; 
    echo '<p class="sass_error">';
  }

  echo '</pre></div>';
}
