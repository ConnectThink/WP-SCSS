# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

WP-SCSS is a WordPress plugin that compiles SCSS files to CSS using the ScssPhp library. It provides a WordPress admin interface for configuring compilation settings and automatically compiles SCSS files when changes are detected.

## Architecture

### Core Components

- **wp-scss.php**: Main plugin file that orchestrates the entire compilation workflow
- **class/class-wp-scss.php**: Core Wp_Scss class that handles SCSS compilation logic 
- **options.php**: Wp_Scss_Settings class that creates the WordPress admin settings page
- **scssphp/**: Embedded ScssPhp library (v1.11.0) for SCSS compilation

### Key Workflow

1. Plugin registers settings and admin page
2. Settings are validated and directory paths resolved
3. Wp_Scss object is instantiated with configuration
4. On wp_loaded hook, checks if compilation is needed by comparing file modification times
5. Compiles SCSS files (non-underscore prefixed) to matching CSS files via cache directory
6. Handles errors by displaying them or logging to file
7. Optionally auto-enqueues generated CSS files

### Directory Structure

- SCSS source files in configurable directory (default: theme/scss/)
- CSS output files in configurable directory (default: theme/css/)  
- Cache directory for temporary compilation (default: plugin/cache/)
- Underscore-prefixed SCSS files are imported only, not compiled individually

## Development Commands

This project does not use build tools or package managers. Development is done directly on PHP files.

## Key Filters and Hooks

- `wp_scss_variables`: Allows setting SCSS variables via PHP
- `wp_scss_needs_compiling`: Override compilation necessity check
- Always recompile flag: `WP_SCSS_ALWAYS_RECOMPILE` constant

## Configuration

Settings stored in `wpscss_options` WordPress option:
- Base compiling folder (theme/uploads/plugin directory)
- SCSS and CSS directory paths  
- Compilation mode (compressed/expanded)
- Source map generation options
- Error display preferences
- Auto-enqueue option