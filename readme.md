# WP-SCSS
#### A lightweight SCSS compiler for Wordpress.

Compiles .scss files on your wordpress install using [lefo's scssphp](https://github.com/leafo/scssphp). Includes settings page for configuring directories, error reporting, compiling options, and auto enqueuing.

The plugin only compiles when changes have been made to the scss files. Compiles are made to the matching css file, so disabling this plugin will not take down your stylesheets. In the instance where a matching css file does not exist yet, the plugin will create the appropriate css file in the css directory.

## Settings

#### Directories
Directories are defined relative to your theme folder. They must be separate from one another, so you cannot define the root folder to compile into itself.

Ideally you should setup a scss folder and a css folder within your theme. This will ensure the most accurate compiling.

    library
    |-css
    |  --style.css
    |  --ie.css
    |-scss
    |  --style.scss
    |  --ie.scss

#### Compiling Mode
Compiling comes in three modes:

* Expanded - Full open css. One line per property. Brackets close on their own line.
* Nested - Lightly compressed css. Brackets close with css block. Indents to match scss nesting.
* Compressed - More compressed css. Entire rule block on one line. No indentation.
* Compact - Removes all line breaks, unnecessary whitespace, and single-line comments.
* Crunched - Same as Compressed, but also removes multi-line comments.

See examples of each in [leafo's documentation](http://leafo.github.io/scssphp/docs/#output-formatting)

#### Source Map Mode
Source maps come in three modes:

* None - No source map will be generated.
* Inline - A source map will be generated in the compiled CSS file.
* File - A source map will be generated as a standalone file in the compiled CSS directory.

#### Error Display
'Show in Header' will post a message on the front end when errors have occured. This helps debug as you write your scss.

If you're working on a live/production site, you can send errors to a log. This will create a log file in your scss directory and print errors there as they occur. Just keep an eye on it, because the css will not be updated until errors have been resolved.

#### Enqueuing
The plugin can automatically add your css files to the header for you. This option will [enqueue](http://codex.wordpress.org/Function_Reference/wp_enqueue_style) all files found in the css directory defined in the settings. Keep this in mind if you have other non-compiled css files in this folder. The plugin will add them to the header, just don't reenque them somewhere else.

Also keep in mind, that if you disable this plugin it can no longer enqueue files for you.


## Directions

*This plugin requires at least php 5.4 to work.*

#### Importing Subfiles
You can import other scss files into parent files and compile them into a single css file. To do this, use @import as normal in your scss file. All imported file names *must* start with an underscore. Otherwise they will be compiled into their own css file.

When importing in your scss file, you can leave off the underscore.

    @import 'subfile';

#### Setting Variables via PHP
You can set SCSS variables in your theme or plugin by using the wp_scss_variables filter.

    function wp_scss_set_variables(){
        $variables = array(
            'black' => '#000',
            'white' => '#fff'
        );
        return $variables;
    }
    add_filter('wp_scss_variables','wp_scss_set_variables');

#### Always Recompile
During development it's sometimes useful to force stylesheet compilation on every page load. Especially on hosts where filemtime() is not updating consistently.

You can tell the plugin to always recompile by adding the following constant to your wp-config.php or functions.php file.

    define('WP_SCSS_ALWAYS_RECOMPILE', true);

#### Compass Support
Currently there isn't a way to fully support [compass](https://github.com/chriseppstein/compass) with a php compiler. If you want limited support, you can manually import the compass framework. You'll need both the _compass.scss and compass directory.

    compass / frameworks / compass / stylesheets /
    @import 'compass';

Alternatively, you can include [Bourbon](https://github.com/thoughtbot/bourbon) in a similar fashion.

#### .sass Support
This plugin will only work with .scss format.

## Changelog
* 1.2.4
  * Updated scssphp to version 0.7.5
  * Added source map [@iannacone](https://github.com/ConnectThink/WP-SCSS/issues/49)
  * Always define $wpscss_compiler in the global scope [@jazbek](https://github.com/ConnectThink/WP-SCSS/pull/98)
* 1.2.3
  * Updated scssphp to version 0.7.2 [@hellerbenjamin](https://github.com/ConnectThink/WP-SCSS/pull/86)
  * Removed depricated screen_icon()
* 1.2.2
  * Updated scssphp to version 0.6.6
* 1.2.1
  * Changed set version option to update if already exists
* 1.2.0
  * Fixed a bug where directory inputs were not getting sanitized [@mmcev106](https://github.com/ConnectThink/WP-SCSS/pull/66)
  * Made the missing directory warning also display if a specified path is a file [@mmcev106](https://github.com/ConnectThink/WP-SCSS/pull/65)
  * Added /vendor to .gitignore [@mmcev106](https://github.com/ConnectThink/WP-SCSS/pull/64)
  * Dont enqueue already enqueued stylesheets [@bobbysmith007](https://github.com/ConnectThink/WP-SCSS/pull/61)
* 1.1.9 - Added filter to set variables via PHP [@ohlle](https://github.com/ohlle) and option to minify CSS output [@mndewitt](https://github.com/mndewitt)
* 1.1.8 - Various improvements from pull requests by [@jbrains](https://github.com/jbrains) and [@brainfork](https://github.com/brainfork)
* 1.1.7 - Update scssphp to 0.0.12 - pull from #16 [@GabrielGil](https://github.com/GabrielGil)
* 1.1.6 - Upgraded scss.inc.php to version 0.0.10 - pull from #12 [@kirkhoff](https://github.com/kirkhoff)
* 1.1.5 - Added option to only show errors to logged in users - merge from #10 [@tolnem](https://github.com/tolnem)
* 1.1.4 - Add support for subfolders in scss directory
* 1.1.3 - Fix print bug (2) in header
* 1.1.2 - Add support for moved wp-content directory
* 1.1.1 - Catch permissions errors
* 1.0.0 - Initial Release

## License
This plugin is developed and maintained by Connect Think.
[GPL V3](http://www.gnu.org/copyleft/gpl.html)

## Gifts
https://connectthink.com/wp-scss-gift/

