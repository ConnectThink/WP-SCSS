<?php 

class Wp_Scss {
  /**
   * Compiling preferences properites
   * 
   * @var string
   * @access public
   */
  public $scss_dir, $css_dir, $compile_method, $scssc;

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
   * @var array settings_errors - catches errors if settings aren not entered correct
   */
  public function __construct ($scss_dir, $css_dir, $compile_method) {
    $this->scss_dir = $scss_dir;
    $this->css_dir = $css_dir;
    $this->compile_method = $compile_method;

    $scssc = new scssc();
    $scssc-> setFormatter($this->compile_method);
    $scssc-> setImportPaths($scss_dir);

    $this->compile_errors = array();
    $this->settings_errors = array();
  }

 /**
   * Compiling workflow
   * 
   * @return nothing - Puts successfully compiled css into apporpriate location 
   *                   Puts error in 'compile_errors' property
   * @access public
   */
  public function compile() {



    
  } 

  /**
   * Tests if compiling is needed
   * DirectoryIterators have like 20 million errors, so let's try to catch them 
   *
   * @param string scss_dir - path to scss folder
   * @param string css_dir - path to css folder
   *
   * @var iterator sdirit - scss directory iterator
   * @var itator cdirit - css directory iterator
   * 
   * @return bool - true if compiling is needed
   */
  public function needs_compiling() {
      var_dump($this->scss_dir);
      //$sdirit = new DirectoryIterator($this->$scss_dir);
      //$cdirit = new DirectoryIterator($this->$css_dir);
    return 'yup';
  }


}


