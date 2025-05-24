# Testing WP-SCSS Plugin

This document explains how to run tests for the WP-SCSS plugin to ensure the settings persistence bug fix and other functionality work correctly.

## Test Setup

### Prerequisites

1. **PHP 7.2+** (same as plugin requirement)
2. **MySQL/MariaDB** for test database
3. **PHPUnit** (automatically installed with WordPress test suite)

### Installation

1. **Install WordPress Test Suite:**
   ```bash
   # Run from plugin root directory
   bin/install-wp-tests.sh wp_scss_test root password localhost latest
   ```
   
   Replace the database credentials as needed:
   - `wp_scss_test` - test database name (will be created)
   - `root` - database user
   - `password` - database password  
   - `localhost` - database host

2. **Verify Setup:**
   ```bash
   # Check if test environment is ready
   ls /tmp/wordpress-tests-lib/
   ```

## Running Tests

### Run All Tests
```bash
phpunit
```

### Run Specific Test Files
```bash
# Test only settings functionality
phpunit tests/test-settings.php

# Test only integration scenarios
phpunit tests/test-wp-scss-integration.php
```

### Run Specific Test Methods
```bash
# Test the specific bug fix
phpunit --filter test_bug_fix_all_settings_preserved

# Test base location change scenario
phpunit --filter test_base_location_change_persistence
```

## Test Coverage

### Settings Sanitization Tests (`test-settings.php`)

- **test_sanitize_preserves_all_fields** - Ensures all 9 form fields are preserved
- **test_sanitize_adds_trailing_slashes** - Verifies directory path formatting
- **test_sanitize_checkbox_fields** - Tests checkbox handling (checked/unchecked)
- **test_sanitize_handles_empty_fields** - Tests behavior with missing data
- **test_sanitize_text_fields** - Tests XSS protection via sanitize_text_field()
- **test_bug_fix_all_settings_preserved** - Regression test for the original bug

### Integration Tests (`test-wp-scss-integration.php`)

- **test_settings_persistence_workflow** - Full save/retrieve cycle
- **test_base_location_change_persistence** - Specific bug scenario reproduction
- **test_development_mode_persistence** - Development settings scenarios
- **test_checkbox_form_behavior** - Real WordPress checkbox behavior
- **test_old_sanitize_method_would_fail** - Proves the bug was actually fixed

## The Bug We Fixed

**Original Problem:** The `sanitize()` method only preserved `scss_dir` and `css_dir` fields, causing all other settings (base location, compilation mode, etc.) to be lost on save.

**Tests That Prove the Fix:**
- `test_bug_fix_all_settings_preserved` - Shows all 9 fields are now saved
- `test_base_location_change_persistence` - Reproduces exact user scenario
- `test_old_sanitize_method_would_fail` - Demonstrates the old broken behavior

## Test Database

Tests use a separate database (`wp_scss_test` by default) that is:
- Automatically created by the install script
- Cleaned between test runs
- Completely separate from your development/production WordPress

## Continuous Integration

These tests can be integrated into CI/CD pipelines:

```yaml
# Example GitHub Actions workflow
- name: Setup test database
  run: |
    sudo systemctl start mysql
    bin/install-wp-tests.sh wp_scss_test root password localhost latest

- name: Run tests
  run: phpunit
```

## Troubleshooting

**"Could not find wp-tests-config.php"**
- Run the install script: `bin/install-wp-tests.sh ...`

**Database connection errors**
- Verify MySQL is running
- Check database credentials in install command
- Ensure database user has CREATE privileges

**Class not found errors**
- Ensure you're running tests from the plugin root directory
- Check that wp-scss.php and options.php exist