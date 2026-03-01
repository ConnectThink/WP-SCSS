# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

WP-SCSS is a WordPress plugin that compiles SCSS files to CSS using the embedded ScssPhp library (v1.11.0). It provides a WordPress admin interface for configuring compilation settings and automatically compiles SCSS files when changes are detected.

## Core Files

- **wp-scss.php**: Main plugin file — orchestrates the 8-stage workflow (global vars → dependencies → settings registration → DB migration → settings resolution → compiler execution → error handling → enqueue)
- **class/class-wp-scss.php**: `Wp_Scss` class — handles SCSS compilation, caching, needs-compiling checks, and CSS enqueuing
- **options.php**: `Wp_Scss_Settings` class — WordPress admin settings page with field registration and sanitization
- **scssphp/**: Embedded ScssPhp library (do not modify; vendor code)
- **cache/**: Temporary directory for atomic compilation (gitignored except `.gitkeep`)

## Development

No build tools or test suite. Testing requires a live WordPress installation with the plugin active. PHP development only.

## Architecture

### Compilation Flow

The compilation check runs on `wp_loaded` (allowing themes to inject variables first via `wp_scss_variables` filter before `compile()` is called).

**Important distinction:**
- `needs_compiling()` scans the SCSS directory **recursively** (catches partial changes in subdirectories)
- `compile()` only iterates the **top-level** SCSS directory for files to compile (partials in subdirectories are not directly compiled, only imported)

**Atomic write pattern:** Each SCSS file is compiled to `cache/` first. Only after all files compile with zero errors are the cached CSS files moved to the CSS directory. If any error occurs, the cache files remain and CSS files are not updated.

### Base Directory System

The `base_compiling_folder` setting stores a **key name** (not a path) in the DB. `get_base_dir_from_name()` in `wp-scss.php` maps these names to real paths at runtime:

| Key | Function |
|-----|----------|
| `Current Theme` | `get_stylesheet_directory()` (only shown when no child theme) |
| `Parent Theme` | `get_template_directory()` |
| `Child Theme` | `get_stylesheet_directory()` |
| `Uploads Directory` | `wp_get_upload_dir()['basedir']` |
| `WP-SCSS Plugin` | `WPSCSS_PLUGIN_DIR` |

Legacy absolute paths stored in the DB trigger an admin notice asking users to re-save settings.

### DB Options (`wpscss_options`)

Keys: `base_compiling_folder`, `scss_dir`, `css_dir`, `cache_dir`, `compiling_options` (`compressed`/`expanded`), `sourcemap_options` (`SOURCE_MAP_NONE`/`SOURCE_MAP_INLINE`/`SOURCE_MAP_FILE`), `errors` (`show`/`show-logged-in`/`hide`), `enqueue`, `always_recompile`

The `option_wpscss_options` filter (step 4 in wp-scss.php) migrates legacy Leafo formatter class names to current ScssPhp names on every read.

### WordPress Hooks

- `wp_loaded`: Triggers `needs_compiling()` → `compile()` → error handling
- `admin_menu` / `admin_init`: Settings page registration
- `wp_enqueue_scripts` (priority 50): Auto-enqueues compiled CSS using file mtime as version
- `plugin_action_links`: Adds Settings link on plugins page

### Extensibility Filters

- `wp_scss_variables`: Return `array('var-name' => 'value')` to inject SCSS variables
- `wp_scss_needs_compiling`: Override the boolean result of `needs_compiling()`
- `wp_scss_base_compiling_modes`: Modify the base directory dropdown options
- `wp_scss_compiling_modes`: Add custom output style options
- `wp_scss_sourcemap_modes`: Extend sourcemap options
- `wp_scss_error_diplay`: Modify error display options (note: key is misspelled in source as `diplay`)
- `option_wpscss_options`: DB value cleanup (used internally for legacy migration)

### Always-Recompile

Two ways to force recompilation on every page load (useful for development):
1. `WP_SCSS_ALWAYS_RECOMPILE` PHP constant (defined in `wp-config.php`)
2. `always_recompile` setting in admin UI

The constant takes precedence and disables the checkbox in the UI.

### Error Handling

Errors are stored in `Wp_Scss::$compile_errors` as `['file' => ..., 'message' => ...]` arrays. Display is controlled by the `errors` setting:

- `show`: Renders a fixed overlay with error details for all visitors
- `show-logged-in`: Same, but checks `LOGGED_IN_COOKIE` (not full WP auth stack)
- `hide`: Appends to `error_log.log` in the SCSS directory; auto-rotates at 1MB by discarding the first half

### Variable Injection

```php
add_filter('wp_scss_variables', function() {
    return ['primary-color' => '#007cba', 'font-size' => '16px'];
});
```

Values are passed through `ScssPhp\ScssPhp\ValueConverter::parseValue()` before being injected into the compiler. Empty string values are silently dropped.
