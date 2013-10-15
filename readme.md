#WP-SCSS
####A lightweight SCSS compiler for Wordpress. 

Compiles .scss files on your wordpress install using [lefo's scssphp](https://github.com/leafo/scssphp). Includes settings page for configuring directories, error reporting, compiling options, and auto enqueuing.

The plugin only compiles when changes have been made to the scss files. Compiles are made to the matching css file, so disabling this plugin will not take down your stylesheets. In the instance where a matching css file does not exist yet, the plugin will create the appropriate css file in the css directory. 

##Settings

####Directories
Directories are defined relative to your theme folder. They must be separate from one another, so you cannot define the root folder to compile into itself. 

Ideally you should setup a scss folder and a css folder within your theme. This will ensure the most accurate compiling. 

    library
    |-css
    |  --style.css
    |  --ie.css
    |-scss
    |  --style.scss
    |  --ie.scss

####Compiling Mode
Compiling comes in three modes:

* Expanded - Full open css. One line per property. Brackets close on their own line.
* Nested - Lightly compressed css. Brackets close with css block. Indents to match scss nesting.
* Compressed - Fully compressed css. 

####Error Display
'Show in Header' will post a message on the front end when errors have occured. This helps debug as you write your scss. 

If you're working on a live/production site, you can send errors to a log. This will create a log file in your scss directory and print errors there as they occur. Just keep an eye on it, because the css will not be updated until errors have been resolved.

####Enqueuing
The plugin can automatically add your css files to the header for you. This option will [enqueue](http://codex.wordpress.org/Function_Reference/wp_enqueue_style) all files found in the css directory defined in the settings. Keep this in mind if you have other non-compiled css files in this folder. The plugin will add them to the header, just don't reenque them somewhere else. 

Also keep in mind, that if you disable this plugin it can no longer enqueue files for you.


##Directions

*This plugin requires at least php 5.1.2 to work.*

####@importing subfiles
You can import other scss files into parent files and compile them into a single css file. To do this, use @import as normal in your scss file. All imported file names *must* start with an underscore. Otherwise they will be compiled into their own css file.

When importing in your scss file, you can leave off the underscore.
  
    @import 'subfile';

####Compass Support
Currently there isn't a way to fully support [compass](https://github.com/chriseppstein/compass) with a php compiler. If you want limited support, you can manually import the compass framework. You'll need both the _compass.scss and compass directory.
    
     compass / frameworks / compass / stylesheets /
     @import 'compass';

Alternatively, you can include [Bourbon](https://github.com/thoughtbot/bourbon) in a similar fashion. 

####.sass Support
This plugin will only work with .scss format.

##License
This plugin is developed and maintained by Connect Think.  
[GPL V3](http://www.gnu.org/copyleft/gpl.html)

