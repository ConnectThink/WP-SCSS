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
 *        c. ACF and ACF Options
 *        d. ACF options page configuration for plugin settings
 *    3. Link to settings page
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
 *    ACF
 *    ACF options addon
 *    ACF options fields
 */

include_once WPSCSS_PLUGIN_DIR . '/scssphp/scss.inc.php'; // Sass Compiler (vendor)
require_once WPSCSS_PLUGIN_DIR . '/class/class-wp-scss.php'; // Compiling Manager

$install_errors = array();

// Requires ACF to Work
if ( !function_exists( 'get_field' ) ) {
  array_push($install_errors, '<strong>WP-SCSS</strong> requires <a href="http://www.advancedcustomfields.com/" target="_blank">Advanced Custom Fields</a> to work. Please install it.');
}

// Requires ACF Options page
if ( function_exists('acf_add_options_sub_page') ) {
  // Register Settings Page
  acf_add_options_sub_page(array(
    'title' => 'WP-Scss',
    'parent' => 'options-general.php',
    'capability' => 'manage_options'
  ));
  
  // Import Settings Fields (ACF export);
  //include_once( WP_PLUGIN_DIR . '/settings/wp-scss-settings.php');

} else {
  array_push($install_errors, '<strong>WP-SCSS</strong> requires ACF Options v1.2 to work. Please install it.');
}

// Alert Admin of Errors
if ( count($install_errors) > 0 ) {
    
  function wpscss_install_error() {
    global $install_errors;
    foreach ( $install_errors as $error ) {
      echo '<div class="error">
        <p>'. $error .'</p>
      </div>';
    }
  }
  add_action('admin_notices', 'wpscss_install_error');
}


/**
 * 3. LINK TO SETTINGS PAGE
 */

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
        $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=acf-options-wp-scss">Settings</a>';
        array_unshift($links, $settings_link);
    }

    return $links;
}


/** 
 * 4. PLUGIN SETTINGS
 * 
 * Pull settings from options table
 * Scrub empty fields or directories that don't exists
 * Assign settings via settings array
 */

$scss_dir_setting = get_option('options_wpscss_scss_directory');
$css_dir_setting = get_option('options_wpscss_css_directory');

// Checks if directories are empty 
if( $scss_dir_setting == false || $css_dir_setting == false ) {
  function wpscss_settings_error() {
      echo '<div class="error">
        <p><strong>Wp-Scss</strong> requires both directories be specified. <a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=acf-options-wp-scss">Please update your settings.</a></p>
      </div>';
  }
  add_action('admin_notices', 'wpscss_settings_error');

// Checks if directory exists
} elseif ( !file_exists(WPSCSS_THEME_DIR . $scss_dir_setting) || !file_exists(WPSCSS_THEME_DIR . $css_dir_setting) ) {
  function wpscss_settings_error() {
      echo '<div class="error">
        <p><strong>Wp-Scss:</strong> One or more specified directories does not exist. <a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=acf-options-wp-scss">Please update your settings.</a></p>
      </div>';
  }
  add_action('admin_notices', 'wpscss_settings_error');
}

// Plugin Settings
$wpscss_settings = array(
  'scss_dir'  =>  WPSCSS_THEME_DIR . $scss_dir_setting,
  'css_dir'   =>  WPSCSS_THEME_DIR . $css_dir_setting,
  'compiling' =>  get_option('options_wpscss_compiling_mode'), 
  'errors'    =>  get_option('options_wpscss_errors')
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

if ( $wpscss_settings['errors'] === 'show' && count($wpscss_compiler->compile_errors) > 0 ) {
  echo '<div class="sass_errors"><pre>';
  echo '<h6 style="margin: 15px 0;">Sass Compiling Error</h6>';
  
  foreach( $wpscss_compiler->compile_errors as $error) {
    echo '<p class="sass_error">';
    echo '<strong>'. $error['file'] .'</strong> <br/><em>"'. $error['message'] .'"</em>'; 
    echo '<p class="sass_error">';
  }

  echo '</pre></div>';
}
