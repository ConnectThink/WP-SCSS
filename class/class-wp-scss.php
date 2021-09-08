<?php
//Required to use get_home_path()
require_once(ABSPATH . 'wp-admin/includes/file.php');
include_once(WPSCSS_PLUGIN_DIR . '/scssphp/scss.inc.php');
use ScssPhp\ScssPhp\Compiler;

class Wp_Scss {

  /**
   * Set values for Wp_Scss::properties
   *
   * @param string scss_dir - path to source directory for scss files
   * @param string css_dir - path to output directory for css files
   * @param string compile_method - type of compile (compressed or expanded)
   *
   * @var object scssc - instantiate the compiling object.
   *
   * @var array compile_errors - catches errors from compile
   */
  public function __construct ($scss_dir, $css_dir, $compile_method, $sourcemaps) {

    $this->scss_dir         = $scss_dir;
    $this->css_dir          = $css_dir;
    $this->compile_errors   = array();
    $this->scssc            = new Compiler();

    $this->cache = WPSCSS_PLUGIN_DIR . '/cache/';

    $this->scssc->setOutputStyle( $compile_method );
    $this->scssc->setImportPaths( $this->scss_dir );

    $this->sourcemaps = $sourcemaps;
  }

  /**
   * METHOD GET SCSS DIRECTORY
   * Returns the stored SCSS directory for this compile instance
   *
   * @return string - Value of the SCSS directory
   *
   * @access public
   */
  public function get_scss_dir() {
    return $this->scss_dir;
  }

  /**
   * METHOD GET CSS DIRECTORY
   * Returns the stored CSS directory for this compile instance
   *
   * @return string - Value of the CSS directory
   *
   * @access public
   */
  public function get_css_dir() {
    return $this->css_dir;
  }

  /**
   * METHOD GET CSS DIRECTORY
   * Returns the stored CSS directory for this compile instance
   *
   * @return array - List of errors from the compile process, if any
   *
   * @access public
   */
  public function get_compile_errors() {
    return $this->compile_errors;
  }

  /**
   * METHOD COMPILE
   * Loops through scss directory and compilers files that end
   * with .scss and do not have '_' in front.
   *
   * @var array input_files - array of .scss files with no '_' in front
   * @var array sdir_arr - an array of all the files in the scss directory
   *
   * @return nothing - Puts successfully compiled css into apporpriate location
   *                   Puts error in 'compile_errors' property
   * @access public
   */
  public function compile() {

    $input_files = array();

    // Loop through directory and get .scss file that do not start with '_'
    foreach(new DirectoryIterator($this->scss_dir) as $file) {
      if (substr($file, 0, 1) != "_" && pathinfo($file->getFilename(), PATHINFO_EXTENSION) == 'scss') {
        array_push($input_files, $file->getFilename());
      }
    }

    // For each input file, find matching css file and compile
    foreach ($input_files as $scss_file) {
      $input = $this->scss_dir . $scss_file;
      $outputName = preg_replace("/\.[^$]*/", ".css", $scss_file);
      $output = $this->css_dir . $outputName;

      $this->compiler($input, $output);
    }

    if (count($this->compile_errors) < 1) {
      if  ( is_writable($this->css_dir) ) {
        foreach (new DirectoryIterator($this->cache) as $this->cache_file) {
          if ( pathinfo($this->cache_file->getFilename(), PATHINFO_EXTENSION) == 'css') {
            file_put_contents($this->css_dir . $this->cache_file, file_get_contents($this->cache . $this->cache_file));
            unlink($this->cache . $this->cache_file->getFilename()); // Delete file on successful write
          }
        }
      } else {
        $errors = array(
          'file' => 'CSS Directory',
          'message' => "File Permissions Error, permission denied. Please make your CSS directory writable."
        );
        array_push($this->compile_errors, $errors);
      }
    }
  }

  /**
   * METHOD COMPILER
   * Takes scss $in and writes compiled css to $out file
   * catches errors and puts them the object's compiled_errors property
   *
   * @function compiler - passes input content through scssphp,
   *                      puts compiled css into cache file
   *
   * @var array input_files - array of .scss files with no '_' in front
   * @var array sdir_arr - an array of all the files in the scss directory
   *
   * @return nothing - Puts successfully compiled css into appropriate location
   *                   Puts error in 'compile_errors' property
   * @access public
   */
  private function compiler($in, $out) {

    if (!file_exists($this->cache)) {
      mkdir($this->cache, 0644);
    }
    if (is_writable($this->cache)) {
      try {
        $map = basename($out) . '.map';
        $this->scssc->setSourceMap(constant('ScssPhp\ScssPhp\Compiler::' . $this->sourcemaps));
        $this->scssc->setSourceMapOptions(array(
          'sourceMapWriteTo' => $this->css_dir . $map, // absolute path to a file to write the map to
          'sourceMapURL' => $map, // url of the map
          'sourceMapBasepath' => rtrim(ABSPATH, '/'), // base path for filename normalization
          'sourceRoot' => home_url('/'), // This value is prepended to the individual entries in the 'source' field.
        ));

        $compilationResult = $this->scssc->compileString(file_get_contents($in), $in);
        $css = $compilationResult->getCss();

        file_put_contents($this->cache . basename($out), $css);
      } catch (Exception $e) {
        $errors = array (
          'file' => basename($in),
          'message' => $e->getMessage(),
        );
        array_push($this->compile_errors, $errors);
      }
    } else {
      $errors = array (
        'file' => $this->cache,
        'message' => "File Permission Error, permission denied. Please make the cache directory writable."
      );
      array_push($this->compile_errors, $errors);
    }
  }

  /**
   * METHOD NEEDS_COMPILING
   * Gets the most recently modified file in the scss directory
   * and compares that do the most recently modified css file.
   * If scss is greater, we assume that changes have been made
   * and compiling needs to occur to update css.
   *
   * @param string scss_dir - path to scss folder
   * @param string css_dir - path to css folder
   *
   * @var array sdir_arr - scss directory files
   * @var array cdir_arr - css directory files
   *
   * @var string latest_scss - file mod time of the most recent file change
   * @var string latest_css - file mod time of the most recent file change
   *
   * @return bool - true if compiling is needed
   */
  public function needs_compiling() {
    global $wpscss_settings;
    if (defined('WP_SCSS_ALWAYS_RECOMPILE') && WP_SCSS_ALWAYS_RECOMPILE || (isset($wpscss_settings['always_recompile']) ? $wpscss_settings['always_recompile'] === "1" : false)) {
      return true;
    }

    $latest_scss = 0;
    $latest_css = 0;

    foreach ( new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->scss_dir), RecursiveDirectoryIterator::SKIP_DOTS) as $sfile ) {
      if (pathinfo($sfile->getFilename(), PATHINFO_EXTENSION) == 'scss') {
        $file_time = $sfile->getMTime();

        if ( (int) $file_time > $latest_scss) {
          $latest_scss = $file_time;
        }
      }
    }

    foreach ( new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->css_dir), RecursiveDirectoryIterator::SKIP_DOTS) as $cfile ) {
      if (pathinfo($cfile->getFilename(), PATHINFO_EXTENSION) == 'css') {
        $file_time = $cfile->getMTime();

        if ( (int) $file_time > $latest_css) {
          $latest_css = $file_time;
        }
      }
    }

    if ($latest_scss > $latest_css) {
      return true;
    } else {
      return false;
    }
  }

  public function style_url_enqueued($url){
    global $wp_styles;
    foreach($wp_styles->queue as $wps_name){
      $wps = $wp_styles->registered[$wps_name];
      if($wps->src == $url){
        return $wps;
      }
    }
    return false;
  }
  /**
   * METHOD ENQUEUE STYLES
   * Enqueues all styles in the css directory.
   *
   * @param $css_folder - directory from theme root. We need this passed in separately
   *                      so it can be used in a url, not path
   */
  public function enqueue_files($base_folder_path, $css_folder) {
    if($base_folder_path === wp_get_upload_dir()['basedir']){
      $enqueue_base_url = wp_get_upload_dir()['baseurl'];
    }
    else if($base_folder_path === WPSCSS_PLUGIN_DIR){
      $enqueue_base_url = plugins_url();
    }
    else if($base_folder_path === get_template_directory()){
      $enqueue_base_url = get_template_directory_uri();
    }
    else{ // assume default of get_stylesheet_directory()
      $enqueue_base_url = get_stylesheet_directory_uri();
    }

    foreach( new DirectoryIterator($this->css_dir) as $stylesheet ) {
      if ( pathinfo($stylesheet->getFilename(), PATHINFO_EXTENSION) == 'css' ) {
        $name = $stylesheet->getBasename('.css') . '-style';
        $uri = $enqueue_base_url . $css_folder . $stylesheet->getFilename();
        $ver = $stylesheet->getMTime();

        wp_register_style(
          $name,
          $uri,
          array(),
          $ver,
          $media = 'all' );

        if(!$this->style_url_enqueued($uri)){
          wp_enqueue_style($name);
        }
      }
    }
  }

  public function set_variables(array $variables) {

    $this->scssc->addVariables(array_map('ScssPhp\ScssPhp\ValueConverter::parseValue', $variables));
  }
} // End Wp_Scss Class
