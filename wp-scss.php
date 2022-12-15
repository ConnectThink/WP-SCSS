<?php
/**
 * Plugin Name: WP-SCSS
 * Plugin URI: https://github.com/ConnectThink/WP-SCSS
 * Description: Compiles scss files live on WordPress.
 * Version: 4.0.2
 * Author: Connect Think
 * Author URI: http://connectthink.com
 * License: GPLv3
 */

/**
 * Plugin Workflow
 *    1. Create plugin global variables
 *    2. Require dependancies
 *        a. scssphp - does scss compiling using php (vendor)
 *        b. wp-scss class - manages compiling
 *        c. options class - builds settings page
 *    3. Registering Settings Page and Options
 *    4. Read correct DB values for version 2.0.1 (Leafo => ScssPhp)
 *    5. Assign plugin settings
 *    6. Instantiate wp_scss object and run compiler
 *    7. Handle Errors
 *    8. Enqueue Styles
 */


/*
 * 1. PLUGIN GLOBAL VARIABLES
 */

// Plugin Paths
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
  define('WPSCSS_VERSION_NUM', '3.0.0');

// Add version to options table
if ( get_option( WPSCSS_VERSION_KEY ) !== false ) {

  // The option already exists, so we just update it.
  update_option( WPSCSS_VERSION_KEY, WPSCSS_VERSION_NUM );

} else {

  // The option hasn't been added yet. We'll add it with $autoload set to 'no'.
  $deprecated = null;
  $autoload = 'no';
  add_option( WPSCSS_VERSION_KEY, WPSCSS_VERSION_NUM, $deprecated, $autoload );
}


/*
 * 2. REQUIRE DEPENDENCIES
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
    $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=wpscss_options">Settings</a>';
    array_unshift($links, $settings_link);
  }

  return $links;
}

/**
 * 4. UPDATE DATABASE VALUES
 *
 * Correction for when Leafo is stored in DB
 * as a value in compiling_options
 *
 */

add_filter('option_wpscss_options', 'wpscss_plugin_db_cleanup');
function wpscss_plugin_db_cleanup($option_values){
  if( array_key_exists('compiling_options', $option_values) ) {
    $compiling_options = str_replace("Leafo", "ScssPhp", $option_values['compiling_options']);
    $compiling_options = str_replace("ScssPhp\\ScssPhp\\Formatter\\", "", $compiling_options);
    $compiling_options = str_replace(["Compact", "Crunched"], "compressed", $compiling_options);
    $compiling_options = str_replace("Nested", "expanded", $compiling_options);
    $compiling_options = strtolower($compiling_options);
    $option_values['compiling_options'] = $compiling_options;
  }
  return $option_values;
}

/**
 * 5. PLUGIN SETTINGS
 *
 * Pull settings from options table
 * Scrub empty fields or directories that don't exists
 * Assign settings via settings array to pass to object
 */

// Use current WP functions to get directory values, only store key
function get_base_dir_from_name($name_or_old_path){
  $possible_directories = array(
    'Uploads Directory' => wp_get_upload_dir()['basedir'],
    'WP-SCSS Plugin'    => WPSCSS_PLUGIN_DIR,
  );
  // Won't display if no parent theme as it would have duplicate keys in array
  if(get_stylesheet_directory() === get_template_directory()){
    $possible_directories['Current Theme'] = get_stylesheet_directory();
  }else{
    $possible_directories['Parent Theme'] = get_template_directory();
    $possible_directories['Child Theme'] = get_stylesheet_directory();
  }
  if(array_key_exists($name_or_old_path, $possible_directories)){
    return $possible_directories[$name_or_old_path];
  }else{
    $key = array_search($name_or_old_path, $possible_directories);
    $notice = '<p><strong>WP-SCSS:</strong> <a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=wpscss_options">Please save your settings</a>';
    if($key){
      $notice .= ' with the Base Location of <b>'. $key .'</b> specified.</p>';
    }else{
      $notice .= ' with the <b>correct</b> Base Location specified.</p>';
    }
    add_action('admin_notices', function() use ($notice){
      echo '<div class="notice notice-info">' . $notice . '</div>';
    });
    return $name_or_old_path;
  }
}

$wpscss_options = get_option( 'wpscss_options' );
$base_compiling_folder = isset($wpscss_options['base_compiling_folder']) ? get_base_dir_from_name($wpscss_options['base_compiling_folder']) : get_stylesheet_directory();
$scss_dir_setting = isset($wpscss_options['scss_dir']) ? $wpscss_options['scss_dir'] : '';
$css_dir_setting = isset($wpscss_options['css_dir']) ? $wpscss_options['css_dir'] : '';

// Checks if directories are not yet defined
if( $scss_dir_setting == false || $css_dir_setting == false ) {
  function wpscss_settings_error() {
    echo '<div class="notice notice-error">
      <p><strong>WP-SCSS</strong> requires both directories be specified. <a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=wpscss_options">Please update your settings.</a></p>
      </div>';
  }
  add_action('admin_notices', 'wpscss_settings_error');
  return 0; //exits

  // Checks if SCSS directory exists
} elseif ( !is_dir($base_compiling_folder . $scss_dir_setting) ) {
  add_action('admin_notices', function() use ($base_compiling_folder, $scss_dir_setting){
    echo '<div class="notice notice-error">
      <p><strong>WP-SCSS:</strong> The SCSS directory does not exist (' . $base_compiling_folder . $scss_dir_setting . '). Please create the directory or <a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=wpscss_options">update your settings.</a></p>
      </div>';
  });
  return 0; //exits

  // Checks if CSS directory exists
} elseif ( !is_dir($base_compiling_folder . $css_dir_setting) ) {
  add_action('admin_notices', function() use ($base_compiling_folder, $css_dir_setting){
    echo '<div class="notice notice-error">
      <p><strong>WP-SCSS:</strong> The CSS directory does not exist (' . $base_compiling_folder . $css_dir_setting . '). Please create the directory or <a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=wpscss_options">update your settings.</a></p>
      </div>';
  });
  return 0; //exits
}

// Plugin Settings
$wpscss_settings = array(
  'scss_dir'         => $base_compiling_folder . $scss_dir_setting,
  'css_dir'          => $base_compiling_folder . $css_dir_setting,
  'compiling'        => isset($wpscss_options['compiling_options']) ? $wpscss_options['compiling_options'] : 'compressed',
  'always_recompile' => isset($wpscss_options['always_recompile'])  ? $wpscss_options['always_recompile']  : false,
  'errors'           => isset($wpscss_options['errors'])            ? $wpscss_options['errors']            : 'show',
  'sourcemaps'       => isset($wpscss_options['sourcemap_options']) ? $wpscss_options['sourcemap_options'] : 'SOURCE_MAP_NONE',
  'enqueue'          => isset($wpscss_options['enqueue'])           ? $wpscss_options['enqueue']           : 0
);


/**
 * 6. INSTANTIATE & EXECUTE COMPILER
 *
 * Passes settings to the object
 * If needs_compiling passes, runs compile method
 */

global $wpscss_compiler;
$wpscss_compiler = new Wp_Scss(
  $wpscss_settings['scss_dir'],
  $wpscss_settings['css_dir'],
  $wpscss_settings['compiling'],
  $wpscss_settings['sourcemaps']
);

//wp_scss_needs_compiling() needs to be run as wp_head-action to make it possible
//for themes to set variables and decide if the style needs compiling
function wp_scss_needs_compiling() {
  global $wpscss_compiler;
  $needs_compiling = apply_filters('wp_scss_needs_compiling', $wpscss_compiler->needs_compiling());
  if ( $needs_compiling ) {
    wp_scss_compile();
    wpscss_handle_errors();
  }
}
add_action('wp_loaded', 'wp_scss_needs_compiling');

function wp_scss_compile() {
  global $wpscss_compiler;
  $variables = apply_filters('wp_scss_variables', array());
  foreach ($variables as $variable_key => $variable_value) {
    if (strlen(trim($variable_value)) == 0) {
      unset($variables[$variable_key]);
    }
  }
  $wpscss_compiler->set_variables($variables);
  $wpscss_compiler->compile();
}

/**
 * 7. HANDLE COMPILING ERRORS
 *
 * First block handles print errors to front end.
 * This adds a small style block the header to help errors get noticed
 *
 * Second block handles print errors to log file.
 * After the file gets over 1MB it does a purge and deletes the first
 * half of entries in the file.
 */

$log_file = $wpscss_compiler->get_scss_dir() . 'error_log.log';

function wpscss_error_styles() {
  echo
    '<style>
      .scss_errors {
        position: fixed;
        top: 0px;
        z-index: 99999;
        width: 100%;
      }
      .scss_errors pre {
        background: #f5f5f5;
        border-left: 5px solid #DD3D36;
        box-shadow: 0 2px 3px rgba(51,51,51, .4);
        color: #666;
        font-family: monospace;
        font-size: 14px;
        margin: 20px 0;
        overflow: auto;
        padding: 20px;
        white-space: pre;
        white-space: pre-wrap;
        word-wrap: break-word;
      }
    </style>';
}

function wpscss_settings_show_errors($errors) {
  $allowed_html = array( 'string' => array(), 'br' => array(), 'em' => array() );
  echo '<div class="scss_errors"><pre>';
  echo '<h6 style="margin: 15px 0;">Sass Compiling Error</h6>';

  foreach( $errors as $error) {
    echo '<p class="sass_error">';
    echo wp_kses('<strong>'. $error['file'] .'</strong> <br/><em>"'. $error['message'] .'"</em>', $allowed_html);
    echo '<p class="sass_error">';
  }

  echo '</pre></div>';

  add_action('wp_print_styles', 'wpscss_error_styles');
}

function wpscss_handle_errors() {
  global $wpscss_settings, $log_file, $wpscss_compiler;
  // Show to logged in users: All the methods for checking user login are set up later in the WP flow, so this only checks that there is a cookie

  $compile_errors = $wpscss_compiler->get_compile_errors();
  if ( !is_admin() && $wpscss_settings['errors'] === 'show-logged-in' && !empty($_COOKIE[LOGGED_IN_COOKIE]) && count($compile_errors) > 0) {
    wpscss_settings_show_errors($compile_errors);
    // Show in the header to anyone
  } else if ( !is_admin() && $wpscss_settings['errors'] === 'show' && count($compile_errors) > 0) {
    wpscss_settings_show_errors($compile_errors);
  } else { // Hide errors and print them to a log file.
    foreach ($compile_errors as $error) {
      $error_string = date('m/d/y g:i:s', time()) .': ';
      $error_string .= $error['file'] . ' - ' . $error['message'] . PHP_EOL;
      file_put_contents($log_file, $error_string, FILE_APPEND);
      $error_string = "";
    }
  }

  // Clean out log file if it get's too large
  if ( file_exists($log_file) ) {
    if ( filesize($log_file) > 1000000) {
      $log_contents = file_get_contents($log_file);
      $log_arr = explode("\n", $log_contents);
      $new_contents_arr = array_slice($log_arr, count($log_arr)/2);
      $new_contents = implode(PHP_EOL, $new_contents_arr) . 'LOG FILE CLEANED ' . date('n/j/y g:i:s', time());
      file_put_contents($log_file, $new_contents);
    }
  }
}


/**
 * 8. ENQUEUE STYLES
 */

if ( $wpscss_settings['enqueue'] == '1' ) {
  function wpscss_enqueue_styles() {
    global $wpscss_compiler, $wpscss_options, $base_compiling_folder;
    $wpscss_compiler->enqueue_files($base_compiling_folder, $wpscss_options['css_dir']);
  }
  add_action('wp_enqueue_scripts', 'wpscss_enqueue_styles', 50);
}
