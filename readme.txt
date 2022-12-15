=== WP-SCSS ===
Contributors: connectthink, sky-bolt
Tags: sass, scss, css, ScssPhp
Plugin URI: https://github.com/ConnectThink/WP-SCSS
Requires at least: 3.0.1
Tested up to: 6.0
Requires PHP: 7.2
Stable tag: 4.0.2
License: GPLv3 or later
License URI: http://www.gnu.org/copyleft/gpl.html

Compiles .scss files to .css and enqueues them.

== Description ==

Compiles .scss files on your wordpress install using [ScssPhp](https://github.com/scssphp/scssphp/). Includes settings page for configuring directories, error reporting, compiling options, and auto enqueuing.

The plugin only compiles when changes have been made to the scss files. Compiles are made to the matching css file, so disabling this plugin will not take down your stylesheets. In the instance where a matching css file does not exist yet, the plugin will create the appropriate css file in the css directory.

[Get detailed instructions on github](https://github.com/ConnectThink/WP-SCSS)

== Looking for a new maintainer == 

If you are interested in giving back to the open source plugin respond [here](https://github.com/ConnectThink/WP-SCSS/issues/242) with your interest

== Installation ==

1. Upload plugin to plugins directory
2. Active plugin through the 'Plugins' menu in Wordpress
3. Configure plugin options through settings page `settings -> WP-SCSS`.

== Frequently Asked Questions ==

= Can I use a child theme? =

Yes, absolutely. Make sure you define your directories relative to your child theme and that your child theme is active. Otherwise you'll see an error regarding missing directories.


= What version of PHP is required? =

PHP 7.2 is required to run WP-SCSS


= How do I @import subfiles =

You can import other scss files into parent files and compile them into a single css file. To do this, use @import as normal in your scss file. All imported file names *must* start with an underscore. Otherwise they will be compiled into their own css file.

When importing in your scss file, you can leave off the underscore.

> `@import 'subfile';`


= Does this plugin support Compass? =

Currently there isn't a way to fully support [compass](https://github.com/chriseppstein/compass) with a php compiler. If you want limited support, you can manually import the compass framework. You'll need both the _compass.scss and compass directory.


> `compass / frameworks / compass / stylesheets /
> `@import 'compass';`

Alternatively, you can include [Bourbon](https://github.com/thoughtbot/bourbon) in a similar fashion.


= Can I use .sass syntax with this Plugin? =

This plugin will only work with .scss format.


= It's not updating my css, what's happening? =

Do you have errors printing to the front end? If not, check your log file in your scss directory. The css will not be updated if there are errors in your sass file(s).

Make sure your directories are properly defined in the settings. Paths are defined from the root of the theme.


= I'm having other issues and need help =

If you are having issues with the plugin, create an issue on [github](https://github.com/ConnectThink/WP-SCSS), and we'll do our best to help.


== Changelog ==

= 4.0.2 =
  - Full SVN commit to Wordpress
  - With version bump

= 4.0.0 = 
  - DO NOT USE, missing commited files on WP SVN
  - Updates ScssPHP to version [1.11.0](https://github.com/scssphp/scssphp/releases/tag/v1.11.0) thanks to [fabarea](https://github.com/ConnectThink/WP-SCSS/issues/240)

= 3.0.1 =
  - Full SVN commit to Wordpress

= 3.0.0 =
  - DO NOT USE, missing commited files on WP SVN
  - Updates ScssPHP to version [1.10.0](https://github.com/scssphp/scssphp/releases/tag/v1.10.0) thanks to [fabarea](https://github.com/ConnectThink/WP-SCSS/issues/228)

= 2.4.0 = 
  - Changes the base_compiling_folder to store key not path to directory [shadoath](https://github.com/ConnectThink/WP-SCSS/issues/219)
  - This allows deploying from local or staging to production by not saving absolute paths in DB.

= 2.3.5 = 
  - Add 'selected' to wp_kses on select() [shadoath](https://github.com/ConnectThink/WP-SCSS/issues/217)

= 2.3.4 =
  - Add check to compiling_options on load() [alianschiavoncini](https://github.com/ConnectThink/WP-SCSS/issues/209)
  - Add more params to wp_kses in options() [evHaitch ](https://github.com/ConnectThink/WP-SCSS/issues/213)

= 2.3.3 =
  - Fix params passed to wp_kses() [shadoath](https://github.com/ConnectThink/WP-SCSS/pull/211)
  
= 2.3.2 =
  - Add wp_kses() to echos with potential user input [shadoath](https://github.com/ConnectThink/WP-SCSS/pull/208)

= 2.3.1 =
  - Wrap check for WP_SCSS_ALWAYS_RECOMPILE with () [niaccurshi](https://github.com/ConnectThink/WP-SCSS/pull/199)

= 2.3.0 =
  - Update src to use [ScssPHP github repo at 1.5.2](https://github.com/scssphp/scssphp/releases/tag/1.5.2)
  - Update deprecated setFormatter to setOutputStyle and provide db migration [shadoath](https://github.com/ConnectThink/WP-SCSS/pull/195)

= 2.2.0 =
  - Updates to allow compile() from outside the plugin [niaccurshi](https://github.com/ConnectThink/WP-SCSS/pull/190)
  - Update src to use [ScssPHP github repo at 1.2.1](https://github.com/scssphp/scssphp/releases/tag/1.2.1)

= 2.1.6 =
  - When enqueueing CSS files Defer to WordPress for URLs instead of trying to guess them. Change by [mmcev106](https://github.com/ConnectThink/WP-SCSS/pull/185)
  - Allow setting Base Directory to Parent theme folder. [Shadoath](https://github.com/ConnectThink/WP-SCSS/issues/178)

= 2.1.5 =
  - Enqueue CSS files using `realpath` function. Addition by [mmcev106](https://github.com/ConnectThink/WP-SCSS/pull/179)

= 2.1.4 =
  - Set source URL to be home_url('/') not simply `/`. Issue found by [realjjaveweb](https://github.com/ConnectThink/WP-SCSS/issues/128)

= 2.1.3 =
  - Must declare global to use it for $base_compiling_folder.

= 2.1.2 =
  - Correction for enqueueing styles not defaulting to get_stylesheet_directory() [Issue](https://github.com/ConnectThink/WP-SCSS/issues/168)

= 2.1.1 =
  - Bug fixes after merging 2.0.2 and 2.1.0 defaults worked, but new options did not. [Shadoath](https://github.com/ConnectThink/WP-SCSS/issues/165)

= 2.1.0 =
  - Settings dropdown added for choosing additional base compile locations outside of current theme. Suggestion by [pixeldesignstudio ](https://github.com/ConnectThink/WP-SCSS/issues/127)

= 2.0.2 =
  - Added option in settings to enable an 'always recompile' flag. Suggestion by [bick](https://github.com/ConnectThink/WP-SCSS/issues/151)

= 2.0.1 =
  - Bugfix to add filter for option_wpscss_options to remove Leafo if stored in DB. Thanks to [kinky-org](https://github.com/ConnectThink/WP-SCSS/issues/157) for pointing this out
  - Saving plugin settings will update DB with the correct value.

= 2.0.0 =
  - Requires PHP 5.6
  - Update src to use [ScssPHP github repo at 1.0.2](https://github.com/scssphp/scssphp/tree/1.0.2)
  - Added check to make sure 'compiler' function was not already defined. [Shadoath](https://github.com/ConnectThink/WP-SCSS/pull/155)

= 1.2.6 =
  - Create cache dir if it doesn't exist [@XNBlank](https://github.com/ConnectThink/WP-SCSS/pull/135
  - Add cache dir as default [@mhbapcc](https://github.com/ConnectThink/WP-SCSS/pull/144)

= 1.2.5 =
  - Fix error when ".*" folders exist [@chameron](https://github.com/ConnectThink/WP-SCSS/pull/111)
  - Add detailed error description for the directory settings [@andreyc0d3r](https://github.com/ConnectThink/WP-SCSS/pull/121)
  - Fix on SASS compilation trigger [@fazzinipierluigi](https://github.com/ConnectThink/WP-SCSS/pull/122)

= 1.2.4 =
  - Updated scssphp to version 0.7.5
  - Added source map [@iannacone](https://github.com/ConnectThink/WP-SCSS/issues/49)
  - Always define $wpscss_compiler in the global scope [@jazbek](https://github.com/ConnectThink/WP-SCSS/pull/98)

= 1.2.3 =
  - Updated scssphp to version 0.7.2 [@hellerbenjamin](https://github.com/ConnectThink/WP-SCSS/pull/86)
  - Removed depricated screen_icon()

= 1.2.2 =
  - Updated scssphp to version 0.6.6

= 1.2.1 =
  - Changed set version option to update if already exists

= 1.2.0 =
  - Fixed a bug where directory inputs were not getting sanitized [@mmcev106](https://github.com/ConnectThink/WP-SCSS/pull/66)
  - Made the missing directory warning also display if a specified path is a file [@mmcev106](https://github.com/ConnectThink/WP-SCSS/pull/65)
  - Added /vendor to .gitignore [@mmcev106](https://github.com/ConnectThink/WP-SCSS/pull/64)
  - Dont enqueue already enqueued stylesheets [@bobbysmith007](https://github.com/ConnectThink/WP-SCSS/pull/61)

= 1.1.9 =
  - Added filter to set variables via PHP [@ohlle](https://github.com/ohlle)
  - Added option to minify CSS output [@mndewitt](https://github.com/mndewitt)

= 1.1.8 =
Various improvements from pull requests by [@jbrains](https://github.com/jbrains) and [@brainfork](https://github.com/brainfork)

= 1.1.7 =
  - Update scssphp to 0.0.12 - pull from #16 [@GabrielGil](https://github.com/GabrielGil)

= 1.1.6 =
  - Upgraded scss.inc.php to version 0.0.10; via pull request from [kirkhoff](https://github.com/kirkhoff)

= 1.1.5 =
  - Added option to only show errors to logged in users; via pull request from [tolnem](https://github.com/tolnem)

= 1.1.4 =
  - Add suport for subfolders in scss directory

= 1.1.3 =
  - Hotfix for a accidental character

= 1.1.2 =
  - Added support for moved wp-content directories

= 1.1.1 =
  - Added error handling for file permissions issues
  - Changed error log to .log for auto updating errors

= 1.0.0 =
  - Initial Build
