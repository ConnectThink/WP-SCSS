# WP-SCSS

#### A lightweight SCSS compiler for Wordpress.

Compiles .scss files on your wordpress install using [scssphp](https://github.com/scssphp/scssphp). Includes settings page for configuring directories, error reporting, compiling options, and auto enqueuing.

The plugin only compiles when changes have been made to the scss files. Compiles are made to the matching css file, so disabling this plugin will not take down your stylesheets. In the instance where a matching css file does not exist yet, the plugin will create the appropriate css file in the css directory.

## Looking for new maintainer

If you are interested in giving back to the open source plugin respond [here](https://github.com/ConnectThink/WP-SCSS/issues/242) with your interest

## Settings

#### Directories

Directories are defined relative to your `base compile folder` which defaults to the theme folder. Alternatly you can choose the uploads directory or plugin directory. They must be separate from one another, so you cannot define the root folder to compile into itself.

Ideally you should setup a scss folder and a css folder within your theme. This will ensure the most accurate compiling.

    library
    |-css
    |  --style.css
    |  --ie.css
    |-scss
    |  --style.scss
    |  --ie.scss

#### Compiling Mode

Compiling comes in two modes:

- Compressed - More compressed css. Entire rule block on one line. No indentation.
- Expanded - Full open css. One line per property. Brackets close on their own line.

See examples of each in [ScssPHP's documentation](http://scssphp.github.io/scssphp)

- Current version of ScssPHP is 1.11.0

#### Source Map Mode

Source maps come in three modes:

- None - No source map will be generated.
- Inline - A source map will be generated in the compiled CSS file.
- File - A source map will be generated as a standalone file in the compiled CSS directory.

#### Error Display

'Show in Header' will post a message on the front end when errors have occured. This helps debug as you write your scss.

If you're working on a live/production site, you can send errors to a log. This will create a log file in your scss directory and print errors there as they occur. Just keep an eye on it, because the css will not be updated until errors have been resolved.

#### Enqueuing

The plugin can automatically add your css files to the header for you. This option will [enqueue](http://codex.wordpress.org/Function_Reference/wp_enqueue_style) all files found in the css directory defined in the settings. Keep this in mind if you have other non-compiled css files in this folder. The plugin will add them to the header, just don't reenque them somewhere else.

Also keep in mind, that if you disable this plugin it can no longer enqueue files for you.

## Directions

_This plugin requires at least php 7.2 to work._ . Tested up to php 7.4

#### Importing Subfiles

You can import other scss files into parent files and compile them into a single css file. To do this, use @import as normal in your scss file. All imported file names _must_ start with an underscore. Otherwise they will be compiled into their own css file.

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

You can tell the plugin to always recompile in the plugin options page or by adding the following constant to your wp-config.php or functions.php file.

    define('WP_SCSS_ALWAYS_RECOMPILE', true);

#### Compass Support

Currently there isn't a way to fully support [compass](https://github.com/chriseppstein/compass) with a php compiler. If you want limited support, you can manually import the compass framework. You'll need both the \_compass.scss and compass directory.

    compass / frameworks / compass / stylesheets /
    @import 'compass';

Alternatively, you can include [Bourbon](https://github.com/thoughtbot/bourbon) in a similar fashion.

#### .sass Support

This plugin will only work with .scss format.

#### Maintainers

- [@shadoath](https://github.com/shadoath)
- Bug reporters, issues, and pull request contributers metioned below. Thank you.

## Changelog

- 4.0.2
  - Full SVN commit to Wordpress
  - With version bump
- 4.0.0
  - Updates ScssPHP to version [1.11.0](https://github.com/scssphp/scssphp/releases/tag/v1.11.0) thanks to [fabarea](https://github.com/ConnectThink/WP-SCSS/issues/240)
- 3.0.0
  - Updates ScssPHP to version [1.10.0](https://github.com/scssphp/scssphp/releases/tag/v1.10.0) thanks to [fabarea](https://github.com/ConnectThink/WP-SCSS/issues/228)
- 2.4.0
  - Changes the base_compiling_folder to store key not path of directory [shadoath](https://github.com/ConnectThink/WP-SCSS/issues/219)
  - This allows deploying from local or staging to production by not saving absolute paths in DB.
- 2.3.5
  - Add 'selected' to wp_kses on select() [shadoath](https://github.com/ConnectThink/WP-SCSS/issues/217)
- 2.3.4
  - Add check to compiling_options on load() [alianschiavoncini](https://github.com/ConnectThink/WP-SCSS/issues/209)
  - Add more params to wp_kses in options() [evHaitch](https://github.com/ConnectThink/WP-SCSS/issues/213)
- 2.3.3
  - Fix params passed to wp_kses() [shadoath](https://github.com/ConnectThink/WP-SCSS/pull/211)
- 2.3.2
  - Add wp_kses() to echos with potential user input [shadoath](https://github.com/ConnectThink/WP-SCSS/pull/208)
- 2.3.1
  - Wrap check for WP_SCSS_ALWAYS_RECOMPILE with () [niaccurshi](https://github.com/ConnectThink/WP-SCSS/pull/199)
- 2.3.0
  - Update src to use [ScssPHP github repo at 1.5.2](https://github.com/scssphp/scssphp/releases/tag/1.5.2)
  - Update deprecated setFormatter to setOutputStyle and provide db migration [shadoath](https://github.com/ConnectThink/WP-SCSS/pull/195)
- 2.2.0
  - Updates to allow compile() from outside the plugin [niaccurshi](https://github.com/ConnectThink/WP-SCSS/pull/190)
  - Update src to use [ScssPHP github repo at 1.2.1](https://github.com/scssphp/scssphp/releases/tag/1.2.1)
- 2.1.6
  - When enqueueing CSS files Defer to WordPress for URLs instead of trying to guess them. Change by [mmcev106](https://github.com/ConnectThink/WP-SCSS/pull/185)
  - Allow setting Base Directory to Parent theme folder. [Shadoath](https://github.com/ConnectThink/WP-SCSS/issues/178)
- 2.1.5
  - Enqueue CSS files using `realpath` function. Addition by [mmcev106](https://github.com/ConnectThink/WP-SCSS/pull/179)
- 2.1.4
  - Set source URL to be home_url('/') not simply `/`. Issue found by [realjjaveweb](https://github.com/ConnectThink/WP-SCSS/issues/128)
- 2.1.3
  - Must declare global to use it for $base_compiling_folder.
- 2.1.2
  - Correction for enqueueing styles not defaulting to get_stylesheet_directory() [Issue](https://github.com/ConnectThink/WP-SCSS/issues/168)
- 2.1.1
  - Bug fixes after merging 2.0.2 and 2.1.0 defaults worked, but new options did not. [Shadoath](https://github.com/ConnectThink/WP-SCSS/issues/165)
- 2.1.0
  - Settings dropdown added for choosing additional base compile locations outside of current theme. Suggestion by [pixeldesignstudio ](https://github.com/ConnectThink/WP-SCSS/issues/127)
- 2.0.2
  - Added option in settings to enable an 'always recompile' flag. Suggestion by [bick](https://github.com/ConnectThink/WP-SCSS/issues/151)
- 2.0.1
  - Bugfix to add filter for option_wpscss_options to remove Leafo if stored in DB. Thanks to [kinsky-org](https://github.com/ConnectThink/WP-SCSS/issues/157) for pointing this out
  - Saving plugin settings will update DB with correct value.
- 2.0.0
  - Requires PHP 5.6
  - Update src to use [ScssPHP github repo at 1.0.2](https://github.com/scssphp/scssphp/tree/1.0.2)
  - Added check to make sure 'compiler' function was not already defined. [Shadoath](https://github.com/ConnectThink/WP-SCSS/pull/155)
- 1.2.6
  - Create cache dir if it doesn't exist [@XNBlank](https://github.com/ConnectThink/WP-SCSS/pull/135)
  - Add cache dir as default [@mhbapcc](https://github.com/ConnectThink/WP-SCSS/pull/144)
- 1.2.5
  - Fix error when ".\*" folders exist [@chameron](https://github.com/ConnectThink/WP-SCSS/pull/111)
  - Add detailed error description for the directory settings [@andreyc0d3r](https://github.com/ConnectThink/WP-SCSS/pull/121)
  - Fix on SASS compilation trigger [@fazzinipierluigi](https://github.com/ConnectThink/WP-SCSS/pull/122)
- 1.2.4
  - Updated scssphp to version 0.7.5
  - Added source map [@iannacone](https://github.com/ConnectThink/WP-SCSS/issues/49)
  - Always define $wpscss_compiler in the global scope [@jazbek](https://github.com/ConnectThink/WP-SCSS/pull/98)
- 1.2.3
  - Updated scssphp to version 0.7.2 [@hellerbenjamin](https://github.com/ConnectThink/WP-SCSS/pull/86)
  - Removed depricated screen_icon()
- 1.2.2
  - Updated scssphp to version 0.6.6
- 1.2.1
  - Changed set version option to update if already exists
- 1.2.0
  - Fixed a bug where directory inputs were not getting sanitized [@mmcev106](https://github.com/ConnectThink/WP-SCSS/pull/66)
  - Made the missing directory warning also display if a specified path is a file [@mmcev106](https://github.com/ConnectThink/WP-SCSS/pull/65)
  - Added /vendor to .gitignore [@mmcev106](https://github.com/ConnectThink/WP-SCSS/pull/64)
  - Dont enqueue already enqueued stylesheets [@bobbysmith007](https://github.com/ConnectThink/WP-SCSS/pull/61)
- 1.1.9 - Added filter to set variables via PHP [@ohlle](https://github.com/ohlle) and option to minify CSS output [@mndewitt](https://github.com/mndewitt)
- 1.1.8 - Various improvements from pull requests by [@jbrains](https://github.com/jbrains) and [@brainfork](https://github.com/brainfork)
- 1.1.7 - Update scssphp to 0.0.12 - pull from #16 [@GabrielGil](https://github.com/GabrielGil)
- 1.1.6 - Upgraded scss.inc.php to version 0.0.10 - pull from #12 [@kirkhoff](https://github.com/kirkhoff)
- 1.1.5 - Added option to only show errors to logged in users - merge from #10 [@tolnem](https://github.com/tolnem)
- 1.1.4 - Add support for subfolders in scss directory
- 1.1.3 - Fix print bug (2) in header
- 1.1.2 - Add support for moved wp-content directory
- 1.1.1 - Catch permissions errors
- 1.0.0 - Initial Release

## License

This plugin was developed by Connect Think. Now maintained by [shadoath](https://github.com/shadoath/), and contributers.

[GPL V3](http://www.gnu.org/copyleft/gpl.html)
