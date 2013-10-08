<?php





// Define Directories
$input_dir = get_stylesheet_directory().'/library/scss/';
$output_dir = get_stylesheet_directory().'/library/css/';

// Setup
require_once __DIR__ . "/scssphp/scss.inc.php";
$scss = new scssc();
$scss->setFormatter('scss_formatter_compressed');
$scss->setImportPaths($input_dir);


// Get Sass Files, don't include those with '_' or sub directories
$input_files = array();
$dir = new DirectoryIterator($input_dir);
foreach ($dir as $fileinfo) {
  if (substr($fileinfo->getFilename(), 0, 1) != "_" && pathinfo($fileinfo, PATHINFO_EXTENSION) == 'scss') {
    array_push($input_files, $fileinfo->getFilename());
  }
}

// Compile Logic
function needsUpdate($s, $inFname, $outFname) {
  if (!file_exists($outFname)) {
    return true;
  } elseif($s->compile(file_get_contents($inFname)) != file_get_contents($outFname)) {
    return true;
  }
}
function compile($s, $in, $out) {
   try {
      $css = $s->compile(file_get_contents($in));
      file_put_contents($out, $css);
    } catch (Exception $e) {}
}

// Compile Files
foreach($input_files as $input_file) {
  $input = $input_dir.$input_file;
  $outputName = preg_replace("/\.[^$]*/",".min.css", $input_file);
  $output = $output_dir.$outputName;
  if (needsUpdate($scss, $input, $ouput)) { 
    compile($scss, $input, $output);
  }
  enqueue_file($outputName);
}

// Enqueue Compiled Files
function enqueue_file($compiled_css) {
  $name = preg_replace("/\.[^$]*/","", $compiled_css);
  $uri = get_template_directory_uri().'/library/css/'.$compiled_css;
  $deps = array('bones-stylesheet');
 wp_register_style(
    $name,
    $uri,
    $deps
  );
  wp_enqueue_style($name);
}



// Error Handling
function myException($exception) {echo "<strong>Sass Syntax Error:</strong> " , $exception->getMessage();}
set_exception_handler('myException');
?>
