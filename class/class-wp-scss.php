<?php

include_once( WPSCSS_PLUGIN_DIR . '/scssphp/scss.inc.php' );
use Leafo\ScssPhp\Compiler;

class Wp_Scss {
  /**
   * Compiling preferences properites
   *
   * @var string
   * @access public
   */
  public $scss_dir, $css_dir, $compile_method, $scssc, $compile_errors, $sourcemaps;

  /**
   * Set values for Wp_Scss::properties
   *
   * @param string scss_dir - path to source directory for scss files
   * @param string css_dir - path to output directory for css files
   * @param string method - type of compile (compressed, expanded, etc)
   *
   * @var object scssc - instantiate the compiling object.
   *
   * @var array compile_errors - catches errors from compile
   */
  public function __construct ($scss_dir, $css_dir, $compile_method, $sourcemaps) {
    global $scssc;
    $this->scss_dir       = $scss_dir;
    $this->css_dir        = $css_dir;
    $this->compile_method = $compile_method;
    $this->compile_errors = array();
    $scssc                = new Compiler();

    $scssc->setFormatter( $compile_method );
    $scssc->setImportPaths( $this->scss_dir );
    
    $this->sourcemaps = $sourcemaps;
  }

 /**
   * METHOD COMPILE
   * Loops through scss directory and compilers files that end
   * with .scss and do not have '_' in front.
   *
   * @function compiler - passes input content through scssphp,
   *                      puts compiled css into cache file
   *
   * @var array input_files - array of .scss files with no '_' in front
   * @var array sdir_arr - an array of all the files in the scss directory
   *
   * @return nothing - Puts successfully compiled css into apporpriate location
   *                   Puts error in 'compile_errors' property
   * @access public
   */
  public function compile() {
      global $scssc, $cache;
      $cache = WPSCSS_PLUGIN_DIR . '/cache/';

      //Compiler - Takes scss $in and writes compiled css to $out file
      // catches errors and puts them the object's compiled_errors property
      function compiler($in, $out, $instance) {
        global $scssc, $cache;

        if (is_writable($cache)) {
          try {
	          $map = basename($out) . '.map';
			  $scssc->setSourceMap(constant('Leafo\ScssPhp\Compiler::' . $instance->sourcemaps));
			  $scssc->setSourceMapOptions(array(
			  	'sourceMapWriteTo' => $instance->css_dir . $map, // absolute path to a file to write the map to
				'sourceMapURL' => $map, // url of the map
				'sourceMapBasepath' => rtrim(ABSPATH, '/'), // base path for filename normalization
				'sourceRoot' => '/', // This value is prepended to the individual entries in the 'source' field.
			  ));
			  
			  $css = $scssc->compile(file_get_contents($in), $in);

              file_put_contents($cache.basename($out), $css);
          } catch (Exception $e) {
              $errors = array (
                'file' => basename($in),
                'message' => $e->getMessage(),
                );
              array_push($instance->compile_errors, $errors);
          }
        } else {
          $errors = array (
            'file' => $cache,
            'message' => "File Permission Error, permission denied. Please make the cache directory writable."
          );
          array_push($instance->compile_errors, $errors);
        }
      }

      $input_files = array();
      // Loop through directory and get .scss file that do not start with '_'
      foreach(new DirectoryIterator($this->scss_dir) as $file) {
        if (substr($file, 0, 1) != "_" && pathinfo($file->getFilename(), PATHINFO_EXTENSION) == 'scss') {
          array_push($input_files, $file->getFilename());
        }
      }
      
      // For each input file, find matching css file and compile
      foreach ($input_files as $scss_file) {
        $input = $this->scss_dir.$scss_file;
        $outputName = preg_replace("/\.[^$]*/",".css", $scss_file);
        $output = $this->css_dir.$outputName;

        compiler($input, $output, $this);
      }

      if (count($this->compile_errors) < 1) {
        if  ( is_writable($this->css_dir) ) {
          foreach (new DirectoryIterator($cache) as $cache_file) {
            if ( pathinfo($cache_file->getFilename(), PATHINFO_EXTENSION) == 'css') {
              file_put_contents($this->css_dir.$cache_file, file_get_contents($cache.$cache_file));
              unlink($cache.$cache_file->getFilename()); // Delete file on successful write
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
      if (defined('WP_SCSS_ALWAYS_RECOMPILE') && WP_SCSS_ALWAYS_RECOMPILE) {
        return true;
      }

      $latest_scss = 0;
      $latest_css = 0;

      foreach ( new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->scss_dir)) as $sfile ) {
        if (pathinfo($sfile->getFilename(), PATHINFO_EXTENSION) == 'scss') {
          $file_time = $sfile->getMTime();

          if ( (int) $file_time > $latest_scss) {
            $latest_scss = $file_time;
          }
        }
      }

      foreach ( new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->css_dir)) as $cfile ) {
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
  public function enqueue_files($css_folder) {

      foreach( new DirectoryIterator($this->css_dir) as $stylesheet ) {
        if ( pathinfo($stylesheet->getFilename(), PATHINFO_EXTENSION) == 'css' ) {
          $name = $stylesheet->getBasename('.css') . '-style';
          $uri = get_stylesheet_directory_uri().$css_folder.$stylesheet->getFilename();
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
		global $scssc;
		$scssc->setVariables($variables);
  }

} // End Wp_Scss Class
