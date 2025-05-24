# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

WP-SCSS is a WordPress plugin that compiles SCSS files to CSS using the ScssPhp library. It provides a WordPress admin interface for configuring compilation settings and automatically compiles SCSS files when changes are detected.

## Architecture

### Core Components

- **wp-scss.php**: Main plugin file with 8-stage workflow orchestration
- **class/class-wp-scss.php**: Core Wp_Scss class handling SCSS compilation, caching, and file operations
- **options.php**: Wp_Scss_Settings class creating WordPress admin interface with security validation
- **scssphp/**: Embedded ScssPhp library (v1.11.0) with full AST parsing and compilation

### 8-Stage Plugin Workflow

1. Global variable definition and WordPress constants
2. Dependency loading (scssphp compiler, classes, options)
3. Settings registration and admin page setup
4. Database value cleanup/migration for legacy installations
5. Settings validation, path resolution, and directory checks
6. Compiler instantiation with configuration and execution
7. Multi-tier error handling (display/logging) system
8. Optional CSS file auto-enqueuing with version management

### Dynamic Base Directory System

Supports 5 configurable base locations:

- **Current Theme**: `get_stylesheet_directory()` (child theme if active)
- **Parent Theme**: `get_template_directory()` (when child theme exists)
- **Uploads Directory**: `wp_get_upload_dir()['basedir']`
- **WP-SCSS Plugin**: Plugin's own directory
- **Custom Path**: Legacy absolute path support with migration warnings

### File Processing Logic

- SCSS files without underscore prefix are compiled to matching CSS files
- Underscore-prefixed files (partials) are import-only, not directly compiled
- Recursive directory scanning with modification time comparison
- Atomic compilation via cache directory prevents serving incomplete CSS
- Source map generation with configurable modes (None/Inline/File)

## Development Commands

No build tools required - direct PHP development with WordPress coding standards.

## WordPress Integration

### Core Hooks

- `wp_loaded`: Triggers compilation check (allows theme variable injection)
- `admin_menu`: Registers settings page under Settings menu
- `admin_init`: Initializes settings fields with validation
- `wp_enqueue_scripts`: Auto-enqueues compiled CSS with file modification timestamps
- `plugin_action_links`: Adds Settings link to plugins page

### Extensibility Filters

- `wp_scss_variables`: Inject PHP variables into SCSS compilation
- `wp_scss_needs_compiling`: Override compilation necessity logic
- `wp_scss_base_compiling_modes`: Modify available base directories
- `wp_scss_compiling_modes`: Add custom compilation modes
- `wp_scss_sourcemap_modes`: Extend sourcemap options
- `option_wpscss_options`: Database cleanup for legacy values

### Performance Features

- Smart compilation only when SCSS newer than CSS
- RecursiveDirectoryIterator for efficient file system scanning
- Cache-based atomic file operations
- Duplicate enqueue prevention
- Always recompile mode: `WP_SCSS_ALWAYS_RECOMPILE` constant

## Security Implementation

### Input Sanitization

- `sanitize_text_field()` for all directory paths
- `wp_kses()` with allowed HTML for user-facing output
- `esc_attr()` for form field attributes
- WordPress nonce handling via `settings_fields()`

### File Operations Security

- Directory existence and writability validation
- File permission checks before cache/CSS operations
- Path traversal protection through base path validation
- Capability checks (`manage_options`) for settings access

## Error Handling System

### Three-Tier Error Display

- **show**: Display compilation errors in header for all users
- **show-logged-in**: Display only to authenticated users (LOGGED_IN_COOKIE check)
- **hide**: Log errors to file with automatic rotation

### Advanced Logging

- Error log in SCSS directory (`error_log.log`)
- Automatic log rotation when file exceeds 1MB
- Timestamped entries with file and message details
- Graceful degradation for permission issues

## Configuration

Settings stored in `wpscss_options` WordPress option:

- Base compiling folder selection (5 directory types)
- SCSS and CSS directory relative paths
- Compilation mode (compressed/expanded)
- Source map generation (None/Inline/File)
- Error display preferences (show/show-logged-in/hide)
- Auto-enqueue option with intelligent URL generation
- Always recompile flag for development

## Variable Injection System

PHP-to-SCSS variable passing:

```php
function set_scss_variables() {
    return array(
        'primary-color' => '#007cba',
        'font-size' => '16px'
    );
}
add_filter('wp_scss_variables', 'set_scss_variables');
```

## Key Implementation Patterns

- **Defensive Programming**: Extensive validation and graceful degradation
- **WordPress Standards**: Proper API usage and coding standards compliance
- **Separation of Concerns**: Clean architecture between compilation, settings, and WordPress integration
- **Performance-First**: Intelligent compilation triggers and efficient file operations
- **Extensibility**: Rich filter system for customization without core modifications
